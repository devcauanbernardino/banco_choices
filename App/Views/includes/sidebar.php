<?php 

$pagina_atual = basename($_SERVER['PHP_SELF']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


?>

<!-- Sidebar -->
<aside class="sidebar position-fixed p-4 d-flex flex-column" style="height: 100vh;">
    <h4 class="text-white fw-bold mb-4">Banco de Choices</h4>

    <!-- MENU PRINCIPAL -->
    <nav class="d-flex flex-column gap-2">
        <a class="p-3 <?= $pagina_atual === 'dashboard.php' ? 'active' : ''?>" href="./dashboard.php">
            <span class="material-icons align-middle me-2">dashboard</span>
            Dashboard
        </a>

        <a class="p-3" href="#">
            <span class="material-icons align-middle me-2">bar_chart</span>
            Estatísticas
        </a>

        <a class="p-3 <?= $pagina_atual === 'bancoperguntas.php' ? 'active' : ''?>" href="./bancoperguntas.php">
            <span class="material-icons align-middle me-2">quiz</span>
            Banco de Perguntas
        </a>

        <a class="p-3" href="#">
            <span class="material-icons align-middle me-2">assignment</span>
            Simulados
        </a>
    </nav>

    <div class="mt-auto">
        <hr class="text-secondary">

        <a class="p-3 d-block" href="#">
            <span class="material-icons align-middle me-2">person</span>
            Perfil
        </a>

        <a class="p-3 d-block text-danger" href="/banco_choices/App/Controllers/LogoutController.php">
            <span class="material-icons align-middle me-2">logout</span>
            Sair
        </a>
    </div>
</aside>
