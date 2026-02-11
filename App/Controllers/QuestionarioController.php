<?php

session_start();

if (!isset($_SESSION['simulado'])) {
    header('Location: dashboard.php');
    exit;
}



$simulado = $_SESSION['simulado'];
$indiceAtual = $simulado['atual'];
$questoes = $simulado['questoes'];

$totalQuestoes = count($questoes);

if (!isset($questoes[$indiceAtual])) {
    $indiceAtual = 0;
    $_SESSION['simulado']['atual'] = 0;
}

$questao = $questoes[$indiceAtual];
// echo '<pre>';
// print_r($questao);
// echo '</pre>';

require_once __DIR__ . '/../Views/questionario.php';

?>