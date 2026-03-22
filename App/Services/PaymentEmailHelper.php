<?php

/**
 * E-mail de credenciais pós-compra (PHPMailer), alinhado ao fluxo existente.
 */
class PaymentEmailHelper
{
    public static function generateRandomPassword(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*';

        $allChars = $uppercase . $lowercase . $numbers . $special;
        $password = '';

        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($password);
    }

    public static function sendConfirmationEmail(string $email, string $name, string $password, float $totalPrice, string $planId): bool
    {
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer();

        try {
            $mail->isSMTP();
            $mail->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
            $mail->Port = (int) (getenv('MAIL_PORT') ?: 587);
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = getenv('MAIL_ENCRYPTION') ?: 'tls';
            $mail->Username = getenv('MAIL_USERNAME');
            $mail->Password = getenv('MAIL_PASSWORD');

            $mail->setFrom(getenv('MAIL_FROM'), getenv('MAIL_FROM_NAME'));
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $mail->Subject = '¡Bienvenido a Banco de Choices! Tus credenciales de acceso';

            $siteUrl = getenv('SITE_URL') ?: 'http://localhost:8000';
            $planName = self::getPlanName($planId);

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
                </div>
                <div class='footer'>
                    <p>© " . date('Y') . " Banco de Choices.</p>
                </div>
            </div>
        </body>
        </html>
        ";

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('PaymentEmailHelper: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendAccessGrantedExistingUser(string $email, string $name, string $planId): bool
    {
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer();

        try {
            $mail->isSMTP();
            $mail->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
            $mail->Port = (int) (getenv('MAIL_PORT') ?: 587);
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = getenv('MAIL_ENCRYPTION') ?: 'tls';
            $mail->Username = getenv('MAIL_USERNAME');
            $mail->Password = getenv('MAIL_PASSWORD');

            $mail->setFrom(getenv('MAIL_FROM'), getenv('MAIL_FROM_NAME'));
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $siteUrl = getenv('SITE_URL') ?: 'http://localhost:8000';
            $planName = self::getPlanName($planId);

            $mail->Subject = 'Banco de Choices — Acceso a materias actualizado';
            $mail->Body = "<p>Hola <strong>" . htmlspecialchars($name) . "</strong>,</p>"
                . "<p>Se liberó el acceso a nuevas materías en tu plan: <strong>" . htmlspecialchars($planName) . "</strong>.</p>"
                . "<p><a href=\"" . htmlspecialchars($siteUrl) . "/login.php\">Iniciar sesión</a></p>";

            $mail->send();
            return true;
        } catch (Throwable $e) {
            $path = dirname(__DIR__, 2) . '/logs/mp_payment.log';
            @file_put_contents($path, '[' . date('c') . '] [email_erro] ' . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
            return false;
        }
    }

    private static function getPlanName(string $planId): string
    {
        $plans = [
            'monthly' => 'Acceso 1 Mes',
            'semester' => 'Acceso 6 Meses',
            'annual' => 'Acceso 1 Año'
        ];

        return $plans[$planId] ?? 'Plan';
    }
}
