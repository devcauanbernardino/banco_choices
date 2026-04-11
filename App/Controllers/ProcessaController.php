<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../Models/Question.php';
require_once __DIR__ . '/../Session/SimulationSession.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Usuario.php';

/**
 * Controlador responsável pelo processamento das ações do usuário durante o simulado.
 */
class ProcessaController
{
    private SimulationSession $session;

    public function __construct(SimulationSession $session)
    {
        $this->session = $session;
    }

    public function handleRequest(array $postData): void
    {
        if (!csrf_validate(isset($postData['_csrf']) ? (string) $postData['_csrf'] : null)) {
            $this->redirect('/questionario.php');
        }

        if (!$this->session->isActive()) {
            $this->redirect('index.php');
        }

        $this->ensureUsuarioAutorizadoNoSimulado();

        if (isset($postData['resposta'])) {
            $this->saveUserAnswer($postData['resposta']);
        }

        if (isset($postData['ir'])) {
            $this->jumpToQuestion((int) $postData['ir']);
        } elseif (isset($postData['avancar'])) {
            $this->nextQuestion();
        } elseif (isset($postData['voltar'])) {
            $this->previousQuestion();
        }

        $this->redirect('/questionario.php');
    }

    private function ensureUsuarioAutorizadoNoSimulado(): void
    {
        if (!isset($_SESSION['usuario']['id'])) {
            $this->session->clear();
            $this->redirect('/login.php');
        }

        $materiaCodigo = $this->session->get('materia');
        if ($materiaCodigo === null || $materiaCodigo === '' || !is_numeric($materiaCodigo)) {
            return;
        }

        $pdo = (new Conexao())->conectar();
        $usuarioModel = new Usuario($pdo);

        if (!$usuarioModel->usuarioPossuiMateria((int) $_SESSION['usuario']['id'], (int) $materiaCodigo)) {
            $this->session->clear();
            $this->redirect('/bancoperguntas.php');
        }
    }

    private function saveUserAnswer(string $userAnswer): void
    {
        $currentIndex = (int) $this->session->get('atual');
        $questoes = (array) ($this->session->get('questoes') ?? []);
        $modo = (string) ($this->session->get('modo') ?? 'estudo');

        $respostas = (array) ($this->session->get('respostas') ?? []);
        $respostas[$currentIndex] = $userAnswer;
        $this->session->set('respostas', $respostas);

        if ($modo === 'estudo' && isset($questoes[$currentIndex])) {
            $questao = new Question((array) $questoes[$currentIndex]);

            $feedbacks = (array) ($this->session->get('feedback') ?? []);
            $feedbacks[$currentIndex] = [
                'acertou' => $questao->isCorrect($userAnswer),
                'resposta_usuario' => $userAnswer,
                'resposta_correta' => $questao->getCorrectAnswer(),
                'feedback' => $questao->getFeedback()
            ];
            $this->session->set('feedback', $feedbacks);
        }
    }

    private function jumpToQuestion(int $index): void
    {
        $this->session->set('atual', $index);
        $this->redirect('/questionario.php');
    }

    private function nextQuestion(): void
    {
        $currentIndex = (int) $this->session->get('atual');
        $totalQuestoes = count((array) ($this->session->get('questoes') ?? []));
        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= $totalQuestoes) {
            $this->redirect('/resultado.php');
        }

        $this->session->set('atual', $nextIndex);
        $this->redirect('/questionario.php');
    }

    private function previousQuestion(): void
    {
        $currentIndex = (int) $this->session->get('atual');
        $prevIndex = max(0, $currentIndex - 1);

        $this->session->set('atual', $prevIndex);
        $this->redirect('/questionario.php');
    }

    /** Fim do tempo no modo exame (redirecionamento do cliente). */
    public function handleTimeout(): void
    {
        if (!$this->session->isActive()) {
            $this->redirect('/bancoperguntas.php');
        }

        $this->ensureUsuarioAutorizadoNoSimulado();

        $modo = (string) ($this->session->get('modo') ?? '');
        if ($modo === 'exame') {
            $this->redirect('/resultado.php');
        }

        $this->redirect('/questionario.php');
    }

    private function redirect(string $url): void
    {
        if (preg_match('#^https?://#i', $url)) {
            header('Location: ' . $url);
            exit;
        }
        $path = ltrim($url, '/');
        header('Location: ' . app_url($path));
        exit;
    }
}

$session = new SimulationSession();
$controller = new ProcessaController($session);

if (isset($_GET['timeout']) && (string) $_GET['timeout'] !== '' && (string) $_GET['timeout'] !== '0') {
    $controller->handleTimeout();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleRequest($_POST);
} else {
    header('Location: ' . app_url('questionario.php'));
    exit;
}
