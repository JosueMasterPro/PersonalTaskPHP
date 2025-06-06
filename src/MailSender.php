<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

class MailSender {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = getenv('SMTP_HOST');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = getenv('SMTP_USER');
            $this->mailer->Password = getenv('SMTP_PASS') ;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = getenv('SMTP_PORT');

            $this->mailer->setFrom(getenv('SMTP_FROM'), getenv('SMTP_FROM_NAME'));
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

            $urlVerificacion = getenv('APP_URL') . "/verificar?token=" . urlencode($token);

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
?>