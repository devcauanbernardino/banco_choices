<?php 
/**
 * ARQUIVO: sidebar.php
 * OBJETIVO: Navegação lateral do sistema com rodapé aprimorado.
 */
$pagina_atual = basename($_SERVER['PHP_SELF']);
?>

<style>
    /* Estilos da Sidebar */
    .sidebar {
        width: 260px;
        background-color: #1a1a1a;
        color: #fff;
        z-index: 1000;
        transition: all 0.3s;
        box-shadow: 4px 0 10px rgba(0,0,0,0.1);
    }

    .sidebar .logo-area {
        padding: 2rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .sidebar .brand-name {
        font-size: 1.25rem;
        letter-spacing: -0.5px;
        background: linear-gradient(135deg, #fff 0%, #a342cd 100%);
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .sidebar nav {
        padding: 1.5rem 1rem;
    }

    .sidebar nav a, .sidebar .footer-area a {
        display: flex;
        align-items: center;
        padding: 0.85rem 1.25rem;
        color: rgba(255,255,255,0.6);
        text-decoration: none;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .sidebar nav a:hover, .sidebar .footer-area a:hover {
        color: #fff;
        background-color: rgba(255,255,255,0.05);
    }

    .sidebar nav a.active, .sidebar .footer-area a.active {
        color: #fff;
        background-color: #6a0392;
        box-shadow: 0 4px 12px rgba(106, 3, 146, 0.3);
    }

    .sidebar .material-icons {
        margin-right: 12px;
        font-size: 1.4rem;
    }

    /* AJUSTES NA FOOTER AREA */
    .sidebar .footer-area {
        padding: 1.25rem 1rem;
        margin-top: auto; /* Garante que fique no fundo */
        border-top: 1px solid rgba(255,255,255,0.08);
        background-color: rgba(0,0,0,0.2); /* Leve destaque de fundo */
    }

    .sidebar .footer-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255,255,255,0.3);
        padding-left: 1.25rem;
        margin-bottom: 0.75rem;
        display: block;
        font-weight: 700;
    }

    .sidebar .logout-link {
        color: #ff5c5c !important;
        margin-top: 0.5rem;
    }

    .sidebar .logout-link:hover {
        background-color: rgba(255, 92, 92, 0.1) !important;
        color: #ff8080 !important;
    }

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); }
        .sidebar.show { transform: translateX(0); }
    }
</style>

<!-- Sidebar -->
<aside class="sidebar position-fixed d-flex flex-column h-100">
    
    <!-- Logo -->
    <div class="logo-area">
        <div class="d-flex align-items-center gap-2">
            <h4 class="brand-name fw-bold mb-0">Banco de Choices</h4>
        </div>
    </div>

    <!-- MENU PRINCIPAL -->
    <nav class="flex-grow-1">
        <a class="<?= $pagina_atual === 'dashboard.php' ? 'active' : ''?>" href="./dashboard.php">
            <span class="material-icons">dashboard</span>
            Dashboard
        </a>

        <a class="<?= $pagina_atual === 'estatisticas.php' ? 'active' : ''?>" href="./estatisticas.php">
            <span class="material-icons">bar_chart</span>
            Estatísticas
        </a>

        <a class="<?= $pagina_atual === 'bancoperguntas.php' ? 'active' : ''?>" href="./bancoperguntas.php">
            <span class="material-icons">quiz</span>
            Banco de Perguntas
        </a>

        <a class="<?= $pagina_atual === 'simulados.php' ? 'active' : ''?>" href="./simulados.php">
            <span class="material-icons">assignment</span>
            Meus Simulados
        </a>
    </nav>

    <!-- RODAPÉ DA SIDEBAR AJUSTADO -->
    <div class="footer-area">
        <span class="footer-label">Conta e Acesso</span>
        
        <a class="<?= $pagina_atual === 'perfil.php' ? 'active' : ''?>" href="./perfil.php">
            <span class="material-icons">person</span>
            Meu Perfil
        </a>

        <a class="logout-link" href="/banco_choices/App/Controllers/LogoutController.php">
            <span class="material-icons">logout</span>
            Sair
        </a>
    </div>
</aside>
