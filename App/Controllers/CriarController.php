<?php
session_start();

// print_r($_POST);

// recebe dados do formulário
$materia = $_POST['materia'];
$quantidade = (int) $_POST['quantidade'];
$modo = $_POST['modo'];

// carrega JSON
$json = file_get_contents(__DIR__ . '/../../data/questoes_microbiologia_refinado.json');
$dados = json_decode($json, true);

$questoes = $dados['questoes'];

// embaralha
shuffle($questoes);

// corta quantidade escolhida
$questoesSelecionadas = array_slice($questoes, 0, $quantidade);

// cria simulado
$_SESSION['simulado'] = [
    'materia'   => $materia,
    'modo'      => $modo,
    'questoes'  => $questoesSelecionadas,
    'atual'     => 0,
    'respostas' => []
];

if ($modo === 'exame') {
    $_SESSION['simulado']['inicio'] = time(); // timestamp atual
    $_SESSION['simulado']['tempo_total'] = 1 * 60 * 60; // 2 horas (em segundos)
}



// redireciona
header('Location: ../Views/questionario.php');
exit;



?>

