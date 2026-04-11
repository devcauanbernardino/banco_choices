<?php

declare(strict_types=1);

/**
 * ARQUIVO: processa_perfil.php
 * OBJETIVO: Ponto de entrada para o processamento do perfil usando Orientação a Objetos.
 */

require_once __DIR__ . '/../../config/public_url.php';
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Controllers/PerfilController.php';
require_once __DIR__ . '/../Session/SimulationSession.php';

// 2. Inicializamos os objetos
$objConexao = new Conexao();
$db = $objConexao->conectar();

$userModel = new Usuario($db);
$session = new SimulationSession();

// 3. Instanciamos o controlador de perfil
$controller = new PerfilController($userModel, $session);

// 4. Delegamos a ação para o controlador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->handleUpdate($_POST);
} else {
    header('Location: ' . app_url('perfil.php'));
    exit;
}

?>
