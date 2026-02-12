<?php
session_start();

if (!isset($_SESSION['simulado'])) {
    header('Location: index.php');
    exit;
}

$simulado = $_SESSION['simulado'];

$indiceAtual = $simulado['atual'] ?? 0;
$questoes    = $simulado['questoes'] ?? [];
$respostas  = $simulado['respostas'] ?? [];
$modo       = $simulado['modo'] ?? 'estudo';
$feedback   = $simulado['feedback'][$indiceAtual] ?? null;

if (empty($questoes)) {
    die('Erro: nenhuma questão carregada.');
}

if (!isset($questoes[$indiceAtual])) {
    $indiceAtual = 0;
    $_SESSION['simulado']['atual'] = 0;
}

$questao = $questoes[$indiceAtual];

/* ===== CONTROLE DE TEMPO (MODO EXAME) ===== */
if ($modo === 'exame') {
    $inicio = $simulado['inicio'];
    $tempoTotal = $simulado['tempo_total'];

    if (time() - $inicio >= $tempoTotal) {
        header('Location: ../Views/resultado.php');
        exit;
    }

    $tempoRestante = max(0, $tempoTotal - (time() - $inicio));
}
