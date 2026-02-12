<?php
session_start();

if (!isset($_SESSION['simulado'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['simulado']['modo'] === 'exame') {
    $inicio = $_SESSION['simulado']['inicio'];
    $tempoTotal = $_SESSION['simulado']['tempo_total'];

    if (time() - $inicio >= $tempoTotal) {
        header('Location: ../Views/resultado.php');
        exit;
    }
}

$simulado = $_SESSION['simulado'];

$indiceAtual = $simulado['atual'] ?? 0;
$questoes = $simulado['questoes'] ?? [];
$respostas = $simulado['respostas'] ?? [];
$modo = $simulado['modo'];

if ($modo === 'exame') {
    $inicio = $simulado['inicio'];
    $tempoTota = $simulado['tempo_total'];

    $agora = time();
    $tempoPassado = $agora - $inicio;
    $tempoRestante = max(0, $tempoTotal - $tempoPassado);

}


if (empty($questoes)) {
    die('Erro: nenhuma questão carregada.');
}

if (!isset($questoes[$indiceAtual])) {
    $indiceAtual = 0;
    $_SESSION['simulado']['atual'] = 0;
}

$questao = $questoes[$indiceAtual];

?>