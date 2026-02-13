<?php 

require_once __DIR__ . '/../../config/conexao.php';


class HistoricoModel {
    private $db;

    public function __construct($conexao) {
        $this->db = $conexao;
    }

    public function salvarResultado($usuario_id, $materia, $acertos, $total) {
        $sql = "INSERT INTO historico_simulados (usuario_id, materia, acertos, total_questoes) VALUES (:uid, :mat, :ace, :tot)";

        $stmt = $this->db->prepare($sql);
        return  $stmt->execute([
            ':uid' => $usuario_id,
            ':mat' => $materia,
            ':ace' => $acertos,
            ':tot' => $total
        ]);
    }
}

?>