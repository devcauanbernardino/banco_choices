<?php

require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Usuario.php';

use App\Models\Usuario;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /public/cadastro.php');
    exit;
}

$nome  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmaSenha = $_POST['confirma-senha'] ?? '';

// validações
if ($nome === '' || $email === '' || $senha === '' || $confirmaSenha === '') {
    header('Location: ../public/cadastro.php?error=Todos los campos son obligatorios');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../public/cadastro.php?error=E-mail inválido');
    exit;
}

if ($senha !== $confirmaSenha) {
    header('Location: ../public/cadastro.php?error=As senhas não coincidem');
    exit;
}

$usuario = new Usuario($conn);

if ($usuario->buscarPorEmail($email)) {
    header('Location: ../public/cadastro.php?error=E-mail já cadastrado');
    exit;
}

$usuario->cadastrar($nome, $email, $senha);

header('Location: ../public/cadatro.php?success=Conta criada com sucesso');
exit;

?>