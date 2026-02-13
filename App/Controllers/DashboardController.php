<?php
// App/Controllers/DashboardController.php

class DashboardController
{
    private $db;
    private $usuario_id;

    public function __construct($conexao, $usuario_id)
    {
        $this->db = $conexao;
        $this->usuario_id = $usuario_id;
    }

    private function calcularSequenciaReal(): int
    {
        // Busca datas únicas de simulados em ordem decrescente
        $sql = "SELECT DISTINCT DATE(data_realizacao) as data 
                FROM historico_simulados 
                WHERE usuario_id = :uid 
                ORDER BY data DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $this->usuario_id]);
        $datas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($datas))
            return 0;

        $sequencia = 0;
        $hoje = new DateTime('today');
        $ultimaDataSimulado = new DateTime($datas[0]);

        // Se o último simulado foi há mais de 1 dia (ex: anteontem), a sequência quebrou
        $diferencaHoje = $hoje->diff($ultimaDataSimulado)->days;
        if ($diferencaHoje > 1)
            return 0;

        $dataComparacao = $ultimaDataSimulado;
        foreach ($datas as $index => $dataStr) {
            $dataAtual = new DateTime($dataStr);

            if ($index === 0) {
                $sequencia = 1;
                continue;
            }

            // Verifica se a data atual é exatamente 1 dia antes da data de comparação
            $intervalo = $dataComparacao->diff($dataAtual)->days;

            if ($intervalo === 1) {
                $sequencia++;
                $dataComparacao = $dataAtual;
            } else {
                break;
            }
        }

        return $sequencia;
    }

    public function getStats(): array
    {
        $sql = "SELECT 
                    SUM(total_questoes) as total_respondidas, 
                    SUM(acertos) as total_acertos,
                    COUNT(id) as total_simulados
                FROM historico_simulados 
                WHERE usuario_id = :uid";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $this->usuario_id]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalRespondidas = $dados['total_respondidas'] ?? 0;
        $totalAcertos = $dados['total_acertos'] ?? 0;
        $aproveitamento = ($totalRespondidas > 0) ? round(($totalAcertos / $totalRespondidas) * 100, 1) : 0;

        return [
            'questoes_respondidas' => (int) $totalRespondidas,
            'aproveitamento_geral' => $aproveitamento,
            'total_simulados' => (int) ($dados['total_simulados'] ?? 0),
            'pontuacao_total' => (int) ($totalAcertos * 10),
            'sequencia_dias' => $this->calcularSequenciaReal()
        ];
    }

    public function getRecentSimulados(): array
    {
        $sql = "SELECT materia, acertos, total_questoes, data_realizacao 
                FROM historico_simulados 
                WHERE usuario_id = :uid 
                ORDER BY data_realizacao DESC LIMIT 5";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $this->usuario_id]);

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $recentes = [];

        foreach ($resultados as $row) {
            $total = (int) $row['total_questoes'];
            $acertos = (int) $row['acertos'];
            $porcentagem = ($total > 0) ? ($acertos / $total) : 0;

            $recentes[] = [
                'data' => date('d/m/Y', strtotime($row['data_realizacao'])),
                'categoria' => $row['materia'],
                'pontuacao' => "$acertos/$total",
                'status' => ($porcentagem >= 0.7) ? 'Aprovado' : 'Reprovado',
                'classe' => ($porcentagem >= 0.7) ? 'success' : 'danger'
            ];
        }

        return $recentes;
    }
}
