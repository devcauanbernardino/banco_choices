<?php
// Inicia uma nova sessão ou resume a sessão existente para acessar as variáveis $_SESSION
session_start();

// Verifica se a variável de sessão 'simulado' existe. Se não existir, significa que não há um simulado em andamento.
if (!isset($_SESSION['simulado'])) {
    // Redireciona o usuário para a página inicial (index.php) se não houver simulado
    header('Location: index.php');
    // Encerra a execução do script imediatamente após o redirecionamento
    exit;
}

$simulado = $_SESSION['simulado'];
$indice = $simulado['atual'];


/* ================= MAPA ================= */
// Verifica se o formulário enviou um campo chamado 'ir' (clique no mapa de questões)
if (isset($_POST['ir'])) {
    // Atualiza o índice da questão atual na sessão com o valor enviado pelo botão do mapa
    $_SESSION['simulado']['atual'] = (int) $_POST['ir'];
    // Redireciona para a visualização do questionário para mostrar a questão selecionada
    header('Location: ../Views/questionario.php');
    // Encerra o script
    exit;
}



/* ================= SALVAR RESPOSTA ================= */
// Verifica se o formulário enviou uma resposta selecionada
// Isso acontece quando o usuário marca uma alternativa
if (isset($_POST['resposta'])) {
    // Recupera o índice da questão atual direto da sessão
    $indice = $_SESSION['simulado']['atual'];

    // Armazena a resposta escolhida pelo usuário (ex: A, B, C ou D)
    $respostaUsuario = $_POST['resposta'];

    // Salva a resposta do usuário no array de respostas do simulado
    // O índice garante que cada resposta fique ligada à sua questão
    $_SESSION['simulado']['respostas'][$indice] = $respostaUsuario;

    // Recupera os dados da questão atual
    $questao = $_SESSION['simulado']['questoes'][$indice];

    // Obtém a resposta correta da questão
    $respostaCorreta = $questao['resposta_correta'];

    // Verifica se o usuário acertou a questão
    // Retorna true ou false
    $acertou = ($respostaUsuario === $respostaCorreta);

    // Se o modo do simulado for "estudo",
    // salvamos feedback detalhado para mostrar na tela
    if ($_SESSION['simulado']['modo'] === 'estudo') {
        // Armazena o feedback para a questão atual, incluindo se acertou, a resposta do usuário, a resposta correta e uma explicação
        $_SESSION['simulado']['feedback'][$indice] = [
            // Indica se a resposta está correta ou não
            'acertou'            => $acertou,

            // Guarda a alternativa escolhida pelo usuário
            'resposta_usuario'   => $respostaUsuario,

            // Guarda a alternativa correta da questão
            'resposta_correta'   => $respostaCorreta,

            // Texto explicativo da questão
            // Se não existir, mostra uma mensagem padrão
            'feedback'           => $questao['feedback'] ?? 'Sem explicação disponível'
        ];
    }
}

/* ================= AVANÇAR ================= */
// Se não foi 'ir' (mapa) nem 'voltar', assume-se que é para avançar. Incrementa o índice para a próxima questão.
// Verifica se o botão "avançar" foi clicado
if (isset($_POST['avancar'])) {
    // Incrementa o índice da questão atual para avançar para a próxima questão
    $_SESSION['simulado']['atual']++;

    // Conta quantas questões existem no simulado para verificar se já chegamos ao final
    $total = count($_SESSION['simulado']['questoes']);

    if ($_SESSION['simulado']['atual'] >= $total) {
        header('Location: ../Views/resultado.php');
        exit;
    }

    header('Location: ../Views/questionario.php');
    exit;
}

/* ================= VOLTAR ================= */
// Verifica se o botão "Anterior" (name='voltar') foi clicado
if (isset($_POST['voltar'])) {
    // Decrementa o índice atual para voltar uma questão
    $_SESSION['simulado']['atual']--;

    // Garante que o índice não fique negativo (menor que a primeira questão)
    if ($_SESSION['simulado']['atual'] < 0) {
        $_SESSION['simulado']['atual'] = 0;
    }
    // Redireciona para a visualização do questionário (questão anterior)
    header('Location: ../Views/questionario.php');
    // Encerra o script
    exit;
}



// Comentários de debug (código inativo para testes)
// echo '<pre>';
// print_r($_POST['resposta']);
// echo '</pre>';

// Se ainda houver questões, redireciona para a visualização do questionário (próxima questão)
header('Location: ../Views/questionario.php');
// Encerra o script
exit;

?>