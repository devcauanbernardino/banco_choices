<?php

declare(strict_types=1);

/**
 * Cria ou redefine o utilizador de teste local (matérias 1 e 2).
 * Uso: php scripts/criar-usuario-teste.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Apenas CLI.';
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/config/conexao.php';
require_once $root . '/App/Models/Usuario.php';

// Ajuste aqui se precisares
$email = 'teste.banco@example.com';
$nome = 'Usuário Teste';
$senha = 'Teste@Banco2026!';
$materias = [1, 2];

$conexao = new Conexao();
$db = $conexao->conectar();
$usuarioModel = new Usuario($db);

$existente = $usuarioModel->buscarPorEmail($email);

if ($existente !== null) {
    $id = (int) $existente['id'];
    if (!$usuarioModel->atualizarSenha($id, $senha)) {
        fwrite(STDERR, "Erro ao atualizar senha.\n");
        exit(1);
    }
    $usuarioModel->garantirMateriasParaUsuario($id, $materias);
    echo "Conta já existia — senha redefinida e matérias garantidas.\n\n";
} else {
    if (!$usuarioModel->cadastrarComMaterias($nome, $email, $senha, $materias)) {
        fwrite(STDERR, "Erro ao criar utilizador.\n");
        exit(1);
    }
    echo "Conta criada com sucesso.\n\n";
}

echo "Login em public/login.php:\n";
echo "  E-mail: {$email}\n";
echo "  Senha:  {$senha}\n";
