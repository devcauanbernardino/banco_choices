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
        'senhafraca' => 'A senha deve conter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.'
    ],
    'success' => [
        'success' => 'Conta criada com sucesso!'
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="../App/assets/css/cadastro.css" />

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
            --accent-purple-lighter: #6a039220;
            --bg-light: #f8f9fa;
            --success-green: #10b981;
            --success-green-light: #10b98120;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background: linear-gradient(135deg, #6a0392 0%, #6d6d6d 50%, #460161 100%);
            background-size: 160% 160%;
            animation: floatBg 14s ease-in-out infinite;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(106, 3, 146, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(70, 1, 97, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        main {
            position: relative;
            z-index: 1;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            background-color: white !important;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.98);
            overflow: hidden;
            position: relative;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-purple), var(--accent-purple-light));
        }

        .card-header {
            background: linear-gradient(135deg, rgba(106, 3, 146, 0.05) 0%, rgba(0, 33, 71, 0.03) 100%);
        }

        .card-header h3 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--navy-primary), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-header p {
            font-size: 0.95rem;
            font-weight: 500;
            color: #6b7280;
        }

        .form-label {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--navy-primary);
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0.75rem;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 0.85rem 1.1rem;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            background-color: #f9fafb;
        }

        .input-group-text {
            border-radius: 12px;
            background-color: #f3f4f6 !important;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--accent-purple);
            background-color: #f0f0ff !important;
        }

        .input-group .form-control {
            border-radius: 12px;
        }

        .input-group .form-control.border-start-0 {
            border-left: none !important;
        }

        .input-group .form-control.border-end-0 {
            border-right: none !important;
        }

        .input-group-text.border-end-0 {
            border-right: none !important;
        }

        .input-group-text.border-start-0 {
            border-left: none !important;
        }

        .materia-item {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 12px 14px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            background-color: #fff;
            position: relative;
        }

        .materia-item:hover {
            background-color: var(--accent-purple-lighter);
            border-color: var(--accent-purple);
            transform: translateX(4px);
        }

        .form-check-input {
            width: 1.3rem;
            height: 1.3rem;
            border-radius: 6px;
            border: 2px solid #d1d5db;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--accent-purple);
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 0.15rem var(--accent-purple-lighter);
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
            font-weight: 500;
            color: #374151;
            transition: color 0.2s ease;
        }

        .materia-item:hover .form-check-label {
            color: var(--navy-primary);
        }

        .form-text {
            color: #9ca3af;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .form-text i {
            color: var(--accent-purple);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--navy-primary), var(--navy-dark));
            border: none;
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.3px;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 33, 71, 0.3);
            color: white;
        }

        .btn-primary-custom:active {
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--accent-purple), #8b2e9e) !important;
            border: none;
            padding: 14px 28px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(106, 3, 146, 0.4);
        }

        .btn-success:active {
            transform: translateY(-1px);
        }

        .btn-success i {
            transition: transform 0.3s ease;
        }

        .btn-success:hover i {
            transform: translateX(4px);
        }

        .text-navy {
            color: var(--navy-primary);
        }

        .text-purple {
            color: var(--accent-purple);
        }

        .btn-loading {
            pointer-events: none;
            opacity: 0.9;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .success-icon-anim {
            animation: successBounce 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes floatBg {
            0% {
                background-position: 0% 0%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 0%;
            }
        }

        @keyframes successBounce {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .invalid {
            color: #9ca3af;
            font-weight: 500;
        }

        .invalid::before {
            content: "○ ";
            margin-right: 4px;
        }

        .valid {
            color: var(--success-green);
            font-weight: 600;
        }

        .valid::before {
            content: "✓ ";
            margin-right: 4px;
        }

        .password-balloon {
            display: none;
            position: absolute;
            top: 110%;
            left: 0;
            width: 100%;
            background: white;
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            border: 2px solid #e5e7eb;
            backdrop-filter: blur(10px);
        }

        .password-balloon::before {
            content: "";
            position: absolute;
            top: -10px;
            left: 20px;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 10px solid white;
            filter: drop-shadow(0 -2px 4px rgba(0, 0, 0, 0.05));
        }

        #requirements li {
            margin-bottom: 6px;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }

        #requirements li.valid {
            transform: translateX(4px);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 14px 16px;
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .alert-danger i {
            color: #dc2626;
        }

        .border-top {
            border-color: #e5e7eb !important;
        }

        .text-muted {
            color: #9ca3af !important;
        }

        .custom-checkbox .form-check-input {
            border-radius: 6px;
        }

        a.text-purple {
            transition: all 0.2s ease;
            position: relative;
        }

        a.text-purple:hover {
            color: var(--navy-primary) !important;
            text-decoration: underline !important;
        }

        .copyright-text {
            font-size: 0.85rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            transition: color 0.2s ease;
        }

        .copyright-text:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-header h3 {
                font-size: 1.5rem;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            .form-label {
                font-size: 0.9rem;
            }

            .btn-success {
                padding: 12px 24px;
                font-size: 0.95rem;
            }
        }

        /* Smooth transitions for all interactive elements */
        button, input, select, textarea {
            transition: all 0.3s ease;
        }

        /* Loading spinner animation */
        .spinner-border {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card">
                    <div class="card-header bg-transparent border-0 pt-5 pb-3 text-center">
                        <h3 class="fw-bold mb-2">Creá tu cuenta</h3>
                        <p class="text-muted mb-0">Sumate a la plataforma de estudio líder</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <?php if ($errorCode && isset($messages['error'][$errorCode])): ?>
                            <div class="alert alert-danger d-flex align-items-center animate__animated animate__shakeX"
                                role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-3"></i>
                                <div><?= $messages['error'][$errorCode] ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="../App/Controllers/UsuarioController.php" method="POST" id="registrationForm">
                            <!-- Nombre Completo -->
                            <div class="mb-4">
                                <label class="form-label" for="name">Nombre Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                    <input class="form-control border-start-0" id="name" name="nome"
                                        placeholder="Ej: Juan Pérez" required type="text"
                                        value="<?= htmlspecialchars($old['nome'] ?? '') ?>" />
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-4">
                                <label class="form-label" for="email">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0">
                                        <i class="bi bi-envelope text-muted"></i>
                                    </span>
                                    <input class="form-control border-start-0" id="email" name="email"
                                        placeholder="ejemplo@mail.com" required type="email"
                                        value="<?= htmlspecialchars($old['email'] ?? '') ?>" />
                                </div>
                            </div>

                            <!-- Materias -->
                            <div class="mb-4">
                                <label class="form-label mb-3">
                                    Seleccioná las materias que querés comprar
                                </label>

                                <div class="row g-2">
                                    <?php foreach ($materias as $materia): ?>
                                        <?php
                                        $checked = (isset($old['materias']) && in_array($materia['id'], $old['materias'])) ? 'checked' : '';
                                        ?>
                                        <div class="col-md-6">
                                            <div class="materia-item">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" name="materias[]"
                                                        value="<?= $materia['id'] ?>" id="materia<?= $materia['id'] ?>"
                                                        <?= $checked ?>>
                                                    <label class="form-check-label"
                                                        for="materia<?= $materia['id'] ?>">
                                                        <?= htmlspecialchars($materia['nome']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i> Podrás acceder solo a las materias
                                    seleccionadas.
                                </div>
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-4">
                                <label class="form-label" for="password">Contraseña</label>
                                <div class="input-group position-relative">
                                    <span class="input-group-text border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input class="form-control border-start-0 border-end-0" id="password" name="senha"
                                        placeholder="••••••••" required type="password" autocomplete="off" />
                                    <span class="input-group-text border-start-0" id="togglePassword"
                                        style="cursor: pointer;">
                                        <i class="bi bi-eye text-muted"></i>
                                    </span>

                                    <div id="password-popover" class="password-balloon">
                                        <ul id="requirements" class="list-unstyled mb-0 small">
                                            <li id="length" class="invalid">Mínimo de 8 caracteres</li>
                                            <li id="uppercase" class="invalid">Letra maiúscula</li>
                                            <li id="lowercase" class="invalid">Letra minúscula</li>
                                            <li id="number" class="invalid">Número</li>
                                            <li id="special" class="invalid">Caractere especial (!@#$%^&*)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="mb-4">
                                <label class="form-label" for="confirm-password">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0">
                                        <i class="bi bi-shield-check text-muted"></i>
                                    </span>
                                    <input class="form-control border-start-0 border-end-0" id="confirm-password"
                                        name="confirma-senha" placeholder="••••••••" required type="password" />
                                    <span class="input-group-text border-start-0"
                                        id="toggleConfirmPassword" style="cursor: pointer;">
                                        <i class="bi bi-eye text-muted"></i>
                                    </span>
                                </div>
                                <div id="match-error" class="text-danger small mt-2" style="display: none;">
                                    <i class="bi bi-exclamation-circle me-1"></i>As senhas não coincidem.
                                </div>
                            </div>

                            <!-- Termos -->
                            <div class="mb-4">
                                <div class="form-check custom-checkbox">
                                    <input class="form-check-input" id="terms" name="terms" required type="checkbox" />
                                    <label class="form-check-label small text-muted" for="terms">
                                        Acepto los <a
                                            class="text-purple text-decoration-none fw-bold"
                                            href="#">términos y condiciones</a>.
                                    </label>
                                </div>
                            </div>

                            <!-- Botão Submit -->
                            <div class="d-grid">
                                <button class="btn btn-success py-3 fw-bold fs-5" type="submit" id="submitBtn">
                                    <span>Registrarme</span>
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>

                        <!-- Link para Login -->
                        <div class="mt-5 pt-4 border-top text-center">
                            <p class="mb-0 text-muted">
                                ¿Ya tenés uma cuenta?
                                <a class="text-purple fw-bold text-decoration-none ms-1" href="login.php">Iniciá
                                    sesión</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <p class="copyright-text">
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
                    <h3 class="fw-bold text-navy mb-2" style="font-family: 'Poppins', sans-serif; font-size: 1.5rem;">¡Bienvenido a bordo!</h3>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Mostrar modal de sucesso
            <?php if (isset($_GET['success'])): ?>
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            <?php endif; ?>

            // Efeito de loading no botão
            const form = document.getElementById('registrationForm');
            const btn = document.getElementById('submitBtn');

            form.addEventListener('submit', function () {
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
                        parent.style.backgroundColor = 'var(--accent-purple-lighter)';
                    } else {
                        parent.style.borderColor = '#e5e7eb';
                        parent.style.backgroundColor = '#fff';
                    }
                };

                updateStyle();
                check.addEventListener('change', updateStyle);
            });
        });

        const passwordInput = document.getElementById('password');
        const popover = document.getElementById('password-popover');

        // Mostrar balão ao focar no campo
        passwordInput.addEventListener('focus', () => {
            popover.style.display = 'block';
        });

        // Esconder balão ao clicar fora
        document.addEventListener('click', (e) => {
            if (!passwordInput.contains(e.target) && !popover.contains(e.target)) {
                popover.style.display = 'none';
            }
        });

        // Validação de requisitos de senha
        const requirements = {
            length: { regex: /.{8,}/, element: document.getElementById('length') },
            uppercase: { regex: /[A-Z]/, element: document.getElementById('uppercase') },
            lowercase: { regex: /[a-z]/, element: document.getElementById('lowercase') },
            number: { regex: /[0-9]/, element: document.getElementById('number') },
            special: { regex: /[!@#$%^&*]/, element: document.getElementById('special') }
        };

        passwordInput.addEventListener('input', () => {
            const value = passwordInput.value;
            for (const key in requirements) {
                const { regex, element } = requirements[key];
                if (regex.test(value)) {
                    element.classList.add('valid');
                    element.classList.remove('invalid');
                } else {
                    element.classList.add('invalid');
                    element.classList.remove('valid');
                }
            }
        });

        // Toggle visibilidade da senha
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });

        // Toggle visibilidade da confirmação de senha
        const toggleConfirm = document.querySelector('#toggleConfirmPassword');
        const confirmField = document.querySelector('#confirm-password');

        if (toggleConfirm) {
            toggleConfirm.addEventListener('click', function () {
                const type = confirmField.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmField.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }

        // Validação de correspondência de senhas
        const confirmPasswordField = document.getElementById('confirm-password');
        const matchError = document.getElementById('match-error');

        confirmPasswordField.addEventListener('input', () => {
            if (passwordField.value !== confirmPasswordField.value) {
                matchError.style.display = 'block';
            } else {
                matchError.style.display = 'none';
            }
        });
    </script>
</body>

</html>
