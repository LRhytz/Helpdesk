<?php
// sendmail.php
// This file sends an email using PHPMailer with Gmail SMTP settings.

require 'libs/PHPMailer.php';
require 'libs/SMTP.php';
require 'libs/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 2;

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->SMTPAuth   = true; 
    $mail->Username   = 'smjbolotaulo@gmail.com'; 
    $mail->Password   = 'dpehuhlskyolzseu'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587;

    $mail->setFrom('smjbolotaulo@gmail.com', 'Help Desk');
    $mail->addAddress('smjbolotaulo@gmail.com', 'Admin');

    $mail->isHTML(true);
    $mail->Subject = 'Test Email via Gmail SMTP';
    $mail->Body    = 'This is a test email sent using PHPMailer with Gmail SMTP.';

    $mail->send();
    echo 'Message has been sent successfully.';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
}
?>
