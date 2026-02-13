<?php

/**
 * Representa uma questão individual do simulado.
 */
/**
 * CLASSE: Question
 * OBJETIVO: Representar o objeto "Questão".
 * Por que usar: Em vez de manipular arrays puros, usamos métodos que deixam o código mais legível.
 */

class Question
{
    // Propriedade privada para guardar os dados brutos da questão
    private array $data;

    /**
     * Construtor recebe o array de dados da questão (vinda do JSON).
     */
    public function __construct($data)
    {
        // Atribui os dados recebidos à propriedade privada $data
        $this->data = $data;
    }

    /**
     * Retorna a letra da resposta correta.
     */
    public function getCorrectAnwser()
    {
        // Acessa o array de dados e retorna o valor da chave 'resposta_correta'
        return $this->data['resposta_correta'];
    }

    /**
     * Retorna o texto explicativo (feedback).
     */
    public function getFeedback()
    {
        // Acessa o array de dados e retorna o valor da chave 'feedback'
        return $this->data['feedback'];
    }

     /**
     * Compara uma resposta enviada com a correta e retorna true ou false.
     */
    public function isCorrect($answer)
    {
        // Compara a resposta correta (obtida pelo método getCorrectAnwser) com a resposta fornecida ($answer)
        return $this->getCorrectAnwser() === $answer;
    }

     /**
     * Retorna todos os dados da questão (pergunta, opções, etc).
     */
    public function getData()
    {
        // Retorna o array completo de dados da questão
        return $this->data;
    }

}

/**
 * CLASSE: SimulationTimer
 * OBJETIVO: Lógica matemática do cronômetro.
 */
class SimulationTimer
{
    private int $startTime; // Hora de início (timestamp)
    private int $totalDuration; // Duração total em segundos

    //Serve para recuperar os atributos Hora de início e Duração total em segundos
    public function __construct($startTime, $totalDuration)
    {
        // Define o tempo de início
        $this->startTime = $startTime;
        // Define a duração total permitida
        $this->totalDuration = $totalDuration;

    }

     /**
     * Verifica se o tempo atual já passou do limite permitido.
     */
    public function isExpired()
    {
        // Calcula se a diferença entre o tempo atual e o início é maior ou igual à duração total
        return time() - $this->startTime >= $this->totalDuration;
    }

     /**
     * Calcula quantos segundos ainda restam.
     */
    public function getRemainingTime()
    {
        // Subtrai o tempo decorrido da duração total para saber quanto falta
        return $this->totalDuration - (time() - $this->startTime);
    }

}


/**
 * CLASSE: SimulationSession
 * OBJETIVO: Gerenciar o acesso à sessão do PHP de forma encapsulada.
 */
class SimulationSession
{
    // Chave usada para armazenar os dados do simulado na sessão
    private const SESSION_KEY = 'simulado';

