<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Usuario.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /banco_choices/public/login.php?error=acessoinvalido');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    header('Location: /banco_choices/public/login.php?error=camposobrigatorios');
    exit;
}

$db = new Conexao();
$conn = $db->conectar();

$usuarioModel = new Usuario($conn);
$usuario = $usuarioModel->autenticar($email, $senha);

if (!$usuario) {
    header('Location: /banco_choices/public/login.php?error=logininvalido');
    exit;
}

// cria sessão
$_SESSION['usuario'] = $usuario;

// redireciona para dashboard
header('Location: /banco_choices/App/Views/dashboard.php');
exit;


?>