<?php
/**
 * ARQUIVO: perfil.php
 * OBJETIVO: Exibir e permitir a edição dos dados do usuário logado.
 */

session_start();

// 1. Carregamos as dependências
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../Controllers/DashboardController.php';

// 2. Verificamos se o usuário está logado
if (!isset($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario'];

// 3. Inicializamos o controlador para buscar dados reais de resumo
$objConexao = new Conexao();
$db = $objConexao->conectar();
$dashboard = new DashboardController($db, $usuario['id']);
$stats = $dashboard->getStats();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil | Banco de Choices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    
    <style>
        :root {
            --primary-color: #6a0392;
            --bg-body: #f6f6f8;
        }

        body { background-color: var(--bg-body); }
        .sidebar-space { margin-left: 260px; }

        .profile-header {
            background: linear-gradient(135deg, #6a0392 0%, #a342cd 100%);
            height: 150px;
            border-radius: 20px 20px 0 0;
        }

        .profile-avatar-container {
            margin-top: -75px;
            padding: 0 30px;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border: 5px solid #fff;
            border-radius: 50%;
            background-color: #eee;
            object-fit: cover;
        }

        .card { border: none; border-radius: 16px; }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .text-primary { color: var(--primary-color) !important; }

        @media (max-width: 992px) {
            .sidebar-space { margin-left: 0; }
        }
    </style>
</head>
<body>

<?php require_once 'includes/sidebar.php'; ?>

<main class="sidebar-space p-4">
    
    <div class="container-fluid">
        <!-- Capa e Avatar -->
        <div class="card shadow-sm overflow-hidden mb-4">
            <div class="profile-header"></div>
            <div class="profile-avatar-container d-flex align-items-end justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-end gap-3 flex-wrap">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nome']) ?>&size=150&background=6a0392&color=fff" 
                         alt="Avatar" class="profile-avatar shadow">
                    <div class="mb-2">
                        <h3 class="fw-bold mb-0"><?= htmlspecialchars($usuario['nome']) ?></h3>
                        <p class="text-muted mb-0"><?= htmlspecialchars($usuario['email']) ?></p>
                    </div>
                </div>
                <div class="mb-2">
                    <button class="btn btn-primary px-4 rounded-pill">
                        <i class="material-icons align-middle fs-5 me-1">edit</i> Editar Perfil
                    </button>
                </div>
            </div>
            <div class="card-body mt-3">
                <hr class="text-muted opacity-25">
                <div class="row text-center py-2">
                    <div class="col-4 border-end">
                        <h5 class="fw-bold mb-0 text-primary"><?= $stats['total_simulados'] ?></h5>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Simulados</small>
                    </div>
                    <div class="col-4 border-end">
                        <h5 class="fw-bold mb-0 text-primary"><?= number_format($stats['questoes_respondidas'], 0, ',', '.') ?></h5>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Questões</small>
                    </div>
                    <div class="col-4">
                        <h5 class="fw-bold mb-0 text-primary"><?= $stats['aproveitamento_geral'] ?>%</h5>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 10px;">Média</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Informações Pessoais -->
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold mb-0">Informações da Conta</h6>
                    </div>
                    <div class="card-body">
                        <form action="../Controllers/ProcessaPerfil.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Nome Completo</label>
                                    <input type="text" class="form-control bg-light border-0" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">E-mail</label>
                                    <input type="email" class="form-control bg-light border-0" name="email" value="<?= htmlspecialchars($usuario['email']) ?>">
                                </div>
                                <!-- <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Especialidade (Opcional)</label>
                                    <input type="text" class="form-control" placeholder="Ex: Clínica Médica">
                                </div> -->
                                <!-- <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Data de Cadastro</label>
                                    <input type="text" class="form-control bg-light border-0" value="12/05/2024" readonly>
                                </div> -->
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3">Segurança</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Senha Atual</label>
                                    <input type="password" class="form-control" placeholder="••••••••">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Nova Senha</label>
                                    <input type="password" class="form-control" placeholder="Mínimo 8 caracteres">
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-5">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Configurações e Status -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-4">Plano Atual</h6>
                        <div class="d-flex align-items-center gap-3 p-3 bg-primary bg-opacity-10 rounded-3 mb-3">
                            <div class="card-icon bg-primary text-white">
                                <span class="material-icons">workspace_premium</span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Premium Anual</h6>
                                <small class="text-muted">Válido até Maio 2025</small>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm w-100">Gerenciar Assinatura</button>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Configurações</h6>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="notifEmail" checked>
                            <label class="form-check-label small" for="notifEmail">Notificações por e-mail</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="modoNoturno">
                            <label class="form-check-label small" for="modoNoturno">Modo Noturno (Beta)</label>
                        </div>
                        <hr>
                        <a href="logout.php" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                            <span class="material-icons fs-6">logout</span> Sair da Conta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

</body>
</html>
