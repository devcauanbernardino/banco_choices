<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Usuario.php';


$database = new Conexao();
$conn = $database->conectar();

$usuario = new Usuario($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /banco_choices/public/cadastro.php?error=acessoinvalido');
    exit;
}

$nome  = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmaSenha = $_POST['confirma-senha'] ?? '';

// validações
if ($nome === '' || $email === '' || $senha === '' || $confirmaSenha === '') {
    header('Location: /banco_choices/public/cadastro.php?error=camposobrigatorios');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /banco_choices/public/cadastro.php?error=emailinvalido');
    exit;
}

if ($senha !== $confirmaSenha) {
    header('Location: /banco_choices/public/cadastro.php?error=naocoincidem');
    exit;
}

if ($usuario->buscarPorEmail($email)) {
    header('Location: /banco_choices/public/public/cadastro.php?error=emailcadastrado');
    exit;
}

$usuario->cadastrar($nome, $email, $senha);

header('Location: /banco_choices/public/cadastro.php?success=success');
exit;

?>