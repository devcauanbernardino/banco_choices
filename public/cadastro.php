<?php
require_once __DIR__ . '/../config/conexao.php';

$messages = [
    'error' => [
        'acessoinvalido' => 'Acesso inválido.',
        'camposobrigatorios' => 'Preencha todos os campos.',
        'emailinvalido' => 'Informe um e-mail válido.',
        'naocoincidem' => 'As senhas não coincidem.',
        'emailcadastrado' => 'Este e-mail já está cadastrado.'
    ],
    'success' => [
        'success' => 'Conta criada com sucesso!'
    ]
];

$errorCode = $_GET['error'] ?? null;
$successCode = $_GET['success'] ?? null;

$conexao = new Conexao();
$pdo = $conexao->conectar();

$materias = $pdo->query("SELECT id, nome FROM materias")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Banco de Choices | Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../App/assets/css/cadastro.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            border: 1px solid #dee2e6;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25 darkblue;
        }

        .btn-primary-custom {
            background-color: #002147;
            color: white;
            transition: all 0.3s;
        }

        .btn-primary-custom:hover {
            background-color: #001a38;
            transform: translateY(-1px);
            color: white;
        }

        .text-navy {
            color: #002147;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="../App/assets/img/logo-bd-transparente.png" alt="logo" style="width: 50px; height: 50px;" />
                <span class="ms-2 fw-bold text-navy">BancodeChoices</span>
            </a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 text-center">
                        <h4 class="fw-bold mb-1">Creá tu cuenta</h4>
                        <p class="text-muted small">Sumate a la plataforma de estudio líder</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($errorCode && isset($messages['error'][$errorCode])): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?= $messages['error'][$errorCode] ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="../App/Controllers/UsuarioController.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-medium" for="name">Nombre Completo</label>
                                <input class="form-control" id="name" name="nome" placeholder="Ej: Juan Pérez" required
                                    type="text" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium" for="email">Correo Electrónico</label>
                                <input class="form-control" id="email" name="email" placeholder="ejemplo@mail.com"
                                    required type="email" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium">
                                    Seleccioná las materias que querés comprar
                                </label>

                                <div class="row">
                                    <?php foreach ($materias as $materia): ?>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="materias[]"
                                                    value="<?= $materia['id'] ?>" id="materia<?= $materia['id'] ?>">
                                                <label class="form-check-label" for="materia<?= $materia['id'] ?>">
                                                    <?= htmlspecialchars($materia['nome']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="form-text small">
                                    Podrás acceder solo a las materias seleccionadas.
                                </div>
                            </div>



                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium" for="password">Contraseña</label>
                                    <input class="form-control" id="password" name="senha" placeholder="••••••••"
                                        required type="password" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium" for="confirm-password">Confirmar</label>
                                    <input class="form-control" id="confirm-password" name="confirma-senha"
                                        placeholder="••••••••" required type="password" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" id="terms" name="terms" required type="checkbox" />
                                    <label class="form-check-label small text-muted" for="terms">
                                        Acepto los <a class="text-navy text-decoration-none fw-bold" href="#">términos y
                                            condiciones</a>.
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-primary-custom py-2 fw-bold" type="submit">
                                    Registrarme <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </form>

                        <div class="mt-4 pt-3 border-top text-center">
                            <p class="mb-0 small">
                                ¿Ya tenés una cuenta?
                                <a class="text-navy fw-bold text-decoration-none" href="login.php">Iniciá sesión</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-muted small">
                        © 2026 BancodeChoices. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold">¡Bienvenido!</h4>
                    <p class="text-muted mb-4">
                        <?= $messages['success'][$successCode] ?? 'Tu cuenta ha sido creada correctamente.' ?>
                    </p>
                    <a href="login.php" class="btn btn-success px-5 py-2 fw-bold">
                        Ir al login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (isset($_GET['success'])): ?>
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        <?php endif; ?>
    </script>
</body>

</html>