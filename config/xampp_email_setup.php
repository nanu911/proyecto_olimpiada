<?php
// Configuración opcional para habilitar emails reales en XAMPP
// Este archivo es solo informativo - no se ejecuta automáticamente


1. OPCIÓN 1 - Usar Gmail SMTP (Recomendado):
   - Instalar PHPMailer: composer require phpmailer/phpmailer
   - Configurar en un archivo separado con tus credenciales de Gmail
   
2. OPCIÓN 2 - Configurar sendmail en XAMPP:
   - Editar php.ini en XAMPP:
     [mail function]
     SMTP = smtp.gmail.com
     smtp_port = 587
     sendmail_from = tu-email@gmail.com
     sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
   
   - Editar sendmail.ini en XAMPP:
     [sendmail]
     smtp_server=smtp.gmail.com
     smtp_port=587
     auth_username=tu-email@gmail.com
     auth_password=tu-contraseña-de-aplicacion
     force_sender=tu-email@gmail.com

3. OPCIÓN 3 - Usar un servicio como Mailtrap para testing:
   - Registrarse en mailtrap.io
   - Usar sus credenciales SMTP para testing

NOTA: Para Gmail necesitas generar una "contraseña de aplicación" 
en tu cuenta de Google, no usar tu contraseña normal.

// Ejemplo de configuración con PHPMailer (si lo instalas):
/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function enviarEmailReal($to, $subject, $body) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tu-email@gmail.com';
        $mail->Password   = 'tu-contraseña-de-aplicacion';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        $mail->setFrom('noreply@quantumtour.com', 'Quantum Tour');
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando email: {$mail->ErrorInfo}");
        return false;
    }
}
?>
