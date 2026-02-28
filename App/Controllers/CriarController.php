<?php

/**
 * Gerencia o estado do Simulado na sessão.
 */

class SimulationSession
{
    // Chave da sessão onde os dados do simulado são guardados
    private const SESSION_KEY = 'simulado';

    public function __construct()
    {
        // Inicia a sessão se ainda não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function init($data)
    {
        // Inicializa a sessão com os dados completos do simulado
        $_SESSION[self::SESSION_KEY] = $data;
    }

    public function set($key, $value)
    {
        // Define um valor específico dentro do array do simulado na sessão
        $_SESSION[self::SESSION_KEY][$key] = $value;
    }

    public function get($key)
    {
        // Retorna um valor específico do array do simulado ou null se não existir
        return $_SESSION[self::SESSION_KEY][$key] ?? null;
    }
}

/**
 * Fábrica responsável por carregar questões e criar a estrutura inicial do simulado.
 */
class SimulationFactory
{
    private string $jsonPath;

    public function __construct(string $jsonPath)
    {
        // Define o caminho do arquivo JSON
        $this->jsonPath = $jsonPath;
    }

    /**
     * Carrega as questões do arquivo JSON, embaralha e seleciona a quantidade desejada.
     */
    public function createSimulationData($materia, $quantidade, $modo)
    {
        // Verifica se o arquivo JSON existe
        if (!file_exists($this->jsonPath)) {
            throw new Exception("Arquivo de questões não encontrado: {$this->jsonPath}");
        }

        // Lê o conteúdo do arquivo JSON
        $json = file_get_contents($this->jsonPath);
        // Decodifica o JSON para um array associativo
        $dados = json_decode($json, true);

        // Valida se a estrutura do JSON está correta
        if (!isset($dados['questoes']) || !is_array($dados['questoes'])) {
            throw new Exception("Formato de JSON inválido.");
        }

        // Obtém a lista de questões
        $questoes = $dados['questoes'];

        // Embaralha as questões
        shuffle($questoes);

        // Seleciona apenas a quantidade solicitada
        $questoesSelecionadas = array_slice($questoes, 0, $quantidade);

        // Monta a estrutura inicial do simulado
        $simulado = [
            'materia' => $materia,
            'modo' => $modo,
            'questoes' => $questoesSelecionadas,
            'atual' => 0,
            'respostas' => [],
            'feedback' => []
        ];

        // Configuração adicional para o modo exame
        if ($modo === 'exame') {
            // Define o tempo de início
            $simulado['inicio'] = time();
            // Define o tempo total (1 hora em segundos)
            $simulado['tempo_total'] = 1 * 60 * 60; // 1 hora em segundos (ajustado conforme o original)
        }

        // Retorna os dados do simulado prontos
        return $simulado;
    }
}
// --- Execução do Script (CriarController.php) ---

try {
    // 1. Recebe e sanitiza os dados do formulário
    $materia = $_POST['materia'] ?? 'Geral';
    $quantidade = (int) ($_POST['quantidade'] ?? 10);
    $modo = $_POST['modo'] ?? 'estudo';

    // 2. Define o caminho do JSON (ajustado para ser robusto)
    //$jsonPath = __DIR__ . '/../../data/questoes_microbiologia_refinado.json';
    $arquivosPorMateria = [
        '1' => 'questoes_microbiologia_refinado.json',
        '2' => 'questoes_biologia_final_v2.json',
    ];

    $nomeArquivo = $arquivosPorMateria[$materia] ?? 'questoes_geral.json';
    $jsonPath = __DIR__ . "/../../data/{$nomeArquivo}";

    // 3. Usa a Fábrica para preparar os dados
    $factory = new SimulationFactory($jsonPath);
    $novoSimulado = $factory->createSimulationData($materia, $quantidade, $modo);

    // 4. Inicializa a sessão com os novos dados
    $session = new SimulationSession();
    $session->init($novoSimulado);

    // 5. Redireciona para o questionário (route pública)
    header('Location: /questionario.php');
    exit;
} catch (Exception $e) {
    // Tratamento de erro básico
    die("Erro ao criar simulado: " . $e->getMessage());
}




?>
















<!-- <?php
        // session_start();

        // // print_r($_POST);

        // // recebe dados do formulário
        // $materia = $_POST['materia'];
        // $quantidade = (int) $_POST['quantidade'];
        // $modo = $_POST['modo'];

        // // carrega JSON
        // $json = file_get_contents(__DIR__ . '/../../data/questoes_microbiologia_refinado.json');
        // $dados = json_decode($json, true);

        // $questoes = $dados['questoes'];

        // // embaralha
        // shuffle($questoes);

        // // corta quantidade escolhida
        // $questoesSelecionadas = array_slice($questoes, 0, $quantidade);

        // // cria simulado
        // $_SESSION['simulado'] = [
        //     'materia'   => $materia,
        //     'modo'      => $modo,
        //     'questoes'  => $questoesSelecionadas,
        //     'atual'     => 0,
        //     'respostas' => []
        // ];

        // if ($modo === 'exame') {
        //     $_SESSION['simulado']['inicio'] = time(); // timestamp atual
        //     $_SESSION['simulado']['tempo_total'] = 1 * 60 * 60; // 2 horas (em segundos)
        // }



        // // redireciona
        // header('Location: ../Views/questionario.php');
        // exit;



        ?> -->