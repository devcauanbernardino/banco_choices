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
    ) {
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
            die($e->getMessage());
        }
    }

    private function vincularMateria(int $usuarioId, int $materiaId): void
    {
        $sql = "INSERT INTO usuarios_materias (usuario_id, materia_id)
                VALUES (:usuario, :materia)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuarioId,
            ':materia' => $materiaId
        ]);
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

    public function buscarMateriasDoUsuario(int $usuarioId): array
    {
        $sql = "
            SELECT m.*
            FROM materias m
            INNER JOIN usuarios_materias um
                ON um.materia_id = m.id
            WHERE um.usuario_id = :usuario
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':usuario' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
