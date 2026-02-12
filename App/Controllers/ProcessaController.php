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
// Verifica se uma resposta foi selecionada no formulário (radio button)
if (isset($_POST['resposta'])) {

    $indice = $_SESSION['simulado']['atual'];
    $respostaUsuario = $_POST['resposta'];

    // salva resposta
    $_SESSION['simulado']['respostas'][$indice] = $respostaUsuario;

    // pega questão atual
    $questao = $_SESSION['simulado']['questoes'][$indice];

    $respostaCorreta = $questao['resposta_correta'];
    $acertou = ($respostaUsuario === $respostaCorreta);

    // salva feedback (MODO ESTUDO)
    if ($_SESSION['simulado']['modo'] === 'estudo') {
        $_SESSION['simulado']['feedback'][$indice] = [
            'acertou'            => $acertou,
            'resposta_usuario'   => $respostaUsuario,
            'resposta_correta'   => $respostaCorreta,
            'feedback'           => $questao['feedback'] ?? 'Sem explicação disponível'
        ];
    }
}

/* ================= AVANÇAR ================= */
// Se não foi 'ir' (mapa) nem 'voltar', assume-se que é para avançar. Incrementa o índice para a próxima questão.
if (isset($_POST['avancar'])) {

    $_SESSION['simulado']['atual']++;

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