<?php

require_once __DIR__ . '/../../config/public_url.php';

session_start();

// Remove todos os dados da sessão
$_SESSION = [];

// Destroi a sessão
session_destroy();

// Redireciona para a tela de login
header('Location: ' . app_url('login.php'));
exit;
