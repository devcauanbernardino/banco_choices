<?php
require_once __DIR__ . '/../Models/Question.php';

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

    public function isActive(): bool
    {
        // Verifica se existe um simulado na sessão
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public function set(string $key, $value): void
    {
        // Define um valor no array do simulado na sessão
        $_SESSION[self::SESSION_KEY][$key] = $value;
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        // Retorna um valor do array do simulado ou null se não existir
        return $_SESSION[self::SESSION_KEY][$key] ?? null;
    }
}

/**
 * Controlador responsável pelo processamento das ações do usuário durante o simulado.
 */
class ProcessaController
{
    private SimulationSession $session;

    public function __construct(SimulationSession $session)
    {
        // Injeta a dependência da sessão
        $this->session = $session;
    }

    public function handleRequest(array $postData): void
    {
        // Se não houver simulado ativo, redireciona para o início
        if (!$this->session->isActive()) {
            $this->redirect('index.php');
        }

        // 1. Processa a Resposta (se houver)
        if (isset($postData['resposta'])) {
            $this->saveUserAnswer($postData['resposta']);
        }

        // 2. Processa a Navegação
        if (isset($postData['ir'])) {
            // Navegação pelo mapa de questões
            $this->jumpToQuestion((int)$postData['ir']);
        } elseif (isset($postData['avancar'])) {
            // Botão "Próxima"
            $this->nextQuestion();
        } elseif (isset($postData['voltar'])) {
            // Botão "Anterior"
            $this->previousQuestion();
        }

        // Redirecionamento padrão
        $this->redirect('../Views/questionario.php');
    }

    private function saveUserAnswer(string $userAnswer): void
    {
        // Obtém o índice atual, as questões e o modo do simulado
        $currentIndex = (int)$this->session->get('atual');
        $questoes = (array)($this->session->get('questoes') ?? []);
        $modo = (string)($this->session->get('modo') ?? 'estudo');

        // Salva a resposta do usuário
        $respostas = (array)($this->session->get('respostas') ?? []);
        // Armazena a resposta no índice correspondente
        $respostas[$currentIndex] = $userAnswer;
        // Atualiza a sessão com as novas respostas
        $this->session->set('respostas', $respostas);

        // Se for modo estudo, gera o feedback imediato
        if ($modo === 'estudo' && isset($questoes[$currentIndex])) {
            // Cria objeto da questão atual para facilitar a verificação
            $questao = new Question((array)$questoes[$currentIndex]);
            
            // Recupera feedbacks existentes
            $feedbacks = (array)($this->session->get('feedback') ?? []);
            // Salva o feedback para a questão atual
            $feedbacks[$currentIndex] = [
                'acertou'            => $questao->isCorrect($userAnswer),
                'resposta_usuario'   => $userAnswer,
                'resposta_correta'   => $questao->getCorrectAnswer(),
                'feedback'           => $questao->getFeedback()
            ];
            // Atualiza a sessão com os feedbacks
            $this->session->set('feedback', $feedbacks);
        }
    }

    private function jumpToQuestion(int $index): void
    {
        // Define o índice atual para o valor recebido
        $this->session->set('atual', $index);
        // Redireciona para a view
        $this->redirect('../Views/questionario.php');
    }

    private function nextQuestion(): void
    {
        // Pega o índice atual e calcula o próximo
        $currentIndex   = (int)$this->session->get('atual');
        $totalQuestoes  = count((array)($this->session->get('questoes') ?? []));
        $nextIndex      = $currentIndex + 1;

        // Se chegou ao fim das questões, vai para o resultado
        if ($nextIndex >= $totalQuestoes) {
            $this->redirect('../Views/resultado.php');
        }

        // Caso contrário, atualiza o índice e recarrega a página
        $this->session->set('atual', $nextIndex);
        $this->redirect('../Views/questionario.php');
    }

    private function previousQuestion(): void
    {
        // Pega o índice atual
        $currentIndex = (int)$this->session->get('atual');
        // Calcula o anterior, garantindo que não seja menor que 0
        $prevIndex    = max(0, $currentIndex - 1);
        
        // Atualiza a sessão e redireciona
        $this->session->set('atual', $prevIndex);
        $this->redirect('../Views/questionario.php');
    }

    private function redirect(string $url): void
    {
        // Envia cabeçalho de redirecionamento e encerra o script
        header("Location: $url");
        exit;
    }
}

// --- Execução do Script ---

// Inicializa a sessão
$session = new SimulationSession();
// Inicializa o controlador
$controller = new ProcessaController($session);
// Processa a requisição POST
$controller->handleRequest($_POST);
?>


