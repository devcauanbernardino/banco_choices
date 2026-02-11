<?php
session_start();

// Proteção básica
if (!isset($_SESSION['simulado'])) {
    header('Location: index.php');
    exit;
}

// Verifica se veio resposta
if (!isset($_POST['resposta'])) {
    header('Location: questionario.php');
    exit;
}

$indice = $_SESSION['simulado']['atual'];
$perguntaAtual = $_SESSION['simulado']['questoes'][$indice];

// Resposta do usuário
$respostaUsuario = $_POST['resposta'];

// Gabarito (ajuste o nome da chave se no JSON for diferente)
$respostaCorreta = $perguntaAtual['resposta_correta'];

// Verifica se acertou
if ($respostaUsuario === $respostaCorreta) {
    $_SESSION['simulado']['acertos']++;
} else {
    $_SESSION['simulado']['erros']++;
}

// Avança para a próxima pergunta
$_SESSION['simulado']['atual']++;

// Verifica se acabou o simulado
$totalPerguntas = count($_SESSION['simulado']['questoes']);

if ($_SESSION['simulado']['atual'] >= $totalPerguntas) {
    header('Location: ../Views/resultado.php');
    exit;
}

// Volta para o questionário
header('Location: ../Views/questionario.php');
exit;

?>