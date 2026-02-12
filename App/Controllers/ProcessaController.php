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
    // Salva a resposta escolhida no array de respostas da sessão, usando o índice da questão atual como chave
    $_SESSION['simulado']['respostas'][$_SESSION['simulado']['atual']] = $_POST['resposta'];
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

/* ================= AVANÇAR ================= */
// Se não foi 'ir' (mapa) nem 'voltar', assume-se que é para avançar. Incrementa o índice para a próxima questão.
$_SESSION['simulado']['atual']++;

// Conta o número total de questões no simulado
$total = count($_SESSION['simulado']['questoes']);

// Verifica se o índice atual ultrapassou ou igualou o total de questões (significa que acabou o simulado)
if ($_SESSION['simulado']['atual'] >= $total) {
    // Redireciona para a página de resultados
    header('Location: ../Views/resultado.php');
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