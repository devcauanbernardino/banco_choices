<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/conexao.php';

class HistoricoModel
{
    private $db;

    public function __construct($conexao)
    {
        $this->db = $conexao;
    }

    /**
     * Garante coluna detalhes_json (migração leve na primeira uso).
     */
    private function ensureDetalhesJsonColumn(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'historico_simulados'
              AND COLUMN_NAME = 'detalhes_json'
        ");
        if ((int) $stmt->fetchColumn() === 0) {
            $this->db->exec("
                ALTER TABLE historico_simulados
                ADD COLUMN detalhes_json LONGTEXT NULL
                COMMENT 'JSON revisao detalhada'
                AFTER total_questoes
            ");
        }
        $done = true;
    }

    /**
     * @param mixed $materia id da matéria (numérico)
     * @param array<int, array<string, mixed>>|null $detalhes linhas já montadas para a view
     */
    public function salvarResultado($usuario_id, $materia, $acertos, $total, ?array $detalhes = null, ?int $tempoSegundos = null): bool
    {
        $this->ensureDetalhesJsonColumn();

        $json = null;
        if ($detalhes !== null && $detalhes !== []) {
            $payload = [
                'v' => 1,
                'detalhes' => $detalhes,
            ];
            if ($tempoSegundos !== null && $tempoSegundos >= 0) {
                $payload['tempo_segundos'] = $tempoSegundos;
            }
            try {
                $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                error_log('HistoricoModel: JSON detalhes: ' . $e->getMessage());
                $json = null;
            }
        }

        $sql = 'INSERT INTO historico_simulados (usuario_id, materia_id, acertos, total_questoes, detalhes_json)
                VALUES (:uid, :mat, :ace, :tot, :det)';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':uid' => $usuario_id,
            ':mat' => $materia,
            ':ace' => $acertos,
            ':tot' => $total,
            ':det' => $json,
        ]);
    }

    /**
     * Busca um registro de histórico garantindo que pertence ao usuário.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorIdUsuario(int $historicoId, int $usuarioId): ?array
    {
        $this->ensureDetalhesJsonColumn();

        $sql = 'SELECT h.id, h.acertos, h.total_questoes, h.data_realizacao, h.detalhes_json,
                       m.nome AS materia_nome, m.id AS materia_id
                FROM historico_simulados h
                INNER JOIN materias m ON m.id = h.materia_id
                WHERE h.id = :hid AND h.usuario_id = :uid
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':hid' => $historicoId, ':uid' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
