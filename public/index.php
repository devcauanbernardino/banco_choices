<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Banco de Choices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="../App/assets/css/index.css" />
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold text-primary" href="#">
                <span class="material-symbols-outlined me-2 fs-2">school</span>
                BancodeChoices
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
                    <a class="btn btn-reverse text-decoration-none rounded-pill px-4" href="#">Inscribite gratis</a>
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
    <section class="py-5 bg-white" id="caracteristicas">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Todo lo que necesitás para tu éxito</h2>
                <p class=" mx-auto" style="max-width: 700px;">
                    Herramientas diseñadas por expertos para optimizar tu tiempo de estudio y asegurar los mejores
                    resultados en tus evaluaciones.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 p-4">
                        <div class="card-body">
                            <div class="feature-icon">
                                <span class="material-symbols-outlined fs-2">library_books</span>
                            </div>
                            <h4 class="fw-bold mb-3">+10.000 preguntas comentadas</h4>
                            <p class=" mb-0">
                                Accedé a una amplia base de datos con explicaciones detalladas para cada respuesta,
                                redactadas por especialistas en cada área médica.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4">
                        <div class="card-body">
                            <div class="feature-icon">
                                <span class="material-symbols-outlined fs-2">leaderboard</span>
                            </div>
                            <h4 class="fw-bold mb-3">Estadísticas de desempeño</h4>
                            <p class=" mb-0">
                                Monitoreá tu progreso en tiempo real con gráficos detallados que detectan tus áreas de
                                oportunidad y fortalezas por especialidad médica.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4">
                        <div class="card-body">
                            <div class="feature-icon">
                                <span class="material-symbols-outlined fs-2">assignment_turned_in</span>
                            </div>
                            <h4 class="fw-bold mb-3">Simulacros personalizados</h4>
                            <p class=" mb-0">
                                Creá exámenes a tu medida según temas específicos o nivel de dificultad. Preparate para
                                las condiciones reales de los exámenes oficiales.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-5 bg-light" id="materias">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Materias Disponibles</h2>
                <p class=" mx-auto" style="max-width: 700px;">
                    Explorá nuestro contenido organizado por las especialidades más importantes de la carrera médica.
                </p>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="subject-icon">
                                <span class="material-symbols-outlined">cardiology</span>
                            </div>
                            <h5 class="fw-bold mb-2">Cardiología</h5>
                            <p class="small  mb-0">Fisiopatología, EGC y patologías cardiovasculares
                                centrales.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="subject-icon">
                                <span class="material-symbols-outlined">child_care</span>
                            </div>
                            <h5 class="fw-bold mb-2">Pediatría</h5>
                            <p class="small  mb-0">Crecimiento, desarrollo y patologías infantiles
                                prevalentes.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="subject-icon">
                                <span class="material-symbols-outlined">female</span>
                            </div>
                            <h5 class="fw-bold mb-2">Ginecología</h5>
                            <p class="small  mb-0">Salud reproductiva, obstetricia y patología ginecológica.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="subject-icon">
                                <span class="material-symbols-outlined">medical_services</span>
                            </div>
                            <h5 class="fw-bold mb-2">Cirugía General</h5>
                            <p class="small  mb-0">Técnicas quirúrgicas, trauma y abdomen agudo.</p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="subject-icon">
                                <span class="material-symbols-outlined">coronavirus</span>
                            </div>
                            <h5 class="fw-bold mb-2">Infectología</h5>
                            <p class="small  mb-0">Antibioticoterapia y manejo de enfermedades infecciosas.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="subject-icon">
                                <span class="material-symbols-outlined">psychology</span>
                            </div>
                            <h5 class="fw-bold mb-2">Neurología</h5>
                            <p class="small  mb-0">Examen neurológico y patologías del sistema nervioso.</p>
                        </div>
                    </div>
                </div>
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
                <a class="btn btn-outline btn-lg rounded-pill px-5 py-3 fw-bold text-primary" href="#">Inscribite ahora</a>
                <a class="btn btn-lg rounded-pill px-5 py-3 fw-bold" href="#">Mirá planes anuales</a>
            </div>
        </div>
    </section>

    <footer class="footer pt-5 pb-4">
        <div class="container pt-5">
            <div class="row g-4">
                <div class="col-md-4 mb-4">
                    <a class="navbar-brand d-flex align-items-center fw-bold text-white mb-3" href="#">
                        <span class="material-symbols-outlined me-2">school</span>
                        BancodeChoices
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