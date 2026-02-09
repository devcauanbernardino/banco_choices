<?php
$mensagensErro = [
    'naologado' => 'Você precisa estar logado para acessar o sistema.',
    'logininvalido' => 'E-mail ou senha incorretos.',
    'acessoinvalido' => 'Acesso inválido.',
];

$erro = $_GET['error'] ?? null;
$mensagem = $mensagensErro[$erro] ?? null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Banco de Choices | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <style>
        :root {
            --bs-primary: #6000df;
            --bs-primary-rgb: 15, 73, 189;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }

        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .btn-primary:hover {
            background-color: #6000df;
            border-color: #0d3da1;
        }

        .login-wrapper {
            min-height: 100vh;
        }

        .login-sidebar {
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .login-sidebar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(15, 73, 189, 0.85) 0%, rgba(10, 31, 75, 0.95) 100%);
        }

        .sidebar-content {
            position: relative;
            z-index: 2;
        }

        .form-control:focus {
            border-color: rgba(15, 73, 189, 0.5);
            box-shadow: 0 0 0 0.25rem rgba(15, 73, 189, 0.25);
        }

        .social-btn {
            border: 1px solid #dee2e6;
            transition: background-color 0.2s;
        }

        .social-btn:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0 login-wrapper">
            <div class="col-lg-7 d-none d-lg-flex login-sidebar align-items-center justify-content-center p-5 text-white"
                style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuC445AKHSVeDzgOnDg89cqG-J45BnnX0jlKJqEDVoAqDa9PF3GuM8AV8eTUyanRvwnfvHSOOc9cPkyCbrND0UX4AnWDqxH2GdbLBAi9kTxBbiKYwhJwpp4McWRaQzKp14-JLsiLfjttFhj-vIaYBR95BlK0Z6arvuWAXGmsEtoBH76JvcIP81a7sjWaeBwLZayIcGfCms3TkEBhVMG3vnN2NFTTcLzxwCoLuoIZokjnUni0LZX0MQe68-QmFcZSHglB4zvHEoKo4mBK');">
                <div class="login-sidebar-overlay"></div>
                <div class="sidebar-content mw-100" style="max-width: 600px;">
                    <div class="mb-4">
                        <svg fill="none" height="48" viewBox="0 0 48 48" width="48" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M24 45.8096C19.6865 45.8096 15.4698 44.5305 11.8832 42.134C8.29667 39.7376 5.50128 36.3314 3.85056 32.3462C2.19985 28.361 1.76794 23.9758 2.60947 19.7452C3.451 15.5145 5.52816 11.6284 8.57829 8.5783C11.6284 5.52817 15.5145 3.45101 19.7452 2.60948C23.9758 1.76795 28.361 2.19986 32.3462 3.85057C36.3314 5.50129 39.7376 8.29668 42.134 11.8833C44.5305 15.4698 45.8096 19.6865 45.8096 24L24 24L24 45.8096Z"
                                fill="white"></path>
                        </svg>
                    </div>
                    <h1 class="display-4 fw-bold mb-4">Tu futuro como especialista empieza acá.</h1>
                    <p class="lead mb-5 opacity-75">Unite a la comunidad más grande de residentes médicos en Argentina y
                        preparate para el éxito.</p>
                    <div class="d-flex align-items-center">
                        <div class="d-flex me-3">
                            <img alt="Médico 1" class="rounded-circle border border-2 border-primary" height="40"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuBxe1tbaTNqG25ZU6ZcY-9ufHepww8gfgPl-ieHIqH07iZB3YuZK8N451KZArbv127uTUpgUurDmQVTT0_Gwsm0cHMch9Fb25sT-VdWBH-2xHBxIeaOjCk2RyqvsxG3glSqIbhTrM_LcJNEfHOpYgBIZdihw9q5sBwxdj-Eg6vTjhpwIQlzn0Ocl4yaaj8SP9ut6MpDtYu5DrJ3WNf6uCWZZRExD-kw2fr26OB5tlssXQ1bISDWOAO9bqJITIItAaZbYIfkY5CpMP0F"
                                width="40" />
                            <img alt="Médico 2" class="rounded-circle border border-2 border-primary" height="40"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCgkPqA_zdy-qAo6UZSAQ8EOr3IEyzLX32nn8c0RGU9O0b75-UcMj1npe9KvD7UsZPdDzIFDsOx1_ax3LSKXc6wEyqr2lci9B02u5_ujCLITqp8MGix6Y_BfMerXX4zLuI3I1wF-OpsjorT__zm8cEoU9z9HBaRk_9aT6nwy8BqKfPZ4g94ocU_l4mqYfNjh0nKz0wislXhAd6TlPcML-Cmy18BY1y90HzMzQ1coutgMPgnLXqlBrG8OxFDIiy2ETMb-V9_3p93N8n_"
                                style="margin-left: -12px;" width="40" />
                            <img alt="Médico 3" class="rounded-circle border border-2 border-primary" height="40"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDyUl43p5wt4ZdqUPkBPODkwqpYX6zRlz8eMHICsBbbCF0q_Oi88jfQvvLCyBB7r9IZbt403sH96XQan5fCryqR1lz8a-xrRTlhnIXNUbPQGevGUpumP-JdhnaEFHGG3WeGVeZykzrGCpASks--pM8pnWTzj-CDFnK7_rBOK7zZ07LomQB2oWpOEMdLi5hBK71bNfwtlBCNJ6ycwZMhlRgYcKLXKUKtySeoB1pGJMGZOArpd6AkXgaVkJkVOjUqAI-8AhcRDs2w78Ez"
                                style="margin-left: -12px;" width="40" />
                        </div>
                        <span class="small opacity-75">+10.000 alumnos ya están practicando</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5 d-flex align-items-center justify-content-center bg-white">
                <div class="p-4 p-md-5 w-100" style="max-width: 480px;">
                    <div class="d-lg-none mb-5 text-center">
                        <div class="d-inline-flex align-items-center gap-2">
                            <svg fill="none" height="32" viewBox="0 0 48 48" width="32"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M24 45.8096C19.6865 45.8096 15.4698 44.5305 11.8832 42.134C8.29667 39.7376 5.50128 36.3314 3.85056 32.3462C2.19985 28.361 1.76794 23.9758 2.60947 19.7452C3.451 15.5145 5.52816 11.6284 8.57829 8.5783C11.6284 5.52817 15.5145 3.45101 19.7452 2.60948C23.9758 1.76795 28.361 2.19986 32.3462 3.85057C36.3314 5.50129 39.7376 8.29668 42.134 11.8833C44.5305 15.4698 45.8096 19.6865 45.8096 24L24 24L24 45.8096Z"
                                    fill="#6000df"></path>
                            </svg>
                            <span class="h4 mb-0 fw-bold text-dark">BancodeChoices</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <h2 class="fw-bold mb-1">Iniciá sesión</h2>
                        <p class="text-muted small">Ingresá a tu cuenta de BancodeChoices</p>
                    </div>
                    <?php if ($mensagem): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($mensagem) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="/banco_choices/App/Controllers/LoginController.php" method="post">
                        <div class="mb-3">
                            <label class="form-label fw-medium small" for="emailInput">Email</label>
                            <input class="form-control form-control-lg" id="emailInput" placeholder="ejemplo@mail.com"
                                required type="email" name="email" />
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <label class="form-label fw-medium small" for="passwordInput">Contraseña</label>
                            </div>
                            <input class="form-control form-control-lg" id="passwordInput" placeholder="••••••••"
                                type="password" name="senha" />
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" id="rememberMe" type="checkbox" />
                                <label class="form-check-label small" for="rememberMe">
                                    Recordarme
                                </label>
                            </div>
                            <a class="text-decoration-none small fw-bold" href="#">¿Olvidaste tu contraseña?</a>
                        </div>
                        <div class="d-grid mb-4">
                            <button class="btn btn-primary btn-lg py-3 fw-bold shadow-sm"
                                type="submit">Ingresar</button>
                        </div>
                        <div class="position-relative text-center mb-4">
                            <hr class="text-muted" />
                            <span
                                class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">O
                                ingresá con</span>
                        </div>
                        <div class="row g-2 mb-5">
                            <div class="col-6">
                                <button
                                    class="btn social-btn w-100 py-2 d-flex align-items-center justify-content-center gap-2"
                                    type="button">
                                    <img alt="Google" height="18"
                                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuDPMHIdOzvR61h0bSe-JxJ3JZAWS8vTn5eonQCUd1MLs8H5hOf1LvZK8B_DBx2-qmRybb-ytnVwmsybYDPJYHels3CVkbwppYgO19IKPJh4IXS3x8RsFncC-J2egzvxX-AhcczRUll4QWIHb-Vx6mPjXNSJf1zUqpbgIdsLoHM7X9mTMuNe5ezpi3hAboHyP2FcAGLftZdTq3vaz8HN-DrLX7HrPpFMj7pOhPyvmosg0WlLSqBIFFSiD3xeAsl5B2_PQGkyq_UZT4aV"
                                        width="18" />
                                    <span class="small fw-medium">Google</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button
                                    class="btn social-btn w-100 py-2 d-flex align-items-center justify-content-center gap-2"
                                    type="button">
                                    <img alt="Apple" height="18"
                                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuC7XdL2mmgQEvCi5loKhNCjfKKr9RYP_3cx8VkXbzSrcVSjuGovtj9TdubAi_mXsh8xGiLixG0VE1K6WKxSGL1lIz1aSRfEKNkRMy2ITl9OdvL0fGlzx5JHGDs3k5Yfix5UH-BjJj-DvNNvk-33shONajvXlTpSZ95jtAOoU63QwIvLF9p9MuVf_eMyzKTs07d_teW8NKvrSc2S8gLf6RANuzxeQ2TLWm87Gkm99fzDJiejHyoIYz35HRFlXp28xf2HyHxwGnciJlCB"
                                        width="18" />
                                    <span class="small fw-medium">Apple</span>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="text-center mb-5">
                        <p class="text-muted small">¿No tenés una cuenta? <a
                                class="text-primary fw-bold text-decoration-none" href="cadastro.php">Registrate
                                gratis</a></p>
                    </div>
                    <footer class="mt-auto border-top pt-4 text-center">
                        <div class="mb-2">
                            <a class="text-muted text-decoration-none small mx-2" href="#">Privacidad</a>
                            <a class="text-muted text-decoration-none small mx-2" href="#">Términos</a>
                            <a class="text-muted text-decoration-none small mx-2" href="#">Contacto</a>
                        </div>
                        <p class="text-muted" style="font-size: 10px; letter-spacing: 1px;">© 2026 BANCODECHOICES
                            ARGENTINA</p>
                    </footer>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if (window.location.search.includes('error=')) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>


</body>

</html>