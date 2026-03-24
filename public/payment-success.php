<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/bootstrap_env.php';
loadProjectEnv();

$status = $_GET['status'] ?? null;
$orderId = $_GET['order_id'] ?? null;

error_log('payment-success invoked; status=' . var_export($status, true) . ' order_id=' . var_export($orderId, true));

$pending = $_SESSION['pending_order'] ?? null;
$userEmail = is_array($pending) ? (string) ($pending['email'] ?? '') : '';
$userName = is_array($pending) ? (string) ($pending['name'] ?? '') : '';

$paymentSuccess = false;
$errorMessage = null;
$processingReturn = false;

if (isset($_GET['error']) && $_GET['error'] === 'payment_failed') {
    $errorMessage = 'El pago no pudo completarse. Podés intentar de nuevo.';
} elseif ($status === 'approved' || $status === 'pending') {
    $paymentSuccess = true;
    $processingReturn = true;
    unset($_SESSION['pending_order'], $_SESSION['selected_materias'], $_SESSION['selected_plan']);
} else {
    $paymentSuccess = false;
    $errorMessage = 'No pudimos identificar el resultado del pago. Si ya pagaste, esperá la confirmación por correo.';
}

$pageTitle = $paymentSuccess
    ? 'Pago recibido'
    : 'Error en el Pago';
?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars($pageTitle) ?> | Banco de Choices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <?php require_once __DIR__ . '/../config/favicon_links.php'; ?>

    <style>
        :root {
            --navy-primary: #002147;
            --navy-dark: #001a38;
            --accent-purple: #6a0392;
            --accent-purple-light: #6a03928e;
            --accent-purple-lighter: #6a039220;
            --success-green: #10b981;
            --error-red: #ef4444;
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
            display: flex;
            align-items: center;
            justify-content: center;
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
            width: 100%;
            padding: 2rem 1rem;
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

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.98);
            overflow: hidden;
            max-width: 600px;
            margin: 0 auto;
        }

        .card-body {
            padding: 3rem 2rem;
            text-align: center;
        }

        .success-icon {
            font-size: 5rem;
            color: var(--success-green);
            margin-bottom: 1rem;
            animation: successBounce 1.2s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .error-icon {
            font-size: 5rem;
            color: var(--error-red);
            margin-bottom: 1rem;
            animation: errorShake 0.5s;
        }

        @keyframes successBounce {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes errorShake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            color: var(--navy-primary);
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .info-box {
            background: var(--accent-purple-lighter);
            border-left: 4px solid var(--accent-purple);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
            text-align: left;
        }

        .info-box p {
            margin: 0.5rem 0;
            color: var(--navy-primary);
        }

        .info-box strong {
            color: var(--accent-purple);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--navy-primary), var(--navy-dark));
            border: none;
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.3px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 33, 71, 0.3);
            color: white;
        }

        .credentials-box {
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            text-align: left;
        }

        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .credential-item:last-child {
            border-bottom: none;
        }

        .credential-label {
            font-weight: 600;
            color: var(--navy-primary);
        }

        .credential-value {
            font-family: 'Courier New', monospace;
            color: var(--accent-purple);
            font-weight: 700;
        }

        .copy-btn {
            background: none;
            border: none;
            color: var(--accent-purple);
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s ease;
            margin-left: 0.5rem;
        }

        .copy-btn:hover {
            transform: scale(1.2);
        }

        .error-message {
            background: #fee2e2;
            border-left: 4px solid var(--error-red);
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <main>
        <div class="card animate__animated animate__zoomIn">
            <div class="card-body">
                <?php if ($paymentSuccess): ?>
                    <div class="success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <h1><?= !empty($processingReturn) ? '¡Recibimos tu pago!' : '¡Pago Confirmado!' ?></h1>
                    <p class="subtitle">
                        <?php if (!empty($processingReturn)): ?>
                            Estamos confirmando el pago con Mercado Pago. <strong>No mostramos contraseñas en esta pantalla</strong> por seguridad: el acceso se libera cuando el pago queda <strong>aprobado</strong> en nuestro sistema (notificación oficial).
                        <?php else: ?>
                            Tu compra ha sido procesada exitosamente
                        <?php endif; ?>
                    </p>

                    <?php if ($userEmail !== ''): ?>
                        <div class="credentials-box">
                            <div class="credential-item">
                                <span class="credential-label">Correo del pedido:</span>
                                <span class="credential-value"><?= htmlspecialchars($userEmail) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="info-box">
                        <p><i class="bi bi-info-circle me-2"></i> <strong>Próximos pasos:</strong></p>
                        <p>1. Si el pago fue aprobado, recibirás tus credenciales por <strong>email</strong> (revisá spam).</p>
                        <p>2. Si el pago quedó pendiente (ej. efectivo), te avisaremos cuando se acredite.</p>
                        <p>3. Podés iniciar sesión cuando recibas el correo con tu contraseña.</p>
                    </div>

                    <a href="<?= htmlspecialchars(getenv('SITE_URL') ?: 'https://bancodechoices.com') ?>/login.php" class="btn-primary-custom">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Ir al Login
                    </a>

                <?php else: ?>
                    <div class="error-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>

                    <h1>Error en el Pago</h1>
                    <p class="subtitle">No pudimos procesar tu compra</p>

                    <div class="error-message">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>

                    <div class="info-box">
                        <p><i class="bi bi-info-circle me-2"></i> <strong>¿Qué hacer ahora?</strong></p>
                        <p>• Intenta nuevamente con tu tarjeta</p>
                        <p>• Verifica que tus datos sean correctos</p>
                        <p>• Contacta a nuestro soporte si el problema persiste</p>
                    </div>

                    <a href="selecionar-materias.php" class="btn-primary-custom">
                        <i class="bi bi-arrow-left me-2"></i>
                        Volver al Inicio
                    </a>

                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Contraseña copiada al portapapeles');
            });
        }
    </script>
</body>

</html>