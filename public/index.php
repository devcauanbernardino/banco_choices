<?php
require_once __DIR__ . '/../config/public_url.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <?php require_once __DIR__ . '/../App/Views/includes/theme-head-public.php'; ?>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars(__('index.page_title')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/buttons-global.css')) ?>" />
    <link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/public-language-selector.css')) ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <?php require_once __DIR__ . '/../config/favicon_links.php'; ?>
    <style>
        :root {
            --bs-primary: #6a0392;
            --bs-primary-rgb: 106, 3, 146;
            --primary-dark: #4a0072;
            --bg-gradient: linear-gradient(-45deg, #6a0392, #4a0072, #2c003e, #1a0026);
            --bs-font-sans-serif: "Inter", system-ui, -apple-system, sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background-color: #f8fafc;
            overflow-x: hidden;
        }

        .btn-back-to-top {
            position: fixed;
            bottom: 1.25rem;
            right: 1.25rem;
            z-index: 1040;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            box-shadow: 0 4px 16px rgba(106, 3, 146, 0.32);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition:
                opacity 0.25s ease,
                visibility 0.25s ease,
                transform 0.25s ease,
                box-shadow 0.2s ease,
                filter 0.2s ease;
        }

        .btn-back-to-top.is-visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        /* Landing: logo + toggler + CTAs em viewports estreitas */
        .landing-navbar > .container {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            row-gap: 0.5rem;
        }

        .landing-navbar .navbar-brand {
            flex: 1 1 auto;
            min-width: 0;
            max-width: calc(100% - 3.75rem);
            margin-right: 0;
        }

        .landing-navbar .navbar-brand img {
            width: auto;
            max-width: min(200px, 100%);
            max-height: 48px;
            height: auto;
            object-fit: contain;
        }

        @media (min-width: 992px) {
            .landing-navbar.navbar-expand-lg .navbar-toggler {
                display: none !important;
            }

            .landing-navbar .navbar-brand {
                max-width: none;
            }

            .landing-navbar .navbar-brand img {
                max-height: 56px;
            }
        }

        @media (max-width: 991.98px) {
            .landing-navbar .navbar-toggler.landing-navbar-toggler {
                flex-shrink: 0;
                margin-left: auto;
                padding: 0;
                width: 3rem;
                height: 3rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(106, 3, 146, 0.14);
                border-radius: 14px;
                background: linear-gradient(165deg, rgba(106, 3, 146, 0.09) 0%, rgba(106, 3, 146, 0.03) 100%);
                box-shadow:
                    0 1px 2px rgba(15, 23, 42, 0.05),
                    inset 0 1px 0 rgba(255, 255, 255, 0.85);
                color: var(--bs-primary);
                transition:
                    background 0.2s ease,
                    box-shadow 0.2s ease,
                    transform 0.18s ease,
                    border-color 0.2s ease;
            }

            .landing-navbar .navbar-toggler.landing-navbar-toggler:hover {
                background: linear-gradient(165deg, rgba(106, 3, 146, 0.14) 0%, rgba(106, 3, 146, 0.06) 100%);
                border-color: rgba(106, 3, 146, 0.22);
                box-shadow: 0 6px 18px rgba(106, 3, 146, 0.14);
                transform: translateY(-1px);
            }

            .landing-navbar .navbar-toggler.landing-navbar-toggler:focus-visible {
                outline: 2px solid rgba(106, 3, 146, 0.45);
                outline-offset: 3px;
            }

            .landing-navbar .navbar-toggler.landing-navbar-toggler[aria-expanded="true"] {
                background: rgba(106, 3, 146, 0.12);
                border-color: rgba(106, 3, 146, 0.28);
                box-shadow: inset 0 1px 3px rgba(106, 3, 146, 0.12);
            }

            .landing-navbar-toggler-svg {
                display: block;
                width: 1.75rem;
                height: 1.75rem;
                flex-shrink: 0;
            }

            .landing-navbar .navbar-collapse {
                flex-basis: 100%;
                width: 100%;
                border-top: 1px solid rgba(15, 23, 42, 0.08);
                margin-top: 0.25rem;
                padding-top: 0.75rem;
            }

            .landing-navbar .navbar-nav {
                width: 100%;
                margin-bottom: 0.5rem !important;
            }

            .landing-navbar .navbar-nav .nav-link {
                padding-left: 0;
                padding-right: 0;
            }

            .landing-navbar .navbar-actions--landing {
                width: 100%;
                justify-content: stretch;
            }

            .landing-navbar .navbar-actions__inner {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .landing-navbar .navbar-actions__divider {
                display: none !important;
            }

            .landing-navbar .bc-lang-selector {
                width: 100%;
            }

            .landing-navbar .btn-navbar-lang,
            .landing-navbar .btn-nav-login,
            .landing-navbar .navbar-cta-register {
                width: 100%;
                justify-content: center;
            }

            .landing-navbar .dropdown-menu {
                width: 100%;
                min-width: 0;
            }
        }

        .nav-link:hover {
            color: var(--bs-primary) !important;
        }

        .hero-section {
            padding: 120px 0 80px;
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-image-container {
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2);
            border: 8px solid white;
            transition: transform 0.5s ease;
        }

        .hero-image-container:hover {
            transform: translateY(-10px);
        }

        /* Cards de Características */
        .card-custom {
            border: none;
            border-radius: 20px;
            padding: 2.5rem;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .card-custom:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(106, 3, 146, 0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: rgba(106, 3, 146, 0.1);
            color: var(--bs-primary);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        /* Subject Cards */
        .subject-card {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            border-radius: 24px;
            border: 2px solid #f0f0f0;
            padding: 2.5rem 2rem;
            transition: all 0.3s ease;
            background: white;
            height: 100%;
        }

        .subject-card:hover {
            border-color: var(--bs-primary);
            background: rgba(106, 3, 146, 0.02);
            transform: scale(1.02);
        }

        /* Não aplicar a .btn-primary: senão o texto fica roxo sobre fundo roxo (Bootstrap + buttons-global). */
        .btn:not(.btn-primary) {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .btn:not(.btn-primary):hover {
            background-color: var(--bs-primary);
            color: white;
        }

        .btn-reverse {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .btn-reverse:hover {
            background-color: white;
            border-color: var(--bs-primary);
            color: var(--bs-primary);
        }

        .btn-outline {
            background-color: var(--bs-primary);
            border-color: white;
            color: white;
        }

        .btn-outline:hover {
            background-color: white;
            border-color: var(--bs-primary);
            color: var(--bs-primary);
        }

        /*
         * Voltar ao topo: .btn global aplica padding grande — sem !important o ícone fica descentrado.
         */
        .btn.btn-primary.btn-back-to-top {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            margin: 0;
            line-height: 0;
            min-width: 3rem;
            min-height: 3rem;
            color: #fff;
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .btn.btn-primary.btn-back-to-top svg {
            display: block;
            width: 1.35rem;
            height: 1.35rem;
            margin: 0;
            flex-shrink: 0;
            stroke: #fff;
        }

        .btn.btn-primary.btn-back-to-top:hover,
        .btn.btn-primary.btn-back-to-top:focus-visible {
            color: #fff !important;
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
            filter: brightness(1.1);
            box-shadow: 0 8px 28px rgba(106, 3, 146, 0.45);
            transform: translateY(-2px);
        }

        .btn.btn-primary.btn-back-to-top:hover svg,
        .btn.btn-primary.btn-back-to-top:focus-visible svg {
            stroke: #fff;
        }

        .btn.btn-primary.btn-back-to-top:active {
            filter: brightness(0.95);
            transform: translateY(0) !important;
            box-shadow: 0 3px 12px rgba(106, 3, 146, 0.35);
        }

        .hero-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        }

        /* Cards */
        .card-custom {
            border: none;
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .card-custom:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(106, 3, 146, 0.1);
        }

        .badge-soft {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            padding: 0.5em 1em;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            font-size: 0.75rem;
        }

        .pricing-card.featured {
            border: 2px solid var(--bs-primary);
            transform: scale(1.05);
            z-index: 10;
        }

        .hero-image-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 8px solid white;
        }

        .material-symbols-outlined {
            vertical-align: middle;
            font-size: 2.5rem;
        }

        .site-footer {
            background: #f8fafc;
            color: #334155;
            border-top: 1px solid #e2e8f0;
        }

        .footer-min-logo {
            display: inline-block;
            max-height: 44px;
            width: auto;
            object-fit: contain;
        }

        .footer-min-tagline {
            font-size: 0.9375rem;
            line-height: 1.65;
            color: #64748b;
            max-width: 26rem;
        }

        .footer-min-heading {
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 0.75rem;
        }

        .footer-min-link {
            display: inline-block;
            padding: 0.2rem 0;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .footer-min-link:hover {
            color: var(--bs-primary);
        }

        .footer-min-cta {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.35rem 1.5rem;
        }

        .footer-min-cta-title {
            font-size: 1.0625rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .footer-min-cta-sub {
            font-size: 0.875rem;
            color: #64748b;
            line-height: 1.55;
        }

        .footer-min-mail {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--bs-primary);
            text-decoration: none;
            white-space: nowrap;
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 0.5rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .footer-min-mail:hover {
            border-color: rgba(106, 3, 146, 0.25);
            box-shadow: 0 1px 8px rgba(106, 3, 146, 0.08);
            text-decoration: none;
        }

        .footer-min-rule {
            border-color: #e2e8f0 !important;
        }

        .footer-min-legal-box {
            font-size: 0.75rem;
            line-height: 1.6;
            color: #94a3b8;
        }

        .footer-min-legal-box p {
            margin-bottom: 0.65rem;
        }

        .footer-min-legal-box p:last-child {
            margin-bottom: 0;
        }

        .footer-min-legal-box strong {
            color: #64748b;
            font-weight: 600;
        }

        .footer-min-bottom {
            font-size: 0.8125rem;
            color: #94a3b8;
        }

        .footer-min-site {
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.8125rem;
        }

        .footer-min-site:hover {
            color: var(--bs-primary);
        }

        .container {
            max-width: 1320px;
        }
    </style>
</head>

<body id="top">
    <nav class="navbar navbar-expand-lg navbar-light sticky-top py-2 py-lg-3 landing-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold text-primary" href="#top" aria-label="<?= htmlspecialchars(__('index.back_to_top')) ?>">
                <img src="<?= htmlspecialchars(public_asset_url('img/logo-bd-transparente.png')) ?>" alt="Banco de Choices" width="200" height="56" decoding="async" />
            </a>
            <button class="navbar-toggler landing-navbar-toggler" type="button" data-bs-target="#navbarNav" data-bs-toggle="collapse" aria-controls="navbarNav" aria-expanded="false" aria-label="<?= htmlspecialchars(__('index.nav.menu_aria')) ?>">
                <svg class="landing-navbar-toggler-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 8h14M5 12h14M5 16h14" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" />
                </svg>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-lg-4">
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3" href="#caracteristicas"><?= htmlspecialchars(__('index.nav.features')) ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3" href="#materias"><?= htmlspecialchars(__('index.nav.subjects')) ?></a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link fw-medium px-3" href="#planes">Planes</a>
                    </li> -->
                </ul>
                <div class="navbar-actions navbar-actions--landing">
                    <div class="navbar-actions__inner">
                        <?php
                        $bc_lang_menu_landing = true;
                        $bc_lang_selector_btn_class = 'btn btn-navbar-lang dropdown-toggle d-inline-flex align-items-center gap-2';
                        require_once __DIR__ . '/../App/Views/includes/language-selector.php';
                        ?>
                        <span class="navbar-actions__divider" aria-hidden="true"></span>
                        <a class="btn btn-nav-login text-decoration-none" href="login.php"><?= htmlspecialchars(__('index.nav.login')) ?></a>
                        <a class="btn btn-reverse navbar-cta-register text-decoration-none shadow-sm" href="selecionar-materias.php"><?= htmlspecialchars(__('index.nav.register')) ?></a>
                    </div>
                </div>
                <?php unset($bc_lang_selector_btn_class, $bc_lang_menu_landing); ?>
            </div>
        </div>
    </nav>
    <header class="hero-section">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <span class="badge rounded-pill badge-soft mb-3"><?= htmlspecialchars(__('index.hero.badge')) ?></span>
                    <h1 class="display-3 fw-bold mb-4 text-dark"><?= htmlspecialchars(__('index.hero.title')) ?>
                    </h1>
                    <p class="lead mb-5 ">
                        <?= htmlspecialchars(__('index.hero.lead')) ?>
                    </p>
                    <div class="d-flex flex-column flex-md-row gap-3">
                        <a class="btn btn-reverse btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm" href="selecionar-materias.php"><?= htmlspecialchars(__('index.hero.cta1')) ?></a>
                        <a class="btn btn-outline-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm" href="#caracteristicas"><?= htmlspecialchars(__('index.hero.cta2')) ?></a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-container">
                        <img alt="<?= htmlspecialchars(__('index.hero.alt')) ?>" class="img-fluid w-100"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAJrVuD5WbRNO1zzzt4UoFwBkgCqUzoRtNiqP7qKv66p8z6aWUXsnAbInu5f7fNYmO-OVq7q9Iz1QNQODMSbVpgi_Fuoek1UW2ktjoYWTuZNjr_OWwAa_bvgoh6UTzJHOWMjIha4zbIIhYsOmTvUdQKfa0QF5fF97ZeOo_w79ao6ZmrprXqPZKiocyGjSrMDBz577aDiuKT7rR9BM33rIXv_8DyNPxSBVSLIcjQVMpmA77tpGYy8m8J1saNHHTxjNCnRay_2tkPmaN5" />
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Características -->
    <section class="py-5 bg-light" id="caracteristicas">
        <div class="container py-5">
            <div class="text-center mb-5 animate__animated animate__fadeInUp">
                <h2 class="display-5 fw-bold mb-3"><?= htmlspecialchars(__('index.features.title')) ?></h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">
                    <?= htmlspecialchars(__('index.features.lead')) ?>
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                    <div class="card-custom h-100">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined fs-2">library_books</span>
                        </div>
                        <h4 class="fw-bold mb-3"><?= htmlspecialchars(__('index.features.c1t')) ?></h4>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars(__('index.features.c1p')) ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card-custom h-100">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined fs-2">leaderboard</span>
                        </div>
                        <h4 class="fw-bold mb-3"><?= htmlspecialchars(__('index.features.c2t')) ?></h4>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars(__('index.features.c2p')) ?>
                        </p>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                    <div class="card-custom h-100">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined fs-2">assignment_turned_in</span>
                        </div>
                        <h4 class="fw-bold mb-3"><?= htmlspecialchars(__('index.features.c3t')) ?></h4>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars(__('index.features.c3p')) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Materias Disponibles -->
    <section class="py-5 bg-white" id="materias">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3"><?= htmlspecialchars(__('index.subjects.title')) ?></h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">
                    <?= htmlspecialchars(__('index.subjects.lead')) ?>
                </p>
            </div>
            <div class="row justify-content-center g-4">
                <div class="col-md-5 col-lg-4">
                    <div class="subject-card">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined">biotech</span>
                        </div>
                        <h3 class="fw-bold mb-3"><?= htmlspecialchars(__('index.subjects.micro')) ?></h3>
                        <p class="text-muted"><?= htmlspecialchars(__('index.subjects.microp')) ?></p>
                        <span class="badge rounded-pill bg-success px-3 py-2"><?= htmlspecialchars(__('index.subjects.available')) ?></span>
                    </div>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="subject-card">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined">science</span>
                        </div>
                        <h3 class="fw-bold mb-3"><?= htmlspecialchars(__('index.subjects.bio')) ?></h3>
                        <p class="text-muted"><?= htmlspecialchars(__('index.subjects.biop')) ?></p>
                        <span class="badge rounded-pill bg-success px-3 py-2"><?= htmlspecialchars(__('index.subjects.available')) ?></span>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <p class="text-muted italic"><?= htmlspecialchars(__('index.subjects.more')) ?></p>
            </div>
        </div>
    </section>

    <!-- <section class="py-5 bg-white" id="planes">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Planes que se adaptan a vos</h2>
                <p class="">Elegí el plan que mejor se adapte a tu etapa de formación médica.</p>
            </div>
            <div class="row g-4 justify-content-center align-items-center">
                <div class="col-lg-4">
                    <div class="card p-4">
                        <div class="card-body">
                            <h5 class="fw-bold  mb-3">Básico</h5>
                            <h2 class="fw-black mb-4">$0 <small class="fs-6 fw-normal">Siempre</small></h2>
                            <ul class="list-unstyled mb-5">
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-success me-2">check_circle</span> Acceso a
                                    500 preguntas</li>
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-success me-2">check_circle</span>
                                    Estadísticas generales</li>
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-success me-2">check_circle</span> 1
                                    simulacro al mes</li>
                            </ul>
                            <a class="btn btn-outline-primary w-100 py-3 fw-bold shadow-sm" href="selecionar-materias.php">Elegí Básico</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card pricing-card featured p-4">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <span class="badge rounded-pill bg-primary px-3 py-2">MÁS POPULAR</span>
                            </div>
                            <h5 class="fw-bold mb-3 text-center">Plan Pro</h5>
                            <h2 class="fw-black mb-4 text-center">ARS $12.000 <small class="fs-6 fw-normal ">/
                                    mes</small></h2>
                            <ul class="list-unstyled mb-5">
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-primary me-2">check_circle</span>
                                    <strong>Preguntas ilimitadas</strong></li>
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-primary me-2">check_circle</span>
                                    Estadísticas avanzadas</li>
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-primary me-2">check_circle</span>
                                    Simulacros ilimitados</li>
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-primary me-2">check_circle</span> Modo
                                    offline</li>
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-primary me-2">check_circle</span> Ranking
                                    nacional</li>
                            </ul>
                            <a class="btn btn-reverse w-100 py-3 fw-bold shadow-sm" href="selecionar-materias.php">Elegí Plan Pro</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card p-4">
                        <div class="card-body">
                            <h5 class="fw-bold  mb-3">Institucional</h5>
                            <h2 class="fw-black mb-4">Cotizá <small class="fs-6 fw-normal">Grupos</small></h2>
                            <ul class="list-unstyled mb-5">
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-success me-2">check_circle</span> Gestión
                                    de grupos</li>
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-success me-2">check_circle</span> Informes
                                    detallados</li>
                                <li class="mb-3"><span
                                        class="material-symbols-outlined text-success me-2">check_circle</span> Soporte
                                    prioritario 24/7</li>
                            </ul>
                            <a class="btn btn-outline-primary w-100 py-3 fw-bold shadow-sm" href="selecionar-materias.php">Contactá ventas</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->
    <section class="py-5 text-white text-center" id="listo">
        <div class="container py-5">
            <h2 class="display-4 fw-bold mb-4"><?= htmlspecialchars(__('index.cta.title')) ?></h2>
            <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 800px;">
                <?= htmlspecialchars(__('index.cta.lead')) ?>
            </p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a class="btn btn-outline btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm" href="selecionar-materias.php"><?= htmlspecialchars(__('index.cta.btn')) ?></a>
                <!-- <a class="btn btn-outline btn-lg rounded-pill px-5 py-3 fw-bold" href="#">Mirá planes anuales</a> -->
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="container py-5">
            <div class="row g-4 g-lg-5 align-items-start">
                <div class="col-lg-5">
                    <a class="d-inline-block mb-3" href="#top" aria-label="<?= htmlspecialchars(__('index.back_to_top')) ?>">
                        <img class="footer-min-logo" src="<?= htmlspecialchars(public_asset_url('img/logo-bd-transparente.png')) ?>" alt="Banco de Choices" width="180" height="48" decoding="async" />
                    </a>
                    <p class="footer-min-tagline mb-0">
                        <?= htmlspecialchars(__('index.footer.brand')) ?>
                    </p>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <p class="footer-min-heading mb-0"><?= htmlspecialchars(__('index.footer.platform')) ?></p>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li><a class="footer-min-link" href="login.php"><?= htmlspecialchars(__('index.footer.f1')) ?></a></li>
                        <li><a class="footer-min-link" href="login.php"><?= htmlspecialchars(__('index.footer.f2')) ?></a></li>
                        <li><a class="footer-min-link" href="#materias"><?= htmlspecialchars(__('index.footer.f3')) ?></a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <p class="footer-min-heading mb-0"><?= htmlspecialchars(__('index.footer.support')) ?></p>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li><a class="footer-min-link" href="login.php"><?= htmlspecialchars(__('index.footer.s1')) ?></a></li>
                        <li><a class="footer-min-link" href="#contacto"><?= htmlspecialchars(__('index.footer.s2')) ?></a></li>
                        <li><a class="footer-min-link" href="#caracteristicas"><?= htmlspecialchars(__('index.footer.s3')) ?></a></li>
                    </ul>
                </div>
                <div class="col-md-4 col-lg-3">
                    <p class="footer-min-heading mb-0"><?= htmlspecialchars(__('index.footer.legal')) ?></p>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li><a class="footer-min-link" href="#terminos"><?= htmlspecialchars(__('index.footer.l1')) ?></a></li>
                        <li><a class="footer-min-link" href="#privacidad"><?= htmlspecialchars(__('index.footer.l2')) ?></a></li>
                        <li><a class="footer-min-link" href="#lgpd"><?= htmlspecialchars(__('index.footer.l3')) ?></a></li>
                        <li><a class="footer-min-link" href="#cookies"><?= htmlspecialchars(__('index.footer.l4')) ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-min-cta mt-5" id="contacto">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <p class="footer-min-cta-title mb-1"><?= htmlspecialchars(__('index.footer.cta_title')) ?></p>
                        <p class="footer-min-cta-sub mb-0"><?= htmlspecialchars(__('index.footer.cta_sub')) ?></p>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex flex-column flex-sm-row flex-wrap align-items-stretch align-items-sm-center gap-2 justify-content-lg-end">
                            <a class="btn btn-primary rounded-pill px-4 fw-semibold" href="selecionar-materias.php"><?= htmlspecialchars(__('index.footer.cta_btn')) ?></a>
                            <a class="footer-min-mail d-inline-flex align-items-center justify-content-center px-3 py-2" href="mailto:contato@bancodechoices.com">contato@bancodechoices.com</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-min-legal-box mt-5 pt-4 border-top footer-min-rule">
                <p id="terminos" style="scroll-margin-top: 5rem;"><strong><?= htmlspecialchars(__('index.footer.legal_terms_head')) ?></strong> <?= htmlspecialchars(__('index.footer.legal_terms_p')) ?></p>
                <p id="privacidad" style="scroll-margin-top: 5rem;"><strong><?= htmlspecialchars(__('index.footer.legal_privacy_head')) ?></strong> <?= htmlspecialchars(__('index.footer.legal_privacy_p')) ?></p>
                <p id="lgpd" style="scroll-margin-top: 5rem;"><strong><?= htmlspecialchars(__('index.footer.legal_lgpd_head')) ?></strong> <?= htmlspecialchars(__('index.footer.legal_lgpd_p')) ?></p>
                <p id="cookies" class="mb-0" style="scroll-margin-top: 5rem;"><strong><?= htmlspecialchars(__('index.footer.legal_cookies_head')) ?></strong> <?= htmlspecialchars(__('index.footer.legal_cookies_p')) ?></p>
            </div>

            <div class="mt-4 pt-4 border-top footer-min-rule d-flex flex-column flex-md-row gap-3 justify-content-between align-items-start align-items-md-center">
                <p class="footer-min-bottom mb-0">© <?= (int) date('Y') ?> BancodeChoices. <?= htmlspecialchars(__('index.footer.rights_reserved')) ?></p>
                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-4 align-items-start align-items-sm-center text-md-end">
                    <p class="footer-min-bottom mb-0" style="max-width: 22rem;">
                        <?= htmlspecialchars(__('index.footer.trust_line')) ?>
                    </p>
                    <a class="footer-min-site" href="https://bancodechoices.com" target="_blank" rel="noopener noreferrer">bancodechoices.com</a>
                </div>
            </div>
        </div>
    </footer>

    <button type="button" class="btn btn-primary btn-back-to-top" id="backToTop" aria-label="<?= htmlspecialchars(__('index.back_to_top')) ?>" title="<?= htmlspecialchars(__('index.back_to_top')) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M18 15l-6-6-6 6" />
        </svg>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var btn = document.getElementById('backToTop');
            if (!btn) return;
            var threshold = 400;
            function updateVisibility() {
                btn.classList.toggle('is-visible', window.scrollY > threshold);
            }
            window.addEventListener('scroll', updateVisibility, { passive: true });
            updateVisibility();
            btn.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        })();
    </script>

</body>

</html>