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

// Verificar status do pagamento
$status = $_GET['status'] ?? null;
$orderId = $_GET['order_id'] ?? null;

// registrar execução para diagnosticar problemas posteriores
error_log("payment-success invoked; status=" . var_export($status, true) . " order_id=" . var_export($orderId, true) . " session_pending=" . (empty($_SESSION['pending_order']) ? 'no' : 'yes'));

// ============================================
// PROCESSAR PAGAMENTO APROVADO
// ============================================

if ($status === 'approved' && !empty($_SESSION['pending_order'])) {
    $order = $_SESSION['pending_order'];

    $conexao = new Conexao();
    $pdo = $conexao->conectar();

    try {
        // ============================================
        // 1. GERAR SENHA ALEATÓRIA
        // ============================================

        $password = generateRandomPassword();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // ============================================
        // 2. CRIAR USUÁRIO NO BANCO DE DADOS
        // ============================================

        $stmt = $pdo->prepare("
            INSERT INTO users (nome, email, senha, created_at)
            VALUES (:nome, :email, :senha, NOW())
        ");

        $stmt->execute([
            ':nome' => $order['name'],
            ':email' => $order['email'],
            ':senha' => $hashedPassword
        ]);

        $userId = $pdo->lastInsertId();

        // ============================================
        // 3. CRIAR REGISTRO DE PEDIDO
        // ============================================

        $stmt = $pdo->prepare("
            INSERT INTO pedidos (email, nome, valor_total, status, stripe_payment_id, data_criacao)
            VALUES (:email, :nome, :valor_total, :status, :payment_id, NOW())
        ");

        $stmt->execute([
            ':email' => $order['email'],
            ':nome' => $order['name'],
            ':valor_total' => $order['total_price'],
            ':status' => 'completed',
            ':payment_id' => $orderId
        ]);

        $pedidoId = $pdo->lastInsertId();

        // ============================================
        // 4. CRIAR ITENS DO PEDIDO (MATERIAS)
        // ============================================

        $stmt = $pdo->prepare("
            INSERT INTO pedidos_itens (pedido_id, materia_id, plano_id, preco, data_expiracao)
            VALUES (:pedido_id, :materia_id, :plano_id, :preco, DATE_ADD(NOW(), INTERVAL :dias DAY))
        ");

        foreach ($order['materias'] as $materiaId) {
            error_log("Tentando inserir materia ID: " . $materiaId);
            $stmt->execute([
                ':pedido_id' => $pedidoId,
                ':materia_id' => intval($materiaId),
                ':plano_id' => $order['plan_id'],
                ':preco' => $order['total_price'] / count($order['materias']),
                ':dias' => $order['plan_duration_days']
            ]);
        }

        // ============================================
        // 5. ENVIAR EMAIL COM CREDENCIAIS (não interrompe o fluxo)
        // ============================================

        // apenas dispara se as credenciais realmente existem (evita exceções em dev)
        $mailUser = getenv('MAIL_USERNAME');
        $mailPass = getenv('MAIL_PASSWORD');
        if (!empty($mailUser) && !empty($mailPass) && strpos($mailUser, 'seu_email') === false) {
            sendConfirmationEmail(
                $order['email'],
                $order['name'],
                $password,
                $order['total_price'],
                $order['plan_id']
            );
        } else {
            error_log("payment-success: credenciais de e-mail ausentes ou padrões, ignorando envio.");
        }

        // ============================================
        // 6. LIMPAR SESSÃO
        // ============================================

        unset($_SESSION['pending_order']);
        unset($_SESSION['selected_materias']);
        unset($_SESSION['selected_plan']);

        $paymentSuccess = true;
        $userEmail = $order['email'];
        $userName = $order['name'];
        $userPassword = $password;
    } catch (Exception $e) {
        // incluir detalhes completos no log para facilitar debug
        $msg = "Erro ao processar pagamento: " . $e->getMessage();
        $msg .= " em " . $e->getFile() . "(" . $e->getLine() . ")";
        $msg .= "\n" . $e->getTraceAsString();
        error_log($msg);

        $paymentSuccess = false;
        $errorMessage = "Erro ao processar seu pedido. Entraremos em contato em breve.";
    }
} else {
    $paymentSuccess = false;
    $errorMessage = "Pagamento não confirmado ou dados não encontrados.";
}

// ============================================
// FUNÇÃO PARA GERAR SENHA ALEATÓRIA
// ============================================

function generateRandomPassword($length = 12)
{
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $special = '!@#$%^&*';

    $allChars = $uppercase . $lowercase . $numbers . $special;
    $password = '';

    // Garantir que tenha pelo menos um de cada tipo
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $special[rand(0, strlen($special) - 1)];

    // Completar com caracteres aleatórios
    for ($i = 4; $i < $length; $i++) {
        $password .= $allChars[rand(0, strlen($allChars) - 1)];
    }

    // Embaralhar
    $password = str_shuffle($password);

    return $password;
}

// ============================================
// FUNÇÃO PARA ENVIAR EMAIL
// ============================================

function sendConfirmationEmail($email, $name, $password, $totalPrice, $planId)
{
    // Carregar PHPMailer
    require_once __DIR__ . '/../vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer();

    try {
        // ============================================
        // CONFIGURAÇÃO DO SMTP
        // ============================================
        // ALTERE ESTES VALORES COM SUAS CREDENCIAIS

        $mail->isSMTP();
        $mail->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $mail->Port = getenv('MAIL_PORT') ?: 587;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = getenv('MAIL_ENCRYPTION') ?: 'tls';
        $mail->Username = getenv('MAIL_USERNAME');
        $mail->Password = getenv('MAIL_PASSWORD');

        // ============================================
        // CONFIGURAÇÃO DO EMAIL
        // ============================================

        $mail->setFrom(getenv('MAIL_FROM'), getenv('MAIL_FROM_NAME'));
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        // ============================================
        // ASSUNTO E CORPO DO EMAIL
        // ============================================

        $mail->Subject = '¡Bienvenido a Banco de Choices! Tus credenciales de acceso';

        $siteUrl = getenv('SITE_URL') ?: 'http://localhost:8000';
        $planName = getPlanName($planId);

        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #f9fafb; padding: 20px; border-radius: 8px; }
                .header { background: linear-gradient(135deg, #002147, #6a0392); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 20px; }
                .content { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                .credentials { background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #6a0392; }
                .credentials p { margin: 10px 0; }
                .credentials strong { color: #6a0392; }
                .button { display: inline-block; background: linear-gradient(135deg, #002147, #6a0392); color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                .footer { text-align: center; color: #9ca3af; font-size: 12px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Bienvenido a Banco de Choices!</h1>
                    <p>Tu compra ha sido procesada exitosamente</p>
                </div>
                
                <div class='content'>
                    <p>Hola <strong>$name</strong>,</p>
                    
                    <p>¡Gracias por tu compra! Aquí están tus credenciales de acceso:</p>
                    
                    <div class='credentials'>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Contraseña:</strong> $password</p>
                        <p><strong>Plan:</strong> $planName</p>
                        <p><strong>Total Pagado:</strong> R\$ " . number_format($totalPrice, 2, ',', '.') . "</p>
                    </div>
                    
                    <p>Para acceder al sistema, haz clic en el botón de abajo:</p>
                    
                    <center>
                        <a href='$siteUrl/login.php' class='button'>Ir al Login</a>
                    </center>
                    
                    <p><strong>Instrucciones:</strong></p>
                    <ol>
                        <li>Accede a $siteUrl/login.php</li>
                        <li>Ingresa tu email: <strong>$email</strong></li>
                        <li>Ingresa tu contraseña: <strong>$password</strong></li>
                        <li>¡Listo! Podrás acceder a todas tus materias</li>
                    </ol>
                    
                    <p><strong>Recomendaciones:</strong></p>
                    <ul>
                        <li>Guarda esta contraseña en un lugar seguro</li>
                        <li>Puedes cambiar tu contraseña en tu perfil después de acceder</li>
                        <li>Si tienes problemas, contacta a nuestro soporte</li>
                    </ul>
                </div>
                
                <div class='footer'>
                    <p>© " . date('Y') . " Banco de Choices. Todos los derechos reservados.</p>
                    <p>Este es un email automático, por favor no responder.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $mail->ErrorInfo);
        return false;
    }
}

// ============================================
// FUNCIÓN PARA OBTENER NOMBRE DEL PLAN
// ============================================

function getPlanName($planId)
{
    $plans = [
        'monthly' => 'Acceso 1 Mes',
        'semester' => 'Acceso 6 Meses',
        'annual' => 'Acceso 1 Año'
    ];

    return $plans[$planId] ?? 'Plan Desconocido';
}
?>

<!DOCTYPE html>
<html lang="es-AR">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= $paymentSuccess ? 'Pago Confirmado' : 'Error en el Pago' ?> | Banco de Choices</title>
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

                    <h1>¡Pago Confirmado!</h1>
                    <p class="subtitle">Tu compra ha sido procesada exitosamente</p>

                    <div class="credentials-box">
                        <div class="credential-item">
                            <span class="credential-label">Email:</span>
                            <span class="credential-value"><?= htmlspecialchars($userEmail) ?></span>
                        </div>
                        <div class="credential-item">
                            <span class="credential-label">Contraseña:</span>
                            <span class="credential-value" id="passwordValue"><?= htmlspecialchars($userPassword) ?></span>
                            <button class="copy-btn" onclick="copyToClipboard('<?= htmlspecialchars($userPassword) ?>')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    <div class="info-box">
                        <p><i class="bi bi-info-circle me-2"></i> <strong>Próximos pasos:</strong></p>
                        <p>1. Revisa tu email (incluido spam) para confirmar tu compra</p>
                        <p>2. Accede a tu cuenta con los datos arriba</p>
                        <p>3. Comienza a estudiar tus materias</p>
                    </div>

                    <a href="<?= getenv('SITE_URL') ?: 'http://localhost:8000' ?>/login.php" class="btn-primary-custom">
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