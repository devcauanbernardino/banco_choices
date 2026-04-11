<?php

require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../Session/SimulationSession.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Usuario.php';

/**
 * Fábrica responsável por carregar questões e criar a estrutura inicial do simulado.
 */
class SimulationFactory
{
    private string $jsonPath;

    public function __construct(string $jsonPath)
    {
        $this->jsonPath = $jsonPath;
    }

    /**
     * Embaralhamento uniforme (Fisher–Yates) com inteiros criptograficamente seguros.
     */
    private function shuffleQuestions(array $questoes): array
    {
        $n = count($questoes);
        if ($n < 2) {
            return $questoes;
        }

        for ($i = $n - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            if ($i !== $j) {
                $tmp = $questoes[$i];
                $questoes[$i] = $questoes[$j];
                $questoes[$j] = $tmp;
            }
        }

        return $questoes;
    }

    /**
     * Carrega as questões do arquivo JSON, embaralha e seleciona a quantidade desejada.
     */
    public function createSimulationData($materia, $quantidade, $modo)
    {
        if (!is_readable($this->jsonPath)) {
            throw new Exception('Arquivo de questões não encontrado ou inacessível.');
        }

        $json = file_get_contents($this->jsonPath);
        if ($json === false) {
            throw new Exception('Não foi possível ler o arquivo de questões.');
        }

        try {
            $dados = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new Exception('Arquivo de questões inválido (JSON malformado).');
        }

        if (!isset($dados['questoes']) || !is_array($dados['questoes'])) {
            throw new Exception('Formato de JSON inválido.');
        }

        $questoes = $dados['questoes'];
        if ($questoes === []) {
            throw new Exception('Nenhuma questão disponível neste banco.');
        }

        $questoes = $this->shuffleQuestions($questoes);

        $totalDisponivel = count($questoes);
        $quantidade = max(1, min($totalDisponivel, (int) $quantidade));

        $questoesSelecionadas = array_slice($questoes, 0, $quantidade);

        $simulado = [
            'materia' => $materia,
            'modo' => $modo,
            'questoes' => $questoesSelecionadas,
            'atual' => 0,
            'respostas' => [],
            'feedback' => []
        ];

        if ($modo === 'exame') {
            $simulado['inicio'] = time();
            $simulado['tempo_total'] = 1 * 60 * 60;
        }

        return $simulado;
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . app_url('bancoperguntas.php'));
        exit;
    }

    if (!csrf_validate(isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null)) {
        header('Location: ' . app_url('bancoperguntas.php'));
        exit;
    }

    if (!isset($_SESSION['usuario']['id'])) {
        header('Location: ' . app_url('login.php'));
        exit;
    }

    $materia = isset($_POST['materia']) ? (string) $_POST['materia'] : '';
    $quantidade = (int) ($_POST['quantidade'] ?? 10);
    $modo = $_POST['modo'] ?? 'estudo';

    $arquivosPorMateria = [
        '1' => 'questoes_microbiologia_refinado.json',
        '2' => 'questoes_biologia_final_v2.json',
    ];

    if ($materia === '' || !isset($arquivosPorMateria[$materia])) {
        throw new Exception('Matéria inválida.');
    }

    $conexao = new Conexao();
    $pdo = $conexao->conectar();
    $usuarioModel = new Usuario($pdo);

    if (!$usuarioModel->usuarioPossuiMateria((int) $_SESSION['usuario']['id'], (int) $materia)) {
        throw new Exception('Acesso não autorizado a esta matéria.');
    }

    if ($modo !== 'estudo' && $modo !== 'exame') {
        $modo = 'estudo';
    }

    $nomeArquivo = $arquivosPorMateria[$materia];
    $jsonPath = __DIR__ . '/../../data/' . $nomeArquivo;

    $factory = new SimulationFactory($jsonPath);
    $novoSimulado = $factory->createSimulationData($materia, $quantidade, $modo);

    $session = new SimulationSession();
    $session->init($novoSimulado);

    header('Location: ' . app_url('questionario.php'));
    exit;
} catch (Exception $e) {
    error_log('CriarController: ' . $e->getMessage());
    header('Location: ' . app_url('bancoperguntas.php'));
    exit;
}
