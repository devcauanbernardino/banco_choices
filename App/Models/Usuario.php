<?php

class Usuario
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /* ======================
       CADASTRO
    ====================== */

    public function cadastrar(string $nome, string $email, string $senha): int
    {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (nome, email, senha)
                VALUES (:nome, :email, :senha)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senhaHash
        ]);

        return (int) $this->conn->lastInsertId();
    }

    /**
     * Cadastro completo com matérias
     */
    public function cadastrarComMaterias(
        string $nome,
        string $email,
        string $senha,
        array $materias
    ): bool {
        try {
            $this->conn->beginTransaction();

            $usuarioId = $this->cadastrar($nome, $email, $senha);

            foreach ($materias as $materiaId) {
                $this->vincularMateria($usuarioId, (int) $materiaId);
            }

            $this->conn->commit();
            return true;

        } catch (Throwable $e) {
            $this->conn->rollBack();
            error_log('Usuario::cadastrarComMaterias: ' . $e->getMessage());
            return false;
        }
    }

    public function vincularMateria(int $usuarioId, int $materiaId): void
    {
        $sql = "INSERT INTO usuarios_materias (usuario_id, materia_id)
                VALUES (:usuario, :materia)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuarioId,
            ':materia' => $materiaId
        ]);
    }

    /**
     * Garante vínculos (ex.: matérias 1 e 2 no login) sem erro em duplicata.
     */
    public function garantirMateriasParaUsuario(int $usuarioId, array $materiaIds): void
    {
        $sql = 'INSERT IGNORE INTO usuarios_materias (usuario_id, materia_id) VALUES (:usuario, :materia)';
        $stmt = $this->conn->prepare($sql);
        foreach ($materiaIds as $mid) {
            $stmt->execute([
                ':usuario' => $usuarioId,
                ':materia' => (int) $mid,
            ]);
        }
    }

    /**
     * Verifica se o usuário tem a matéria liberada (compra/cadastro).
     */
    public function usuarioPossuiMateria(int $usuarioId, int $materiaId): bool
    {
        $sql = "SELECT 1 FROM usuarios_materias
                WHERE usuario_id = :usuario AND materia_id = :materia
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuarioId,
            ':materia' => $materiaId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /* ======================
       BUSCAS
    ====================== */

    public function buscarPorEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Último plano (monthly / semester / annual) em pedidos_itens cujo pedido
     * corresponde ao e-mail do utilizador na tabela users (evita divergência com a sessão).
     * Não exige status: itens só existem após pagamento aprovado no fulfillment.
     */
    public function buscarUltimoPlanoIdParaUsuarioId(int $userId): ?string
    {
        if ($userId <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare(
            'SELECT pi.plano_id
             FROM pedidos_itens pi
             INNER JOIN pedidos p ON p.id = pi.pedido_id
             INNER JOIN users u ON u.id = :uid
                AND TRIM(LOWER(p.email)) = TRIM(LOWER(u.email))
             ORDER BY p.id DESC, pi.id DESC
             LIMIT 1'
        );
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        $id = trim((string) ($row['plano_id'] ?? ''));

        return $id !== '' ? $id : null;
    }

    public function buscarMateriasDoUsuario(int $usuarioId): array
    {
        $sql = "
            SELECT m.*
            FROM materias m
            INNER JOIN (
                SELECT DISTINCT materia_id
                FROM usuarios_materias
                WHERE usuario_id = :usuario
            ) um ON um.materia_id = m.id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':usuario' => $usuarioId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $porId = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0 && !isset($porId[$id])) {
                $porId[$id] = $row;
            }
        }

        return array_values($porId);
    }

    /* ======================
       LOGIN
    ====================== */

    public function autenticar(string $email, string $senha): array|false
    {
        $usuario = $this->buscarPorEmail($email);

        if (!$usuario) {
            return false;
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return false;
        }

        // adiciona matérias ao usuário logado
        $usuario['materias'] = $this->buscarMateriasDoUsuario($usuario['id']);

        return $usuario;
    }

    /* ======================
       ATUALIZAÇÕES
    ====================== */

    public function atualizarPerfil(int $id, string $nome): bool
    {
        $sql = "UPDATE users SET nome = :nome WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':nome' => $nome,
            ':id' => $id
        ]);
    }

    public function atualizarSenha(int $id, string $novaSenha): bool
    {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET senha = :senha WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':senha' => $senhaHash,
            ':id' => $id
        ]);
    }
}
