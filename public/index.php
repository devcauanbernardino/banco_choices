<?php
require_once __DIR__ . '/../config/public_url.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(locale_html_lang()) ?>" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <?php require_once __DIR__ . '/../App/Views/includes/theme-head-public.php'; ?>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Banco de Choices</title>
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

        body {
            font-family: var(--bs-font-sans-serif);
            background-color: #f8fafc;
            overflow-x: hidden;
        }

        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
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

        .btn {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }

        .btn:hover {
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
            position: relative;
            background: linear-gradient(165deg, #150520 0%, #2d0a42 38%, #1e0b2e 100%);
            color: #cbd5e1;
            overflow: hidden;
        }

        .site-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #6a0392, #c084fc, #002147);
            z-index: 2;
        }

        .site-footer .footer-glow {
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(106, 3, 146, 0.35) 0%, transparent 70%);
            top: -120px;
            right: -100px;
            pointer-events: none;
            z-index: 0;
        }

        .site-footer .footer-glow-2 {
            position: absolute;
            width: 360px;
            height: 360px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 33, 71, 0.25) 0%, transparent 70%);
            bottom: -80px;
            left: -80px;
            pointer-events: none;
            z-index: 0;
        }

        .site-footer .container {
            position: relative;
            z-index: 1;
        }

        .footer-heading {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 1.1rem;
        }

        .footer-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #e2e8f0;
            text-decoration: none;
            font-size: 0.9375rem;
            font-weight: 500;
            padding: 0.2rem 0;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .footer-link:hover {
            color: #fff;
            transform: translateX(4px);
        }

        .footer-brand-lead {
            font-size: 0.95rem;
            line-height: 1.65;
            color: #94a3b8;
            max-width: 22rem;
        }

        .footer-contact-panel {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        }

        .footer-contact-panel .footer-contact-title {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.35rem;
        }

        .footer-contact-panel .footer-contact-sub {
            font-size: 0.875rem;
            color: #94a3b8;
            margin-bottom: 1rem;
        }

        .footer-btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.15rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #fff;
            text-decoration: none;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .footer-btn-outline:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.55);
            color: #fff;
        }

        .footer-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.15rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 600;
            background: linear-gradient(135deg, #6a0392, #8b2ec7);
            color: #fff;
            text-decoration: none;
            border: none;
            box-shadow: 0 4px 16px rgba(106, 3, 146, 0.45);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .footer-btn-primary:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(106, 3, 146, 0.5);
        }

        .footer-legal-box {
            background: rgba(0, 0, 0, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 0.875rem;
            padding: 1.35rem 1.5rem;
            font-size: 0.8125rem;
            line-height: 1.65;
            color: #94a3b8;
        }

        .footer-legal-box p {
            margin-bottom: 0.85rem;
        }

        .footer-legal-box p:last-child {
            margin-bottom: 0;
        }

        .footer-legal-box strong {
            color: #cbd5e1;
            font-weight: 600;
        }

        .footer-bottom-bar {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 1.75rem;
            margin-top: 2.5rem;
        }

        .footer-copyright {
            font-size: 0.8125rem;
            color: #94a3b8;
        }

        .footer-trust {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.35);
            max-width: 36rem;
        }

        .container {
            max-width: 1320px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold text-primary" href="index.php">
                <img src="<?= htmlspecialchars(public_asset_url('img/logo-bd-transparente.png')) ?>" alt="Banco de Choices" style="width: 200px; height: auto; max-height: 56px; object-fit: contain;" />
            </a>
            <button class="navbar-toggler" data-bs-target="#navbarNav" data-bs-toggle="collapse" type="button">
                <span class="navbar-toggler-icon"></span>
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

    <footer class="site-footer pt-5 pb-4">
        <div class="footer-glow" aria-hidden="true"></div>
        <div class="footer-glow-2" aria-hidden="true"></div>
        <div class="container pt-4 pb-2">
            <div class="row g-4 g-lg-5 justify-content-between">
                <div class="col-lg-4 col-md-6">
                    <a class="d-inline-flex align-items-center mb-3 text-decoration-none" href="index.php">
                        <img src="<?= htmlspecialchars(public_asset_url('img/logo-bd-transparente.png')) ?>" alt="Banco de Choices" style="width: 168px; height: auto; max-height: 56px; object-fit: contain;" />
                    </a>
                    <p class="footer-brand-lead mb-4">
                        <?= htmlspecialchars(__('index.footer.brand')) ?>
                    </p>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <p class="footer-heading mb-0"><?= htmlspecialchars(__('index.footer.platform')) ?></p>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li class="mb-2"><a class="footer-link" href="login.php"><?= htmlspecialchars(__('index.footer.f1')) ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="login.php"><?= htmlspecialchars(__('index.footer.f2')) ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="#materias"><?= htmlspecialchars(__('index.footer.f3')) ?></a></li>
                    </ul>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <p class="footer-heading mb-0"><?= htmlspecialchars(__('index.footer.support')) ?></p>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li class="mb-2"><a class="footer-link" href="login.php"><?= htmlspecialchars(__('index.footer.s1')) ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="#contacto"><?= htmlspecialchars(__('index.footer.s2')) ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="#caracteristicas"><?= htmlspecialchars(__('index.footer.s3')) ?></a></li>
                    </ul>
                </div>

                <div class="col-md-6 col-lg-3">
                    <p class="footer-heading mb-0"><?= htmlspecialchars(__('index.footer.legal')) ?></p>
                    <ul class="list-unstyled mt-3 mb-0">
                        <li class="mb-2"><a class="footer-link" href="#terminos"><?= htmlspecialchars(__('index.footer.l1')) ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="#privacidad"><?= htmlspecialchars(__('index.footer.l2')) ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="#lgpd"><?= htmlspecialchars(__('index.footer.l3')) ?></a></li>
                        <li class="mb-2"><a class="footer-link" href="#cookies"><?= htmlspecialchars(__('index.footer.l4')) ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-contact-panel mt-4 mt-lg-5" id="contacto">
                <div class="row align-items-center gy-3">
                    <div class="col-lg-7">
                        <p class="footer-contact-title mb-1"><?= htmlspecialchars(__('index.footer.cta_title')) ?></p>
                        <p class="footer-contact-sub mb-0"><?= htmlspecialchars(__('index.footer.cta_sub')) ?></p>
                    </div>
                    <div class="col-lg-5">
                        <div class="d-flex flex-column flex-sm-row flex-lg-column flex-xl-row gap-2 justify-content-lg-end">
                            <a class="footer-btn-primary justify-content-center" href="selecionar-materias.php"><?= htmlspecialchars(__('index.footer.cta_btn')) ?></a>
                            <a class="footer-btn-outline justify-content-center" href="mailto:contato@bancodechoices.com">contato@bancodechoices.com</a>
                        </div>
                        <p class="small mt-2 mb-0 text-center text-lg-end" style="color: rgba(255,255,255,0.45);">
                            <a class="text-decoration-none" style="color: rgba(255,255,255,0.65);" href="https://bancodechoices.com" target="_blank" rel="noopener noreferrer">bancodechoices.com</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="footer-legal-box mt-4">
                <p id="terminos" style="scroll-margin-top: 5rem;"><strong>Términos.</strong> Los servicios de BancodeChoices se ofrecen bajo los términos generales de uso del sitio. El acceso implica la aceptación de estas condiciones.</p>
                <p id="privacidad" style="scroll-margin-top: 5rem;"><strong>Privacidad.</strong> Tratamos los datos personales conforme a nuestra política de privacidad y la normativa aplicable.</p>
                <p id="lgpd" style="scroll-margin-top: 5rem;"><strong>LGPD.</strong> Cumplimos con la protección de datos personales (Ley Nº 13.709/2018 y principios equivalentes en la región).</p>
                <p id="cookies" class="mb-0" style="scroll-margin-top: 5rem;"><strong>Cookies.</strong> Utilizamos cookies necesarias para el funcionamiento del sitio; podés ajustar preferencias en tu navegador.</p>
            </div>

            <div class="footer-bottom-bar">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <p class="footer-copyright mb-0">© <?= (int) date('Y') ?> BancodeChoices. Todos los derechos reservados.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="footer-trust mb-0 ms-md-auto">
                            Datos personales tratados de forma segura y transparente, únicamente para los fines informados.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>