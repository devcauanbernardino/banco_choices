<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CARREGAR CONFIGURAÇÕES
// ============================================
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

require_once __DIR__ . '/../config/conexao.php';

$conexao = new Conexao();
$pdo = $conexao->conectar();

// Buscar matérias do banco de dados
$materias = $pdo->query("SELECT id, nome FROM materias ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Inicializar sessão com matérias selecionadas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedMaterias = $_POST['materias'] ?? [];
    
    if (empty($selectedMaterias)) {
        $error = 'Selecione pelo menos uma matéria';
    } else {
        $_SESSION['selected_materias'] = array_map('intval', $selectedMaterias);
        header('Location: selecionar-plano.php');
        exit;
    }
}

$selectedMaterias = $_SESSION['selected_materias'] ?? [];
?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Seleccionar Materias | Banco de Choices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>
        :root {
            --navy-primary: #002147;
            --navy-dark: #001a38;
            --accent-purple: #6a0392;
            --accent-purple-light: #6a03928e;
            --accent-purple-lighter: #6a039220;
            --bg-light: #f8f9fa;
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

        .container-custom {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
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
            padding: 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--navy-primary), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .card-header p {
            font-size: 0.95rem;
            font-weight: 500;
            color: #6b7280;
            margin-top: 0.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #9ca3af;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: var(--accent-purple);
            border-color: var(--accent-purple);
            color: white;
            box-shadow: 0 0 0 0.3rem var(--accent-purple-lighter);
        }

        .step-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #9ca3af;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .step.active .step-label {
            color: var(--accent-purple);
        }

        .materias-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .materia-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            position: relative;
            overflow: hidden;
        }

        .materia-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--accent-purple-lighter);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0;
        }

        .materia-card:hover {
            border-color: var(--accent-purple);
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(106, 3, 146, 0.2);
        }

        .materia-card.selected {
            border-color: var(--accent-purple);
            background: var(--accent-purple-lighter);
            box-shadow: 0 10px 25px rgba(106, 3, 146, 0.2);
        }

        .materia-card-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .materia-checkbox {
            width: 24px;
            height: 24px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .materia-card input[type="checkbox"]:checked + .materia-checkbox {
            background-color: var(--accent-purple);
            border-color: var(--accent-purple);
        }

        .materia-card input[type="checkbox"] {
            display: none;
        }

        .materia-info {
            flex: 1;
        }

        .materia-name {
            font-weight: 600;
            color: var(--navy-primary);
            font-size: 1rem;
            margin: 0;
        }

        .materia-card.selected .materia-name {
            color: var(--accent-purple);
        }

        .materia-description {
            font-size: 0.85rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        .materia-icon {
            font-size: 1.5rem;
            color: var(--accent-purple);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .materia-card.selected .materia-icon {
            opacity: 1;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .btn-continue {
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
            width: 100%;
        }

        .btn-continue:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 33, 71, 0.3);
            color: white;
        }

        .btn-continue:active {
            transform: translateY(-1px);
        }

        .btn-continue:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .selected-count {
            font-size: 0.9rem;
            color: #6b7280;
            text-align: center;
            margin-bottom: 1rem;
        }

        .selected-count strong {
            color: var(--accent-purple);
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .materias-container {
                grid-template-columns: 1fr;
            }

            .step-indicator {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .step-indicator::before {
                display: none;
            }
        }
    </style>
</head>

<body>
    <main class="container-custom">
        <div class="card animate__animated animate__fadeInUp">
            <div class="card-header">
                <h1>Seleccionar Materias</h1>
                <p>Elige las materias que deseas estudiar</p>
            </div>

            <div class="card-body">
                <!-- Indicador de Pasos -->
                <div class="step-indicator">
                    <div class="step active">
                        <div class="step-number">1</div>
                        <div class="step-label">Materias</div>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-label">Plan</div>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-label">Pago</div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-label">Confirmación</div>
                    </div>
                </div>

                <!-- Mensaje de Error -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario de Selección -->
                <form method="POST" id="materiasForm">
                    <!-- Materias -->
                    <div class="materias-container">
                        <?php foreach ($materias as $materia): ?>
                            <?php $isSelected = in_array($materia['id'], $selectedMaterias); ?>
                            <label class="materia-card <?= $isSelected ? 'selected' : '' ?>">
                                <input type="checkbox" name="materias[]" value="<?= $materia['id'] ?>" 
                                    <?= $isSelected ? 'checked' : '' ?>>
                                <div class="materia-card-content">
                                    <div class="materia-checkbox"></div>
                                    <div class="materia-info">
                                        <p class="materia-name"><?= htmlspecialchars($materia['nome']) ?></p>
                                        <p class="materia-description">Acceso completo a todos los temas</p>
                                    </div>
                                    <i class="bi bi-check-circle-fill materia-icon"></i>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Contador de Seleccionadas -->
                    <div class="selected-count">
                        Seleccionadas: <strong id="selectedCount">0</strong> materia(s)
                    </div>

                    <!-- Botón Continuar -->
                    <button type="submit" class="btn-continue">
                        Continuar <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('input[name="materias[]"]');
            const selectedCountSpan = document.getElementById('selectedCount');
            const form = document.getElementById('materiasForm');

            function updateCount() {
                const checked = document.querySelectorAll('input[name="materias[]"]:checked').length;
                selectedCountSpan.textContent = checked;
            }

            function updateCardStyle(checkbox) {
                const card = checkbox.closest('.materia-card');
                if (checkbox.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    updateCardStyle(this);
                    updateCount();
                });
                updateCardStyle(checkbox);
            });

            form.addEventListener('submit', function (e) {
                const checked = document.querySelectorAll('input[name="materias[]"]:checked').length;
                if (checked === 0) {
                    e.preventDefault();
                    alert('Por favor, selecciona al menos una materia');
                }
            });

            updateCount();
        });
    </script>
</body>

</html>
