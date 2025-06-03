<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';  // Ajusta la ruta según tu estructura

class MailSender {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        try {
            // Configuración SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.tu-servidor.com'; // Cambia al SMTP de tu proveedor (Gmail, Outlook, etc)
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'tu-correo@example.com'; // Tu email SMTP
            $this->mailer->Password = 'tu-password'; // Tu password SMTP o app password si usas Gmail
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // o PHPMailer::ENCRYPTION_SMTPS
            $this->mailer->Port = 587; // Cambia el puerto si es necesario

            $this->mailer->setFrom('no-reply@tudominio.com', 'Tu Nombre o App');
            $this->mailer->isHTML(true);
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            throw new Exception("Error al configurar el correo");
        }
    }

    public function enviarVerificacion($email, $nombre, $token) {
        try {
            $this->mailer->addAddress($email, $nombre);
            $this->mailer->Subject = 'Confirma tu cuenta';

            // URL de verificación (cambia tu dominio)
            $urlVerificacion = "https://tudominio.com/verificar?token=" . urlencode($token);

            $this->mailer->Body = "
                <h1>Confirma tu cuenta</h1>
                <p>Hola {$nombre}, gracias por registrarte. Por favor confirma tu cuenta haciendo clic en el siguiente enlace:</p>
                <a href='{$urlVerificacion}'>Confirmar cuenta</a>
                <p>Si no solicitaste esta cuenta, ignora este mensaje.</p>
            ";

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando correo: " . $e->getMessage());
            return false;
        }
    }
}
