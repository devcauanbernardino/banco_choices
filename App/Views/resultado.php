<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="UTF-8">
    <title>Resultados del Simulacro | BancodeChoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        body {
            background-color: #f6f6f8;
        }

        .score-circle {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 10px solid rgba(13, 110, 253, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .score-circle::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 10px solid #0d6efd;
            border-top-color: transparent;
            transform: rotate(-45deg);
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <header class="bg-white border-bottom sticky-top">
        <div class="container py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white p-2 rounded">
                    <span class="material-icons">school</span>
                </div>
                <h6 class="fw-bold mb-0 text-primary">BancodeChoices</h6>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="container my-5">

        <!-- TITLE -->
        <div class="text-center mb-5">
            <h2 class="fw-bold text-uppercase">Resultados del Simulacro</h2>
        </div>

        <!-- SCORE -->
        <div class="card shadow-sm mb-5">
            <div class="card-body text-center py-5">
                <div class="score-circle mx-auto mb-3">
                    <div>
                        <div class="fs-1 fw-bold text-primary">85%</div>
                        <small class="text-muted text-uppercase fw-bold">Correcto</small>
                    </div>
                </div>

                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar" style="width: 85%"></div>
                </div>

                <small class="text-muted">Has superado el promedio del mes (72%)</small>
            </div>
        </div>

        <!-- METRICS -->
        <div class="row g-4 mb-5">

            <div class="col-md-4">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <span class="material-icons text-success fs-2">check_circle</span>
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Aciertos</small>
                            <div class="fs-4 fw-bold">85 <small class="text-muted">/ 100</small></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-start border-danger border-4">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <span class="material-icons text-danger fs-2">cancel</span>
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Errores</small>
                            <div class="fs-4 fw-bold">15 <small class="text-muted">/ 100</small></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <span class="material-icons text-primary fs-2">timer</span>
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Tiempo</small>
                            <div class="fs-4 fw-bold">42:15 <small class="text-muted">min</small></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- TABLE -->
        <div class="card shadow-sm">
            <div class="card-header bg-light fw-bold">
                Desglose de Preguntas
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>01</td>
                            <td>Pediatría - Crecimiento</td>
                            <td class="text-success fw-bold">
                                <span class="material-icons align-middle">check_circle</span> Correcta
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary">Ver Revisión</button>
                            </td>
                        </tr>
                        <tr>
                            <td>02</td>
                            <td>Cirugía General</td>
                            <td class="text-danger fw-bold">
                                <span class="material-icons align-middle">cancel</span> Incorrecta
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary">Ver Revisión</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer text-center bg-light">
                <button class="btn btn-link fw-bold">
                    <span class="material-icons align-middle">expand_more</span>
                    Mostrar todas las preguntas
                </button>
            </div>
        </div>

    </main>

    <!-- FOOTER ACTIONS -->
    <footer class="bg-white border-top mt-5">
        <div class="container py-4 d-flex flex-column flex-md-row justify-content-center gap-3">
            <a href="dashboard.php" class="btn btn-outline-secondary px-4">
                <span class="material-icons align-middle">dashboard</span>
                Dashboard
            </a>
            <a href="bancoperguntas.php" class="btn btn-primary px-4">
                <span class="material-icons align-middle">refresh</span>
                Nuevo Simulacro
            </a>
        </div>
    </footer>

</body>

</html>