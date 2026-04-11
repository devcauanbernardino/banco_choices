<?php

declare(strict_types=1);

/**
 * Legado: URL antiga do passo "plano" no extra de matérias.
 * O fluxo atual resolve o plano automaticamente e vai direto ao checkout.
 */
require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/signup_flow.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Usuario.php';

if (!isset($_SESSION['usuario']['id'])) {
    header('Location: ' . app_url('login.php'));
    exit;
}

$materiasIds = array_values(
    array_unique(array_map('intval', (array) ($_SESSION['addon_materias'] ?? [])))
);
$materiasIds = array_values(array_filter($materiasIds, static fn (int $id): bool => $id > 0));
if ($materiasIds === []) {
    header('Location: ' . app_url('comprar-materias.php'));
    exit;
}

$uid = (int) $_SESSION['usuario']['id'];
$conexao = new Conexao();
$pdo = $conexao->conectar();
$usuarioModel = new Usuario($pdo);
$ultimoPlanoId = $usuarioModel->buscarUltimoPlanoIdParaUsuarioId($uid);

$_SESSION['addon_plan'] = addon_resolve_plan_for_extra_materias($ultimoPlanoId);

header('Location: ' . app_url('checkout-addon.php'));
exit;
