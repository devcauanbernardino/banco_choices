<?php

session_start();

// Remove todos os dados da sessão
$_SESSION = [];

// Destroi a sessão
session_destroy();

// Redireciona para a tela de login
header('Location: /login.php');
exit;
