<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Banco de Choices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../App/assets/css/index.css" />
    <!-- Favicon Redondo -->
    <link rel="icon" type="image/x-icon" href="caminho/para/favicon-round.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="../App/assets/img/favicon-round-32x32.png">

    <!-- Ícone Apple Redondo -->
    <link rel="apple-touch-icon" sizes="180x180" href="../App/assets/img/apple-touch-icon-round.png">

    <!-- Ícone Android Redondo -->
    <link rel="icon" type="image/png" sizes="192x192" href="../App/assets/img/favicon-round-192x192.png">
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

        .footer {
            background-color: #320146;
            color: #94a3b8;
        }

        .container {
            max-width: 1320px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold text-primary" href="#">
                <img src="../App/assets/img/logo-bd-transparente.png" alt="logo" style="width: 60px; height: 60px;" />
            </a>
            <button class="navbar-toggler" data-bs-target="#navbarNav" data-bs-toggle="collapse" type="button">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-4">
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3" href="#caracteristicas">Características</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3" href="#materias">Materias</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link fw-medium px-3" href="#planes">Planes</a>
                    </li> -->
                </ul>
                <div class="d-flex gap-2">
                    <a class="btn btn-link text-decoration-none rounded-pill fw-bold" href="login.php">Iniciá Sesión</a>
                    <a class="btn btn-reverse text-decoration-none rounded-pill px-4" href="cadastro.php">Inscribite gratis</a>
                </div>
            </div>
        </div>
    </nav>
    <header class="hero-section">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <span class="badge rounded-pill badge-soft mb-3">Herramienta Médica Argentina</span>
                    <h1 class="display-3 fw-bold mb-4 text-dark">Dominá el examen de residencia y tus exámenes médicos
                    </h1>
                    <p class="lead mb-5 ">
                        La plataforma premier diseñada para médicos argentinos que buscan la excelencia. Estudiá con el
                        banco de preguntas más completo, actualizado y comentado por especialistas.
                    </p>
                    <div class="d-flex flex-column flex-md-row gap-3">
                        <a class="btn btn-reverse btn-lg rounded-pill px-5" href="#">Empezá hoy gratis</a>
                        <a class="btn btn-outline-primary btn-lg rounded-pill px-5" href="#">Mirá la demo</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-container">
                        <img alt="Estudiante de medicina estudiando" class="img-fluid w-100"
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
                <h2 class="display-5 fw-bold mb-3">Todo lo que necesitás para tu éxito</h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">
                    Herramientas diseñadas por expertos para optimizar tu tiempo de estudio y asegurar los mejores resultados.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                    <div class="card-custom h-100">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined fs-2">library_books</span>
                        </div>
                        <h4 class="fw-bold mb-3">Preguntas Comentadas</h4>
                        <p class="text-muted mb-0">
                            Accedé a explicaciones detalladas para cada respuesta, redactadas para que entiendas el "por qué" de cada opción.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card-custom h-100">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined fs-2">leaderboard</span>
                        </div>
                        <h4 class="fw-bold mb-3">Progreso en Tiempo Real</h4>
                        <p class="text-muted mb-0">
                            Monitoreá tu desempeño con estadísticas que detectan tus fortalezas y áreas que necesitan más refuerzo.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                    <div class="card-custom h-100">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined fs-2">assignment_turned_in</span>
                        </div>
                        <h4 class="fw-bold mb-3">Simulacros Reales</h4>
                        <p class="text-muted mb-0">
                            Preparate con exámenes que replican las condiciones reales de las evaluaciones oficiales.
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
                <h2 class="display-5 fw-bold mb-3">Materias Disponibles</h2>
                <p class="text-muted mx-auto" style="max-width: 700px;">
                    Actualmente contamos con contenido especializado en las siguientes áreas fundamentales:
                </p>
            </div>
            <div class="row justify-content-center g-4">
                <div class="col-md-5 col-lg-4">
                    <div class="subject-card">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined">biotech</span>
                        </div>
                        <h3 class="fw-bold mb-3">Microbiología</h3>
                        <p class="text-muted">Estudio completo de bacterias, virus, hongos y parásitos con enfoque clínico.</p>
                        <span class="badge rounded-pill bg-success px-3 py-2">Disponible</span>
                    </div>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div class="subject-card">
                        <div class="feature-icon">
                            <span class="material-symbols-outlined">science</span>
                        </div>
                        <h3 class="fw-bold mb-3">Biología Celular</h3>
                        <p class="text-muted">Fundamentos de la vida celular, genética y mecanismos moleculares esenciales.</p>
                        <span class="badge rounded-pill bg-success px-3 py-2">Disponible</span>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <p class="text-muted italic">Estamos trabajando para agregar más materias próximamente...</p>
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
                            <a class="btn btn-outline-primary w-100 py-3 fw-bold" href="#">Elegí Básico</a>
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
                            <a class="btn btn-reverse w-100 py-3 fw-bold shadow" href="#">Elegí Plan Pro</a>
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
                            <a class="btn btn-outline-primary w-100 py-3 fw-bold" href="#">Contactá ventas</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->
    <section class="py-5 text-white text-center" id="listo">
        <div class="container py-5">
            <h2 class="display-4 fw-bold mb-4">¿Listo para asegurar tu vacante?</h2>
            <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 800px;">
                Unite a miles de médicos que ya están transformando su manera de estudiar con la plataforma de educación
                médica más avanzada de la región.
            </p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                <a class="btn btn-outline btn-lg rounded-pill px-5 py-3 fw-bold" href="cadastro.php">Inscribite ahora</a>
                <!-- <a class="btn btn-outline btn-lg rounded-pill px-5 py-3 fw-bold" href="#">Mirá planes anuales</a> -->
            </div>
        </div>
    </section>

    <footer class="footer pt-5 pb-4">
        <div class="container pt-5">
            <div class="row justify-content-around">
                <div class="col-md-4 mb-4">
                    <a class="navbar-brand d-flex align-items-center fw-bold text-white mb-3" href="#">
                        <img src="../App/assets/img/logo-bd-transparente.png" alt="logo" style="width: 40px; height: 40px; filter: brightness(0) invert(1);" />
                    </a>
                    <p class="pe-lg-5">
                        Formando a los especialistas del mañana con tecnología de vanguardia y contenido médico de la
                        más alta calidad científica en Argentina.
                    </p>
                </div>

                <div class="col-md-2 col-6">
                    <h6 class="text-white fw-bold mb-4">Plataforma</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a class="text-decoration-none nav-link" href="#">Banco de Preguntas</a></li>
                        <li class="mb-2"><a class="text-decoration-none nav-link" href="#">Simulacros Examen</a></li>
                        <li class="mb-2"><a class="text-decoration-none nav-link" href="#">Especialidades</a></li>
                    </ul>
                </div>

                <div class="col-md-2 col-6">
                    <h6 class="text-white fw-bold mb-4">Soporte</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a class="text-decoration-none nav-link" href="#">Centro de ayuda</a></li>
                        <li class="mb-2"><a class="text-decoration-none nav-link" href="#">Contacto</a></li>
                        <li class="mb-2"><a class="text-decoration-none nav-link" href="#">Blog médico</a></li>
                    </ul>
                </div>

                <div class="col-md-2">
                    <h6 class="text-white fw-bold mb-4">Legal</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a class="text-decoration-none nav-link" href="#">Términos y condiciones</a>
                        </li>
                        <li class="mb-2">
                            <a class="text-decoration-none nav-link" href="#">Política de Privacidad</a>
                        </li>
                        <li class="mb-2">
                            <a class="text-decoration-none nav-link" href="#">Protección de Datos (LGPD)</a>
                        </li>
                        <li class="mb-2">
                            <a class="text-decoration-none nav-link" href="#">Cookies</a>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="mt-5 mb-4 border-secondary opacity-25" />

            <div class="text-center">
                <p class="small mb-1">
                    © 2026 BancodeChoices. Todos los derechos reservados.
                </p>
                <p class="small opacity-75 mb-0">
                    Este sitio cumple con la Ley General de Protección de Datos (Ley Nº 13.709/2018 – LGPD).
                    Los datos personales son tratados de forma segura, transparente y únicamente para los fines
                    informados.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>