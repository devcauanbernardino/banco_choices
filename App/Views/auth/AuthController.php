<?php 

if (!isset($_SESSION['usuario'])) {
    header('Location: /banco_choices/public/login.php?error=naologado');
    exit;
}

?>