<?php

declare(strict_types=1);

/**
 * Cria ou atualiza um utilizador de teste com apenas ALGUMAS matérias (útil para testar
 * comprar-materias.php no painel). Remove vínculos antigos e deixa só os IDs indicados.
 *
 * O e-mail deve estar em config/test_users.php (test_users_skip_default_materias) para o
 * login/sidebar não voltarem a forçar as matérias 1 e 2.
 *
 * Uso: php scripts/criar-usuario-teste-sem-materia.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Apenas CLI.';
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/config/conexao.php';
require_once $root . '/App/Models/Usuario.php';

// Ajuste: só matéria 1 → falta a 2 no fluxo "Comprar matérias"
$email = 'teste.1materia@example.com';
$nome = 'Teste Uma Matéria';
$senha = 'Teste@Banco2026!';
/** @var list<int> $materiasDesejadas IDs em materias (ex.: 1 = uma disciplina, sem a 2) */
$materiasDesejadas = [1];

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
} else {
    if (!$usuarioModel->cadastrarComMaterias($nome, $email, $senha, $materiasDesejadas)) {
        fwrite(STDERR, "Erro ao criar utilizador.\n");
        exit(1);
    }
    $existente = $usuarioModel->buscarPorEmail($email);
    $id = (int) ($existente['id'] ?? 0);
    if ($id <= 0) {
        fwrite(STDERR, "Utilizador criado mas ID inválido.\n");
        exit(1);
    }
    echo "Conta criada.\n";
}

$stmt = $db->prepare('DELETE FROM usuarios_materias WHERE usuario_id = ?');
$stmt->execute([$id]);

foreach ($materiasDesejadas as $mid) {
    $mid = (int) $mid;
    if ($mid > 0) {
        try {
            $usuarioModel->vincularMateria($id, $mid);
        } catch (Throwable $e) {
            fwrite(STDERR, "Erro ao vincular matéria {$mid}: " . $e->getMessage() . "\n");
            exit(1);
        }
    }
}

echo "Matérias do utilizador definidas para: " . implode(', ', $materiasDesejadas) . "\n\n";
echo "Login em public/login.php:\n";
echo "  E-mail: {$email}\n";
echo "  Senha:  {$senha}\n";
echo "\nNo painel: Comprar matérias → deve aparecer a(s) matéria(s) que faltam na BD.\n";