<!-- <?php
// // Inicia uma nova sessão ou resume a sessão existente para acessar as variáveis $_SESSION
// session_start();

// // Verifica se a variável de sessão 'simulado' existe. Se não existir, significa que não há um simulado em andamento.
// if (!isset($_SESSION['simulado'])) {
//     // Redireciona o usuário para a página inicial (index.php) se não houver simulado
//     header('Location: index.php');
//     // Encerra a execução do script imediatamente após o redirecionamento
//     exit;
// }

// $simulado = $_SESSION['simulado'];
// $indice = $simulado['atual'];


// /* ================= MAPA ================= */
// // Verifica se o formulário enviou um campo chamado 'ir' (clique no mapa de questões)
// if (isset($_POST['ir'])) {
//     // Atualiza o índice da questão atual na sessão com o valor enviado pelo botão do mapa
//     $_SESSION['simulado']['atual'] = (int) $_POST['ir'];
//     // Redireciona para a visualização do questionário para mostrar a questão selecionada
//     header('Location: ../Views/questionario.php');
//     // Encerra o script
//     exit;
// }



// /* ================= SALVAR RESPOSTA ================= */
// // Verifica se o formulário enviou uma resposta selecionada
// // Isso acontece quando o usuário marca uma alternativa
// if (isset($_POST['resposta'])) {
//     // Recupera o índice da questão atual direto da sessão
//     $indice = $_SESSION['simulado']['atual'];

//     // Armazena a resposta escolhida pelo usuário (ex: A, B, C ou D)
//     $respostaUsuario = $_POST['resposta'];

//     // Salva a resposta do usuário no array de respostas do simulado
//     // O índice garante que cada resposta fique ligada à sua questão
//     $_SESSION['simulado']['respostas'][$indice] = $respostaUsuario;

//     // Recupera os dados da questão atual
//     $questao = $_SESSION['simulado']['questoes'][$indice];

//     // Obtém a resposta correta da questão
//     $respostaCorreta = $questao['resposta_correta'];

//     // Verifica se o usuário acertou a questão
//     // Retorna true ou false
//     $acertou = ($respostaUsuario === $respostaCorreta);

//     // Se o modo do simulado for "estudo",
//     // salvamos feedback detalhado para mostrar na tela
//     if ($_SESSION['simulado']['modo'] === 'estudo') {
//         // Armazena o feedback para a questão atual, incluindo se acertou, a resposta do usuário, a resposta correta e uma explicação
//         $_SESSION['simulado']['feedback'][$indice] = [
//             // Indica se a resposta está correta ou não
//             'acertou'            => $acertou,

//             // Guarda a alternativa escolhida pelo usuário
//             'resposta_usuario'   => $respostaUsuario,

//             // Guarda a alternativa correta da questão
//             'resposta_correta'   => $respostaCorreta,

//             // Texto explicativo da questão
//             // Se não existir, mostra uma mensagem padrão
//             'feedback'           => $questao['feedback'] ?? 'Sem explicação disponível'
//         ];
//     }
// }

// /* ================= AVANÇAR ================= */
// // Se não foi 'ir' (mapa) nem 'voltar', assume-se que é para avançar. Incrementa o índice para a próxima questão.
// // Verifica se o botão "avançar" foi clicado
// if (isset($_POST['avancar'])) {
//     // Incrementa o índice da questão atual para avançar para a próxima questão
//     $_SESSION['simulado']['atual']++;

//     // Conta quantas questões existem no simulado para verificar se já chegamos ao final
//     $total = count($_SESSION['simulado']['questoes']);

//     if ($_SESSION['simulado']['atual'] >= $total) {
//         header('Location: ../Views/resultado.php');
//         exit;
//     }

//     header('Location: ../Views/questionario.php');
//     exit;
// }

// /* ================= VOLTAR ================= */
// // Verifica se o botão "Anterior" (name='voltar') foi clicado
// if (isset($_POST['voltar'])) {
//     // Decrementa o índice atual para voltar uma questão
//     $_SESSION['simulado']['atual']--;

//     // Garante que o índice não fique negativo (menor que a primeira questão)
//     if ($_SESSION['simulado']['atual'] < 0) {
//         $_SESSION['simulado']['atual'] = 0;
//     }
//     // Redireciona para a visualização do questionário (questão anterior)
//     header('Location: ../Views/questionario.php');
//     // Encerra o script
//     exit;
// }



// // Comentários de debug (código inativo para testes)
// // echo '<pre>';
// // print_r($_POST['resposta']);
// // echo '</pre>';

// // Se ainda houver questões, redireciona para a visualização do questionário (próxima questão)
// header('Location: ../Views/questionario.php');
// // Encerra o script
// exit;

?> -->