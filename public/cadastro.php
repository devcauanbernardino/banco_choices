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
    <title>Registro | Banco de Choices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Animate.css mantido apenas para o modal de sucesso e alertas -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="../App/assets/css/cadastro.css" />
    
    <!-- Favicon Redondo -->
    <link rel="icon" type="image/x-icon" href="../App/assets/img/favicon-round.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../App/assets/img/favicon-round-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../App/assets/img/apple-touch-icon-round.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../App/assets/img/favicon-round-192x192.png">

    <style>
        :root {
            --navy-primary: #002147;
            --navy-dark: #001a38;
            --accent-purple: #6a0392;
            --accent-purple-light: #6a03928e;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background: linear-gradient(120deg, #6a0392, #6d6d6d, #460161); 
            background-size: 160% 160%;
            animation: floatBg 14s ease-in-out infinite;
            min-height: 100vh;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            background-color: white !important;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95);
        }
        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 0.25rem var(--accent-purple-light);
            transform: scale(1.01);
        }

        .materia-item {
            transition: all 0.2s ease;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background-color: #fff;
        }

        .materia-item:hover {
            background-color: rgba(106, 3, 146, 0.05);
            border-color: var(--accent-purple);
        }

        .form-check-input:checked {
            background-color: var(--accent-purple);
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 0.10rem var(--accent-purple-light);
        }

        .btn-primary-custom {
            background-color: var(--navy-primary);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-weight: 700;
        }

        .btn-primary-custom:hover {
            background-color: var(--navy-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 33, 71, 0.3);
            color: white;
        }

        .btn-primary-custom:active {
            transform: translateY(0);
        }

        .text-navy {
            color: var(--navy-primary);
        }

        .text-purple {
            color: var(--accent-purple);
        }

        .btn-loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .success-icon-anim {
            animation: heartBeat 1.5s infinite;
        }

        @keyframes floatBg {
            0% { background-position: 0% 0%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 0%; }
        }
    </style>
</head>

<body>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card">
                    <div class="card-header bg-transparent border-0 pt-5 pb-0 text-center">
                        <h3 class="fw-bold mb-2 text-navy">Creá tu cuenta</h3>
                        <p class="text-muted">Sumate a la plataforma de estudio líder</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <?php if ($errorCode && isset($messages['error'][$errorCode])): ?>
                            <div class="alert alert-danger d-flex align-items-center animate__animated animate__shakeX" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?= $messages['error'][$errorCode] ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="../App/Controllers/UsuarioController.php" method="POST" id="registrationForm">
                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="name">Nombre Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                    <input class="form-control bg-light border-start-0" id="name" name="nome" placeholder="Ej: Juan Pérez" required type="text" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="email">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input class="form-control bg-light border-start-0" id="email" name="email" placeholder="ejemplo@mail.com" required type="email" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold mb-3">
                                    Seleccioná las materias que querés comprar
                                </label>

                                <div class="row g-2">
                                    <?php foreach ($materias as $materia): ?>
                                        <div class="col-6">
                                            <div class="materia-item rounded">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" name="materias[]"
                                                        value="<?= $materia['id'] ?>" id="materia<?= $materia['id'] ?>">
                                                    <label class="form-check-label small fw-medium" for="materia<?= $materia['id'] ?>">
                                                        <?= htmlspecialchars($materia['nome']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="form-text mt-2 small">
                                    <i class="bi bi-info-circle me-1"></i> Podrás acceder solo a las materias seleccionadas.
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold" for="password">Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                        <input class="form-control bg-light border-start-0" id="password" name="senha" placeholder="••••••••" required type="password" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="confirm-password">Confirmar</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
                                        <input class="form-control bg-light border-start-0" id="confirm-password" name="confirma-senha" placeholder="••••••••" required type="password" />
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check custom-checkbox">
                                    <input class="form-check-input" id="terms" name="terms" required type="checkbox" />
                                    <label class="form-check-label small text-muted" for="terms">
                                        Acepto los <a class="text-purple text-decoration-none fw-bold border-bottom border-purple" href="#">términos y condiciones</a>.
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-success py-3 fw-bold fs-5" type="submit" id="submitBtn">
                                    <span>Registrarme</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>

                        <div class="mt-5 pt-4 border-top text-center">
                            <p class="mb-0 text-muted">
                                ¿Ya tenés uma cuenta?
                                <a class="text-purple fw-bold text-decoration-none ms-1" href="login.php">Iniciá sesión</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-white-50 small">
                        © <?= date('Y') ?> BancodeChoices. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Sucesso -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg animate__animated animate__zoomIn">
                <div class="modal-body text-center py-5 px-4">
                    <div class="mb-4">
                        <div class="success-icon-anim">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-navy mb-2">¡Bienvenido a bordo!</h3>
                    <p class="text-muted mb-4 fs-5">
                        <?= $messages['success'][$successCode] ?? 'Tu cuenta ha sido creada correctamente.' ?>
                    </p>
                    <div class="d-grid">
                        <a href="login.php" class="btn btn-success btn-lg py-3 fw-bold rounded-pill shadow-sm">
                            Ir al login <i class="bi bi-box-arrow-in-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar modal de sucesso
            <?php if (isset($_GET['success'])): ?>
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            <?php endif; ?>

            // Efeito de loading no botão
            const form = document.getElementById('registrationForm');
            const btn = document.getElementById('submitBtn');

            form.addEventListener('submit', function() {
                btn.classList.add('btn-loading');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...';
            });

            // Estilo dinâmico para itens de matéria
            const materiaChecks = document.querySelectorAll('.form-check-input');
            materiaChecks.forEach(check => {
                const parent = check.closest('.materia-item');
                
                const updateStyle = () => {
                    if (check.checked) {
                        parent.style.borderColor = 'var(--accent-purple)';
                        parent.style.backgroundColor = 'rgba(106, 3, 146, 0.05)';
                    } else {
                        parent.style.borderColor = '#dee2e6';
                        parent.style.backgroundColor = '#fff';
                    }
                };

                updateStyle();
                check.addEventListener('change', updateStyle);
            });
        });
    </script>
</body>

</html>
