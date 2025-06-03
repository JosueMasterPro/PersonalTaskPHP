<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'SO2UNAHVS@gmail.com';       // Tu correo Gmail
    $mail->Password = '30@Reyes';                   // Tu app password de Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('SO2UNAHVS@gmail.com', 'Josue Reyes');
    $mail->addAddress('josuereyes1229@example.com', 'ProMaster');

    $mail->isHTML(true);
    $mail->Subject = 'Prueba de correo PHPMailer';
    $mail->Body = '<h1>Hola</h1><p>Este es un correo de prueba.</p>';

    $mail->send();
    echo "Correo enviado exitosamente.";
} catch (Exception $e) {
    echo "Error al enviar correo: {$mail->ErrorInfo}";
}
?>