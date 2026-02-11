<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <title>Simulador | BancodeChoices</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        body {
            background-color: #f6f6f8;
        }
        .option-card:hover {
            border-color: #0d6efd;
            background-color: rgba(13,110,253,.05);
            cursor: pointer;
        }
        .question-map button {
            width: 38px;
            height: 38px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<header class="bg-white border-bottom sticky-top">
    <div class="container py-3 d-flex justify-content-between align-items-center">

        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white p-2 rounded">
                <span class="material-icons">biotech</span>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-primary">BancodeChoices</h6>
                <small class="text-muted">Examen de Residencia Médica</small>
            </div>
        </div>

        <div class="text-end">
            <small class="text-muted d-block">Tiempo restante</small>
            <strong class="text-primary fs-5">
                <span class="material-icons fs-6">timer</span>
                01:42:15
            </strong>
        </div>

    </div>
</header>
<main class="container my-5">
    <div class="row g-4">

        <!-- QUESTION -->
        <div class="col-lg-8">
            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <span class="badge bg-primary-subtle text-primary">
                        Microbiología
                    </span>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary">
                            <span class="material-icons">bookmark_border</span>
                        </button>
                        <button class="btn btn-sm btn-outline-danger">
                            <span class="material-icons">report_problem</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        ¿Cuál es el tratamiento de primera línea para un paciente con insuficiencia cardíaca descompensada?
                    </h5>

                    <!-- OPTIONS -->
                    <div class="vstack gap-3">

                        <label class="border rounded p-3 d-flex gap-3 option-card">
                            <input type="radio" name="opcao" class="form-check-input mt-1">
                            <strong>A</strong>
                            <span>Furosemida endovenosa y monitoreo estricto</span>
                        </label>

                        <label class="border rounded p-3 d-flex gap-3 option-card">
                            <input type="radio" name="opcao" class="form-check-input mt-1">
                            <strong>B</strong>
                            <span>Aumento inmediato de Betabloqueantes</span>
                        </label>

                        <label class="border rounded p-3 d-flex gap-3 option-card">
                            <input type="radio" name="opcao" class="form-check-input mt-1">
                            <strong>C</strong>
                            <span>Administración de Digoxina</span>
                        </label>

                        <label class="border rounded p-3 d-flex gap-3 option-card">
                            <input type="radio" name="opcao" class="form-check-input mt-1">
                            <strong>D</strong>
                            <span>Antibioticoterapia profiláctica</span>
                        </label>

                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <button class="btn btn-outline-secondary">
                        <span class="material-icons">chevron_left</span> Anterior
                    </button>

                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary">
                            Guardar
                        </button>
                        <button class="btn btn-primary">
                            Siguiente <span class="material-icons">chevron_right</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 100px">
                <div class="card-body">

                    <h6 class="fw-bold mb-3">
                        <span class="material-icons text-primary fs-6">grid_view</span>
                        MAPA DE PREGUNTAS
                    </h6>

                    <div class="d-flex flex-wrap gap-2 question-map mb-4">
                        <button class="btn btn-success">1</button>
                        <button class="btn btn-success">2</button>
                        <button class="btn btn-warning">11</button>
                        <button class="btn btn-primary">12</button>
                        <button class="btn btn-light">13</button>
                    </div>

                    <hr>

                    <div class="small text-muted mb-3">
                        <div><span class="badge bg-success"></span> Respondida</div>
                        <div><span class="badge bg-warning"></span> En revisión</div>
                        <div><span class="badge bg-secondary"></span> Pendiente</div>
                        <div><span class="badge bg-primary"></span> Actual</div>
                    </div>

                    <button class="btn btn-danger w-100">
                        Finalizar Examen
                    </button>

                </div>
            </div>
        </div>

    </div>
</main>
