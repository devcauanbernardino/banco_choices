<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/conexao.php';

$messages = [
    'error' => [
        'acessoinvalido' => 'Acesso inválido.',
        'camposobrigatorios' => 'Preencha todos os campos.',
        'emailinvalido' => 'Informe um e-mail válido.',
        'naocoincidem' => 'As senhas não coincidem.',
        'emailcadastrado' => 'Este e-mail já está cadastrado.',
        'senhafraca' => 'A senha não atende aos requisitos de segurança.'
    ],
    'success' => [
        'success' => '¡Tu cuenta ha sido creada correctamente!'
    ]
];

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    
    <style>
        :root {
            --navy-primary: #002147;
            --navy-dark: #001a38;
            --accent-purple: #6a0392;
            --accent-purple-light: rgba(106, 3, 146, 0.1);
            --success-color: #28a745;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .card-registro {
            border: none;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: white;
        }

        .form-label {
            font-size: 0.9rem;
            color: var(--navy-primary);
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-color: #e9ecef;
            color: #adb5bd;
        }

        .form-control {
            border-color: #e9ecef;
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 4px var(--accent-purple-light);
        }

        /* Grid de Materias */
        .materia-card {
            cursor: pointer;
            border: 2px solid #f1f3f5;
            border-radius: 12px;
            padding: 12px;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            align-items: center;
            background: #fff;
        }

        .materia-card:hover {
            border-color: var(--accent-purple-light);
            transform: translateY(-2px);
        }

        .materia-card.selected {
            border-color: var(--accent-purple);
            background-color: var(--accent-purple-light);
        }

        /* Password Balloon Melhorado */
        .password-balloon {
            display: none;
            position: absolute;
            z-index: 100;
            top: 105%;
            left: 0;
            right: 0;
            background: #fff;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid #eee;
        }

        .requirement {
            font-size: 0.8rem;
            margin-bottom: 4px;
            color: #adb5bd;
            display: flex;
            align-items: center;
        }

        .requirement.valid {
            color: var(--success-color);
            font-weight: 600;
        }

        .requirement i { margin-right: 8px; }

        .btn-registrar {
            background: var(--navy-primary);
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .btn-registrar:hover {
            background: var(--navy-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 33, 71, 0.2);
        }
    </style>
</head>
<body>
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card card-registro">
                    <div class="row g-0">
                        <div class="col-md-5 d-none d-md-flex bg-primary align-items-center justify-content-center p-5 text-white text-center" 
                             style="background: linear-gradient(45deg, var(--navy-primary), var(--accent-purple)) !important;">
                            <div>
                                <h2 class="fw-bold mb-4">Bienvenido a la comunidad</h2>
                                <p class="opacity-75">Estudiá de forma inteligente con nuestra base de datos especializada.</p>
                                <img src="https://cdn-icons-png.flaticon.com/512/3406/3406828.png" class="img-fluid mt-4 opacity-50" style="max-width: 150px;">
                            </div>
                        </div>

                        <div class="col-md-7 p-4 p-lg-5">
                            <div class="mb-4">
                                <h3 class="fw-bold text-navy">Creá tu cuenta</h3>
                                <p class="text-muted small">Completá los datos para comenzar.</p>
                            </div>

                            <?php if ($errorCode && isset($messages['error'][$errorCode])): ?>
                                <div class="alert alert-danger border-0 small animate__animated animate__headShake">
                                    <i class="bi bi-exclamation-circle me-2"></i><?= $messages['error'][$errorCode] ?>
                                </div>
                            <?php endif; ?>

                            <form action="../App/Controllers/UsuarioController.php" method="POST" id="registrationForm">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Nombre Completo</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="nome" class="form-control" placeholder="Ej: Juan Pérez" required value="<?= htmlspecialchars($old['nome'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Correo Electrónico</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" name="email" class="form-control" placeholder="nombre@mail.com" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="col-12 mb-4">
                                        <label class="form-label d-block mb-3">Materias de interés</label>
                                        <div class="row g-2">
                                            <?php foreach ($materias as $materia): 
                                                $checked = (isset($old['materias']) && in_array($materia['id'], $old['materias'])) ? 'checked' : '';
                                            ?>
                                            <div class="col-6">
                                                <label class="materia-card <?= $checked ? 'selected' : '' ?>" for="materia<?= $materia['id'] ?>">
                                                    <input class="form-check-input d-none" type="checkbox" name="materias[]" 
                                                           value="<?= $materia['id'] ?>" id="materia<?= $materia['id'] ?>" <?= $checked ?>>
                                                    <span class="small fw-medium text-truncate"><?= htmlspecialchars($materia['nome']) ?></span>
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contraseña</label>
                                        <div class="input-group position-relative">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" id="password" name="senha" class="form-control" placeholder="••••••••" required>
                                            <span class="input-group-text" id="togglePassword" style="cursor: pointer;"><i class="bi bi-eye"></i></span>
                                            
                                            <div id="password-popover" class="password-balloon">
                                                <div class="requirement" id="length"><i class="bi bi-circle"></i> 8+ caracteres</div>
                                                <div class="requirement" id="uppercase"><i class="bi bi-circle"></i> Mayúscula</div>
                                                <div class="requirement" id="number"><i class="bi bi-circle"></i> Número</div>
                                                <div class="requirement" id="special"><i class="bi bi-circle"></i> Símbolo (!@#)</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label">Confirmar</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                            <input type="password" id="confirm-password" name="confirma-senha" class="form-control" placeholder="••••••••" required>
                                            <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;"><i class="bi bi-eye"></i></span>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="terms" required>
                                            <label class="form-check-label small text-muted" for="terms">
                                                Acepto los <a href="#" class="text-purple">términos y condiciones</a>.
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12 d-grid">
                                        <button type="submit" id="submitBtn" class="btn btn-primary btn-registrar">
                                            Registrarme <i class="bi bi-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="mt-4 text-center">
                                <p class="small text-muted">¿Ya tenés cuenta? <a href="login.php" class="text-purple fw-bold text-decoration-none">Iniciá sesión</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4 border-0">
                <div class="modal-body">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold mt-3">¡Cuenta Creada!</h4>
                    <p class="text-muted">Ya podés empezar a estudiar con nosotros.</p>
                    <a href="login.php" class="btn btn-success w-100 py-3 rounded-pill fw-bold mt-3">Ir al Login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sucesso
            <?php if (isset($_GET['success'])): ?>
                new bootstrap.Modal(document.getElementById('successModal')).show();
            <?php endif; ?>

            // Interação das Matérias
            document.querySelectorAll('.materia-card').forEach(card => {
                const input = card.querySelector('input');
                input.addEventListener('change', () => {
                    card.classList.toggle('selected', input.checked);
                });
            });

            // Toggle Password
            const setupToggle = (btnId, inputId) => {
                document.getElementById(btnId).addEventListener('click', function() {
                    const input = document.getElementById(inputId);
                    const icon = this.querySelector('i');
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                });
            };
            setupToggle('togglePassword', 'password');
            setupToggle('toggleConfirmPassword', 'confirm-password');

            // Password Requirements
            const passInput = document.getElementById('password');
            const balloon = document.getElementById('password-popover');
            
            passInput.addEventListener('focus', () => balloon.style.display = 'block');
            document.addEventListener('click', (e) => {
                if (!passInput.contains(e.target) && !balloon.contains(e.target)) balloon.style.display = 'none';
            });

            passInput.addEventListener('input', function() {
                const val = this.value;
                const reqs = {
                    length: val.length >= 8,
                    uppercase: /[A-Z]/.test(val),
                    number: /[0-9]/.test(val),
                    special: /[!@#$%^&*]/.test(val)
                };

                for (const k in reqs) {
                    const el = document.getElementById(k);
                    const icon = el.querySelector('i');
                    if (reqs[k]) {
                        el.classList.add('valid');
                        icon.className = 'bi bi-check-circle-fill';
                    } else {
                        el.classList.remove('valid');
                        icon.className = 'bi bi-circle';
                    }
                }
            });
        });
    </script>
</body>
</html>