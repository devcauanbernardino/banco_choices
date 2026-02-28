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

// Verificar se matérias foram selecionadas
if (empty($_SESSION['selected_materias'])) {
    header('Location: selecionar-materias.php');
    exit;
}

$conexao = new Conexao();
$pdo = $conexao->conectar();

// Buscar detalhes das matérias selecionadas
$materiasIds = $_SESSION['selected_materias'];
$placeholders = implode(',', $materiasIds);
$materiasDetails = $pdo->query("SELECT id, nome FROM materias WHERE id IN ($placeholders)")->fetchAll(PDO::FETCH_ASSOC);

// Definir planos
$plans = [
    [
        'id' => 'monthly',
        'name' => 'Acceso 1 Mes',
        'duration' => '1 mes',
        'durationDays' => 30,
        'price' => 29.90,
        'description' => 'Acceso completo por 30 días',
        'features' => [
            'Acceso a todas las aulas',
            'Materiales de estudio',
            'Soporte por email',
            'Certificado al final'
        ],
        'badge' => null,
        'popular' => false
    ],
    [
        'id' => 'semester',
        'name' => 'Acceso 6 Meses',
        'duration' => '6 meses',
        'durationDays' => 180,
        'price' => 119.90,
        'description' => 'Acceso completo por 180 días',
        'features' => [
            'Acceso a todas las aulas',
            'Materiales de estudio',
            'Soporte por email y chat',
            'Certificado al final',
            '20% de descuento'
        ],
        'badge' => 'Economiza 20%',
        'popular' => true
    ],
    [
        'id' => 'annual',
        'name' => 'Acceso 1 Año',
        'duration' => '1 año',
        'durationDays' => 365,
        'price' => 199.90,
        'description' => 'Acceso completo por 365 días',
        'features' => [
            'Acceso a todas las aulas',
            'Materiales de estudio',
            'Soporte prioritario 24/7',
            'Certificado al final',
            '33% de descuento',
            'Actualizaciones futuras incluidas'
        ],
        'badge' => 'Mejor Valor',
        'popular' => false
    ]
];

// Procesar selección de plano
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $planId = $_POST['plan_id'] ?? null;
    
    // Encontrar el plan seleccionado
    $selectedPlan = null;
    foreach ($plans as $plan) {
        if ($plan['id'] === $planId) {
            $selectedPlan = $plan;
            break;
        }
    }
    
    if ($selectedPlan) {
        $_SESSION['selected_plan'] = $selectedPlan;
        header('Location: checkout-mercadopago.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Seleccionar Plan | Banco de Choices</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header-section {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }

        .header-section h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header-section p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.2);
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
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: var(--accent-purple);
            border-color: white;
            box-shadow: 0 0 0 0.3rem var(--accent-purple-lighter);
        }

        .step-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .step.active .step-label {
            color: white;
        }

        .plans-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .plan-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            border: 2px solid #e5e7eb;
            padding: 2rem;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            overflow: hidden;
        }

        .plan-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-purple), var(--accent-purple-light));
            transition: height 0.3s ease;
        }

        .plan-card:hover {
            border-color: var(--accent-purple);
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(106, 3, 146, 0.2);
        }

        .plan-card.popular {
            border-color: var(--accent-purple);
            box-shadow: 0 20px 40px rgba(106, 3, 146, 0.15);
            transform: scale(1.05);
        }

        .plan-card.popular::before {
            height: 6px;
        }

        .plan-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: linear-gradient(135deg, var(--accent-purple), #8b2e9e);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(106, 3, 146, 0.3);
        }

        .plan-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--navy-primary);
            margin-bottom: 0.5rem;
        }

        .plan-description {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .plan-price {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--navy-primary), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.25rem;
        }

        .plan-price-period {
            font-size: 0.85rem;
            color: #9ca3af;
            margin-bottom: 1.5rem;
        }

        .plan-features {
            list-style: none;
            margin-bottom: 1.5rem;
        }

        .plan-features li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            font-size: 0.9rem;
            color: #374151;
        }

        .plan-features i {
            color: var(--accent-purple);
            font-size: 1.1rem;
        }

        .plan-button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--navy-primary), var(--navy-dark));
            border: none;
            color: white;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            letter-spacing: 0.3px;
        }

        .plan-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 33, 71, 0.3);
            color: white;
        }

        .plan-button:active {
            transform: translateY(0);
        }

        .selected-materias {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .selected-materias h3 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--navy-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .selected-materias h3 i {
            color: var(--accent-purple);
        }

        .materias-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .materia-tag {
            background: var(--accent-purple-lighter);
            border: 1px solid var(--accent-purple);
            color: var(--navy-primary);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 1rem;
        }

        .back-link:hover {
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
        }

        @media (max-width: 768px) {
            .plans-container {
                grid-template-columns: 1fr;
            }

            .plan-card.popular {
                transform: scale(1);
            }

            .header-section h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <main class="container-custom">
        <a href="selecionar-materias.php" class="back-link">
            <i class="bi bi-chevron-left"></i>
            Volver
        </a>

        <!-- Header -->
        <div class="header-section animate__animated animate__fadeInDown">
            <h1>Elige tu Plan</h1>
            <p>Selecciona la duración de acceso que mejor se adapte a ti</p>
        </div>

        <!-- Indicador de Pasos -->
        <div class="step-indicator">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-label">Materias</div>
            </div>
            <div class="step active">
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

        <!-- Matérias Seleccionadas -->
        <div class="selected-materias animate__animated animate__fadeInUp">
            <h3>
                <i class="bi bi-check-circle-fill"></i>
                Materias Seleccionadas
            </h3>
            <div class="materias-list">
                <?php foreach ($materiasDetails as $materia): ?>
                    <span class="materia-tag">
                        <i class="bi bi-book"></i>
                        <?= htmlspecialchars($materia['nome']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Planes -->
        <div class="plans-container animate__animated animate__fadeInUp">
            <?php foreach ($plans as $plan): ?>
                <form method="POST" class="plan-form">
                    <div class="plan-card <?= $plan['popular'] ? 'popular' : '' ?>">
                        <?php if ($plan['badge']): ?>
                            <div class="plan-badge"><?= $plan['badge'] ?></div>
                        <?php endif; ?>

                        <h2 class="plan-name"><?= htmlspecialchars($plan['name']) ?></h2>
                        <p class="plan-description"><?= htmlspecialchars($plan['description']) ?></p>

                        <div class="plan-price">
                            R$ <?= number_format($plan['price'] * count($materiasDetails), 2, ',', '.') ?>
                        </div>
                        <p class="plan-price-period">por <?= htmlspecialchars($plan['duration']) ?></p>

                        <ul class="plan-features">
                            <?php foreach ($plan['features'] as $feature): ?>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?= htmlspecialchars($feature) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                        <button type="submit" class="plan-button">
                            Seleccionar Plan <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