    public function __construct()
    {
        // Verifica se a sessão já foi iniciada, se não, inicia a sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Verifica se existe um simulado ativo na sessão
    public function isActive(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    // Define um valor dentro do array do simulado na sessão
    public function set(string $key, $value): void
    {
        $_SESSION[self::SESSION_KEY][$key] = $value;
    }

    // Obtém um valor do array do simulado na sessão
    public function get(string $key)
    {
        if (isset($_SESSION[self::SESSION_KEY]) && array_key_exists($key, $_SESSION[self::SESSION_KEY])) {
            return $_SESSION[self::SESSION_KEY][$key];
        }
    }

    // Limpa os dados do simulado da sessão
    public function clear(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}


/**
 * Controlador para a lógica e exibição do Questionário.
 */
class QuestionarioController
{
    private SimulationSession $session; // Instância da sessão
    private ?SimulationTimer $timer = null; // Instância do timer (pode ser nulo se não for modo exame)

    // Construtor recebe a sessão injetada
    public function __construct(SimulationSession $session)
    {
        $this->session = $session;
        // Inicializa o timer se necessário
        $this->initializeTimer();
    }

    // Configura o timer se o modo for 'exame'
    private function initializeTimer()
    {
        // Obtém o modo do simulado da sessão
        $modo = $this->session->get('modo');

        // Se for modo exame, cria o objeto SimulationTimer com dados da sessão
        if ($modo === 'exame' && $this->session->get('inicio') !== null) {
            $inicio = (int) $this->session->get('inicio');
            $tempoTotal = (int) $this->session->get('tempo_total');
            $this->timer = new SimulationTimer($inicio, $tempoTotal);
        }
    }

    // Valida se o estado atual permite continuar o questionário
    public function validateState(): void
    {
        // Se não há sessão ativa, redireciona para o início
        if (!$this->session->isActive()) {
            $this->redirect('index.php');
        }

        // Verifica se as questões estão carregadas
        $questoes = $this->session->get('questoes');
        if (empty($questoes)) {
            die('Erro: nenhuma questão carregada.');
        }

        // Se houver timer e o tempo acabou, redireciona para o resultado
        if ($this->timer && $this->timer->isExpired()) {
            $this->redirect('../Views/resultado.php');
        }
    }

    /**
     * Prepara todos os dados necessários para a View.
     */
    public function getViewData(): array
    {
        // Obtém o índice da questão atual
        $indiceAtual = (int) $this->session->get('atual');
        // Obtém a lista de questões
        $questoes = $this->session->get('questoes');

        // Garante que $questoes seja um array
        $questoes = (array) ($this->session->get('questoes') ?? []);

        // Garante que o índice é válido
        if (!isset($questoes[$indiceAtual])) {
            $indiceAtual = 0;
            $this->session->set('atual', 0);
        }

        // Pega os dados da questão atual
        $questaoData = $questoes[$indiceAtual];
        // Cria um objeto Question para facilitar o uso na View
        $questao = new Question($questaoData);

        // Retorna um array associativo com tudo que a View precisa
        return [
            'questao' => $questao, // Objeto da questão atual
            'indiceAtual' => $indiceAtual, // Número da questão atual
            'totalQuestoes' => count($questoes), // Total de questões
            'respostas' => $this->session->get('respostas') ?? [], // Respostas já dadas
            'modo' => $this->session->get('modo') ?? 'estudo', // Modo (estudo/exame)
            'feedback' => ($this->session->get('feedback') ?? [])[$indiceAtual] ?? null, // Feedback se houver
            'tempoRestante' => $this->timer ? $this->timer->getRemainingTime() : null, // Tempo restante se houver timer
        ];
    }

    // Método auxiliar para redirecionamento
    private function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}

// --- Inicialização e Uso ---

 // Cria a sessão
$session = new SimulationSession();
 // Cria o controlador passando a sessão
$controller = new QuestionarioController($session);

// 1. Valida se o simulado é válido e se o tempo não expirou
$controller->validateState();

// 2. Prepara os dados para a View
$viewData = $controller->getViewData();

?>


<!-- // session_start();

// if (!isset($_SESSION['simulado'])) {
//     header('Location: index.php');
//     exit;
// }

// $simulado = $_SESSION['simulado'];

// $indiceAtual = $simulado['atual'] ?? 0;
// $questoes = $simulado['questoes'] ?? [];
// $respostas = $simulado['respostas'] ?? [];
// $modo = $simulado['modo'] ?? 'estudo';
// $feedback = $simulado['feedback'][$indiceAtual] ?? null;

// if (empty($questoes)) {
//     die('Erro: nenhuma questão carregada.');
// }

// if (!isset($questoes[$indiceAtual])) {
//     $indiceAtual = 0;
//     $_SESSION['simulado']['atual'] = 0;
// }

// $questao = $questoes[$indiceAtual];

// /* ===== CONTROLE DE TEMPO (MODO EXAME) ===== */
// if ($modo === 'exame') {
//     $inicio = $simulado['inicio'];
//     $tempoTotal = $simulado['tempo_total'];

//     if (time() - $inicio >= $tempoTotal) {
//         header('Location: ../Views/resultado.php');
//         exit;
//     }

//     $tempoRestante = max(0, $tempoTotal - (time() - $inicio));
// } -->