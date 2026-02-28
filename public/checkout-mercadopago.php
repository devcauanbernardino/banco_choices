<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CARREGAR CONFIGURAÇÕES
// ============================================
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    $lines = explode("\n", $envContent);
    
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignorar linhas vazias e comentários
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        // Validar formato KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

require_once __DIR__ . '/../config/conexao.php';

// Verificar si plan y materias fueron seleccionados
if (empty($_SESSION['selected_plan']) || empty($_SESSION['selected_materias'])) {
    header('Location: selecionar-materias.php');
    exit;
}

$plan = $_SESSION['selected_plan'];
$materiasIds = $_SESSION['selected_materias'];

$conexao = new Conexao();
$pdo = $conexao->conectar();

// Buscar detalhes das matérias
if (empty($materiasIds)) {
    $_SESSION['error'] = 'Nenhuma matéria selecionada';
    header('Location: selecionar-materias.php');
    exit;
}

// Preparar placeholders com segurança
$placeholders = array_fill(0, count($materiasIds), '?');
$placeholderStr = implode(',', $placeholders);

try {
    $stmt = $pdo->prepare("SELECT id, nome FROM materias WHERE id IN ($placeholderStr)");
    $stmt->execute($materiasIds);
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($materias)) {
        $_SESSION['error'] = 'Matérias não encontradas';
        header('Location: selecionar-materias.php');
        exit;
    }
} catch (Exception $e) {
    error_log('Erro ao buscar matérias: ' . $e->getMessage());
    $_SESSION['error'] = 'Erro ao carregar matérias';
    header('Location: selecionar-materias.php');
    exit;
}

// Calcular total
$totalPrice = $plan['price'] * count($materias);
$totalPriceCents = intval($totalPrice * 100);

// Gerar ID único para o pedido
$orderId = 'ORDER-' . time() . '-' . rand(1000, 9999);
?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Checkout | Banco de Choices</title>
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
            --success-green: #10b981;
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

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 2rem 1rem;
        }

        .checkout-form-section,
        .order-summary-section {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--navy-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: var(--accent-purple);
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--navy-primary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.85rem 1rem;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            background-color: #f9fafb;
        }

        .form-control:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 0.3rem var(--accent-purple-lighter);
            background-color: #fff;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-name {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .order-item-materia {
            font-weight: 500;
            color: var(--navy-primary);
        }

        .order-item-plan {
            font-size: 0.85rem;
            color: #9ca3af;
        }

        .order-item-price {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--accent-purple);
            font-size: 1.1rem;
        }

        .order-summary-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
            margin: 1.5rem 0;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .order-total-label {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--navy-primary);
        }

        .order-total-amount {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--navy-primary), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-pay {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--accent-purple), #8b2e9e);
            border: none;
            color: white;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            letter-spacing: 0.3px;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(106, 3, 146, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-pay.loading {
            pointer-events: none;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 1rem;
            display: block;
        }

        .back-link:hover {
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .security-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #9ca3af;
            margin-top: 1rem;
        }

        .security-info i {
            color: var(--success-green);
        }

        .mercadopago-info {
            background: var(--accent-purple-lighter);
            border-left: 4px solid var(--accent-purple);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: var(--navy-primary);
        }

        .mercadopago-info i {
            color: var(--accent-purple);
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <main>
        <a href="selecionar-plano.php" class="back-link">
            <i class="bi bi-chevron-left"></i>
            Volver
        </a>

        <div class="checkout-container animate__animated animate__fadeInUp">
            <!-- Formulario de Pago -->
            <div class="checkout-form-section">
                <h2 class="section-title">
                    <i class="bi bi-credit-card"></i>
                    Datos de Contacto
                </h2>

                <form id="payment-form" method="POST" action="process-payment-mp.php">
                    <div class="form-group">
                        <label class="form-label" for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="tu@email.com" required>
                        <small class="form-text">Usaremos este email para tu cuenta y confirmación</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Nombre Completo</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Juan Pérez" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="country">País</label>
                            <input type="text" id="country" name="country" class="form-control" placeholder="Argentina" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="postal">Código Postal</label>
                            <input type="text" id="postal" name="postal" class="form-control" placeholder="1425" required>
                        </div>
                    </div>

                    <div class="mercadopago-info">
                        <i class="bi bi-info-circle"></i>
                        Serás redirigido a MercadoPago para completar el pago de forma segura
                    </div>

                    <div class="form-group form-row full">
                        <div class="form-check">
                            <input type="checkbox" id="terms" name="terms" class="form-check-input" required>
                            <label class="form-check-label" for="terms">
                                Acepto los <a href="#" class="text-decoration-none" style="color: var(--accent-purple); font-weight: 500;">términos y condiciones</a>
                            </label>
                        </div>
                    </div>

                    <!-- Campos ocultos -->
                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                    <input type="hidden" name="total_price" value="<?= $totalPrice ?>">
                    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                    <input type="hidden" name="plan_duration_days" value="<?= $plan['durationDays'] ?>">
                    <input type="hidden" name="materias" value="<?= implode(',', $materiasIds) ?>">

                    <button type="submit" class="btn-pay" id="submit-btn">
                        <i class="bi bi-lock-fill"></i>
                        Ir a MercadoPago - R$ <?= number_format($totalPrice, 2, ',', '.') ?>
                    </button>

                    <div class="security-info">
                        <i class="bi bi-shield-check"></i>
                        Pago seguro con MercadoPago
                    </div>
                </form>
            </div>

            <!-- Resumen del Pedido -->
            <div class="order-summary-section">
                <h2 class="section-title">
                    <i class="bi bi-receipt"></i>
                    Resumen del Pedido
                </h2>

                <div>
                    <?php foreach ($materias as $materia): ?>
                        <div class="order-item">
                            <div class="order-item-name">
                                <span class="order-item-materia">
                                    <i class="bi bi-book me-2"></i><?= htmlspecialchars($materia['nome']) ?>
                                </span>
                                <span class="order-item-plan"><?= $plan['name'] ?></span>
                            </div>
                            <span class="order-item-price">R$ <?= number_format($plan['price'], 2, ',', '.') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary-divider"></div>

                <div class="order-total">
                    <span class="order-total-label">Total</span>
                    <span class="order-total-amount">R$ <?= number_format($totalPrice, 2, ',', '.') ?></span>
                </div>

                <div style="background: var(--accent-purple-lighter); border-left: 4px solid var(--accent-purple); padding: 1rem; border-radius: 8px; margin-top: 1.5rem;">
                    <p style="font-size: 0.85rem; color: var(--navy-primary); margin: 0;">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Acceso:</strong> <?= $plan['duration'] ?> a partir de la confirmación del pago.
                    </p>
                </div>

                <div style="background: var(--success-green); background-opacity: 0.1; border-left: 4px solid var(--success-green); padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                    <p style="font-size: 0.85rem; color: #065f46; margin: 0;">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Después del pago:</strong> Recibirás un email con tu usuario y contraseña para acceder.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Redirigiendo...';
        });
    </script>
</body>

</html>
