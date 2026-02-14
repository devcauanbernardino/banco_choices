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

     /**
     * Busca o histórico completo de simulados com filtros
     */
    public function getHistoricoCompleto(string $filtroMateria = '', string $filtroStatus = ''): array {
        $sql = "SELECT id, materia, acertos, total_questoes, data_realizacao 
                FROM historico_simulados 
                WHERE usuario_id = :uid";
        
        $params = [':uid' => $this->usuario_id];

        if (!empty($filtroMateria)) {
            $sql .= " AND materia = :materia";
            $params[':materia'] = $filtroMateria;
        }

        $sql .= " ORDER BY data_realizacao DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $historico = [];
        foreach ($resultados as $row) {
            $total = (int)$row['total_questoes'];
            $acertos = (int)$row['acertos'];
            $porcentagem = ($total > 0) ? ($acertos / $total) : 0;
            $status = ($porcentagem >= 0.7) ? 'Aprovado' : 'Reprovado';

            // Aplica filtro de status no PHP para simplificar o SQL
            if (!empty($filtroStatus) && strtolower($status) !== strtolower($filtroStatus)) {
                continue;
            }

            $historico[] = [
                'id' => $row['id'],
                'data' => date('d/m/Y H:i', strtotime($row['data_realizacao'])),
                'materia' => ucfirst($row['materia']),
                'pontuacao' => "$acertos/$total",
                'porcentagem' => round($porcentagem * 100) . '%',
                'status' => $status,
                'classe' => ($porcentagem >= 0.7) ? 'success' : 'danger'
            ];
        }

        return $historico;
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
            'melhor_materia'       => $melhorMateria['materia'] ?? 'N/A',
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
    /**
     * Dados para o Gráfico de Evolução (Últimos 7 simulados)
     */
    public function getEvolucaoGrafico(): array
    {
        $sql = "SELECT DATE(data_realizacao) as data, (SUM(acertos)/SUM(total_questoes))*100 as desempenho 
                FROM historico_simulados 
                WHERE usuario_id = :uid 
                GROUP BY DATE(data_realizacao) 
                ORDER BY data_realizacao ASC LIMIT 10";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $this->usuario_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = [];
        $data = [];

        foreach ($resultados as $row) {
            $labels[] = date('d/m', strtotime($row['data']));
            $data[] = round($row['desempenho'], 1);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Desempenho por Matéria para as Barras de Progresso
     */
    public function getDesempenhoPorMateria(): array
    {
        $sql = "SELECT materia, SUM(acertos) as acertos, SUM(total_questoes) as total 
                FROM historico_simulados 
                WHERE usuario_id = :uid 
                GROUP BY materia 
                ORDER BY (SUM(acertos)/SUM(total_questoes)) DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $this->usuario_id]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $materias = [];
        foreach ($resultados as $row) {
            $porcentagem = round(($row['acertos'] / $row['total']) * 100);
            $materias[] = [
                'nome' => $row['materia'],
                'porcentagem' => $porcentagem
            ];
        }

        return $materias;
    }

    /**
     * Histórico Semanal (Agrupado por semana)
     */
    public function getHistoricoSemanal(): array
    {
        $sql = "SELECT 
                    YEARWEEK(data_realizacao, 1) as semana,
                    MIN(DATE(data_realizacao)) as inicio_semana,
                    SUM(total_questoes) as total,
                    SUM(acertos) as acertos
                FROM historico_simulados 
                WHERE usuario_id = :uid 
                GROUP BY semana 
                ORDER BY semana DESC LIMIT 4";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $this->usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
