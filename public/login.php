<?php
require_once __DIR__ . '/../config/public_url.php';

$mensagensErro = [
    'naologado' => 'login.err.naologado',
    'logininvalido' => 'login.err.logininvalido',
    'acessoinvalido' => 'login.err.acessoinvalido',
    'camposobrigatorios' => 'login.err.camposobrigatorios',
    'emailinvalido' => 'login.err.emailinvalido',
    'naocoincidem' => 'login.err.naocoincidem',
    'senhafraca' => 'login.err.senhafraca',
    'emailcadastrado' => 'login.err.emailcadastrado',
    'error' => 'login.err.error',
];

$erro = $_GET['error'] ?? null;
$mensagem = isset($mensagensErro[$erro]) ? __($mensagensErro[$erro]) : null;
$registroOk = isset($_GET['registered']) && $_GET['registered'] === '1';
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <?php require_once __DIR__ . '/../App/Views/includes/theme-head-public.php'; ?>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars(__('login.title_page')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/buttons-global.css')) ?>" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/public-language-selector.css')) ?>" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/theme-app.css')) ?>" />
    <link rel="stylesheet" href="assets/css/login.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <?php require_once __DIR__ . '/../config/favicon_links.php'; ?>

</head>

<body class="login-page">
    <div class="container-fluid p-0">
        <div class="row g-0 login-wrapper">
            <div class="col-lg-7 d-none d-lg-flex login-sidebar align-items-center justify-content-center p-5 text-white"
                style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuC445AKHSVeDzgOnDg89cqG-J45BnnX0jlKJqEDVoAqDa9PF3GuM8AV8eTUyanRvwnfvHSOOc9cPkyCbrND0UX4AnWDqxH2GdbLBAi9kTxBbiKYwhJwpp4McWRaQzKp14-JLsiLfjttFhj-vIaYBR95BlK0Z6arvuWAXGmsEtoBH76JvcIP81a7sjWaeBwLZayIcGfCms3TkEBhVMG3vnN2NFTTcLzxwCoLuoIZokjnUni0LZX0MQe68-QmFcZSHglB4zvHEoKo4mBK');">
                <div class="login-sidebar-overlay"></div>
                <div class="sidebar-content mw-100" style="max-width: 600px;">
                    <div class="mb-4">
                        <div class="login-sidebar-logo-wrap">
                            <img class="login-sidebar-logo" src="<?= htmlspecialchars(public_asset_url('img/logo-bd-transparente.png')) ?>"
                                alt="Banco de Choices" width="200" height="56" />
                        </div>
                    </div>
                    <h1 class="display-4 fw-bold mb-4">Tu futuro como especialista empieza acá.</h1>
                    <p class="lead mb-5 opacity-75">Unite a la comunidad más grande de residentes médicos en Argentina y
                        preparate para el éxito.</p>
                    <div class="d-flex align-items-center">
                        <div class="d-flex me-3">
                            <img alt="Médico 1" class="rounded-circle border border-2 border-white" height="40"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuBxe1tbaTNqG25ZU6ZcY-9ufHepww8gfgPl-ieHIqH07iZB3YuZK8N451KZArbv127uTUpgUurDmQVTT0_Gwsm0cHMch9Fb25sT-VdWBH-2xHBxIeaOjCk2RyqvsxG3glSqIbhTrM_LcJNEfHOpYgBIZdihw9q5sBwxdj-Eg6vTjhpwIQlzn0Ocl4yaaj8SP9ut6MpDtYu5DrJ3WNf6uCWZZRExD-kw2fr26OB5tlssXQ1bISDWOAO9bqJITIItAaZbYIfkY5CpMP0F"
                                width="40" />
                            <img alt="Médico 2" class="rounded-circle border border-2 border-white" height="40"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCgkPqA_zdy-qAo6UZSAQ8EOr3IEyzLX32nn8c0RGU9O0b75-UcMj1npe9KvD7UsZPdDzIFDsOx1_ax3LSKXc6wEyqr2lci9B02u5_ujCLITqp8MGix6Y_BfMerXX4zLuI3I1wF-OpsjorT__zm8cEoU9z9HBaRk_9aT6nwy8BqKfPZ4g94ocU_l4mqYfNjh0nKz0wislXhAd6TlPcML-Cmy18BY1y90HzMzQ1coutgMPgnLXqlBrG8OxFDIiy2ETMb-V9_3p93N8n_"
                                style="margin-left: -12px;" width="40" />
                            <img alt="Médico 3" class="rounded-circle border border-2 border-white" height="40"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuDyUl43p5wt4ZdqUPkBPODkwqpYX6zRlz8eMHICsBbbCF0q_Oi88jfQvvLCyBB7r9IZbt403sH96XQan5fCryqR1lz8a-xrRTlhnIXNUbPQGevGUpumP-JdhnaEFHGG3WeGVeZykzrGCpASks--pM8pnWTzj-CDFnK7_rBOK7zZ07LomQB2oWpOEMdLi5hBK71bNfwtlBCNJ6ycwZMhlRgYcKLXKUKtySeoB1pGJMGZOArpd6AkXgaVkJkVOjUqAI-8AhcRDs2w78Ez"
                                style="margin-left: -12px;" width="40" />
                        </div>
                        <span class="small opacity-75">+10.000 alumnos ya están practicando</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5 login-form-column">
                <div class="login-form-inner">
                    <div class="login-form-container">
                        <div class="d-lg-none login-mobile-brand text-center">
                            <a href="index.php" class="login-mobile-logo-link d-inline-block text-decoration-none" aria-label="Banco de Choices — inicio">
                                <img class="login-mobile-logo" src="<?= htmlspecialchars(public_asset_url('img/logo-bd-transparente.png')) ?>"
                                    alt="Banco de Choices" width="280" height="78" decoding="async" />
                            </a>
                        </div>

                        <header class="login-form-header">
                            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-2">
                                <a href="index.php" class="login-back-link mb-0 align-self-center">
                                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                                    <span><?= htmlspecialchars(__('login.back_home')) ?></span>
                                </a>
                                <div class="navbar-actions navbar-actions--landing login-lang-toolbar flex-shrink-0 ms-auto">
                                    <div class="navbar-actions__inner">
                                        <?php
                                        $bc_lang_menu_landing = true;
                                        $bc_lang_selector_btn_class = 'btn btn-navbar-lang dropdown-toggle d-inline-flex align-items-center gap-2';
                                        require_once __DIR__ . '/../App/Views/includes/language-selector.php';
                                        unset($bc_lang_menu_landing, $bc_lang_selector_btn_class);
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <h2 class="login-title"><?= htmlspecialchars(__('login.heading')) ?></h2>
                        </header>

                    <?php if ($registroOk): ?>
                        <div class="alert alert-success login-alert alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars(__('login.success')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= htmlspecialchars(__('login.close')) ?>"></button>
                        </div>
                    <?php elseif ($mensagem): ?>
                        <div class="alert alert-warning login-alert alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($mensagem) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?= htmlspecialchars(__('login.close')) ?>"></button>
                        </div>
                    <?php endif; ?>

                    <form action="login-process.php" method="post" id="loginForm" class="login-form">
                        <?= csrf_field() ?>
                        <div class="login-field">
                            <label class="login-field-label" for="emailInput">
                                <?= htmlspecialchars(__('login.email')) ?>
                            </label>
                            <div class="input-group input-group-lg login-input-group">
                                <span class="input-group-text" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                                <input class="form-control" id="emailInput" name="email" type="email"
                                    inputmode="email" autocomplete="email" autocapitalize="none" spellcheck="false"
                                    placeholder="nombre@ejemplo.com" required
                                    aria-required="true" />
                            </div>
                        </div>
                        <div class="login-field">
                            <label class="login-field-label" for="passwordInput">
                                <?= htmlspecialchars(__('login.password')) ?>
                            </label>
                            <div class="input-group input-group-lg login-input-group">
                                <span class="input-group-text" aria-hidden="true"><i class="bi bi-lock"></i></span>
                                <input class="form-control" id="passwordInput" name="senha" type="password"
                                    autocomplete="current-password" required
                                    placeholder="••••••••" minlength="1"
                                    aria-required="true" />
                                <button type="button" class="btn login-password-toggle" id="togglePassword"
                                    aria-label="<?= htmlspecialchars(__('login.show_pwd')) ?>" aria-controls="passwordInput" aria-pressed="false">
                                    <i class="bi bi-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center login-row-extras">
                            <div class="form-check">
                                <input class="form-check-input" id="rememberMe" type="checkbox" />
                                <label class="form-check-label small" for="rememberMe">
                                    <?= htmlspecialchars(__('login.remember')) ?>
                                </label>
                            </div>
                            <a class="text-navy text-decoration-none small fw-bold" href="mailto:contato@bancodechoices.com"><?= htmlspecialchars(__('login.forgot')) ?></a>
                        </div>
                        <div class="d-grid login-submit-wrap">
                            <button class="btn btn-primary btn-lg py-3 fw-bold shadow-sm w-100" type="submit" id="submitBtn"><?= htmlspecialchars(__('login.submit')) ?> <i
                                    class="bi bi-box-arrow-in-right ms-2" aria-hidden="true"></i></button>
                        </div>
                    </form>

                    <div class="login-signup-cta">
                        <p class="login-signup-text mb-0">
                            <?= htmlspecialchars(__('login.signup')) ?>
                            <a class="login-signup-link" href="selecionar-materias.php"><?= htmlspecialchars(__('login.signup_link')) ?></a>
                        </p>
                    </div>

                    <footer class="login-footer">
                        <nav class="login-footer-nav" aria-label="Legal">
                            <a href="index.php#privacidad"><?= htmlspecialchars(__('login.footer_privacy')) ?></a>
                            <span class="login-footer-dot" aria-hidden="true"></span>
                            <a href="index.php#terminos"><?= htmlspecialchars(__('login.footer_terms')) ?></a>
                            <span class="login-footer-dot" aria-hidden="true"></span>
                            <a href="mailto:contato@bancodechoices.com"><?= htmlspecialchars(__('login.footer_contact')) ?></a>
                        </nav>
                        <p class="login-footer-copy"><?= htmlspecialchars(__('login.footer_copy')) ?></p>
                    </footer>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/reload.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('submitBtn');
            const pwd = document.getElementById('passwordInput');
            const toggle = document.getElementById('togglePassword');
            const icon = document.getElementById('togglePasswordIcon');

            if (toggle && pwd && icon) {
                toggle.addEventListener('click', function () {
                    const show = pwd.type === 'password';
                    pwd.type = show ? 'text' : 'password';
                    icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                    toggle.setAttribute('aria-label', show ? <?= json_encode(__('login.hide_pwd')) ?> : <?= json_encode(__('login.show_pwd')) ?>);
                    toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                });
            }

            form.addEventListener('submit', function () {
                btn.classList.add('btn-loading');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + <?= json_encode(__('login.submitting'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
            });
        });
    </script>
</body>

</html>