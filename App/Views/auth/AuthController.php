<?php

require_once __DIR__ . '/../../../config/public_url.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ' . app_url('login.php?error=naologado'));
    exit;
}
