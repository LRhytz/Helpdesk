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
    // Enable SMTP debugging (for testing only - set to 0 for production)
    $mail->SMTPDebug = 2;

    // Set PHPMailer to use SMTP
    $mail->isSMTP();

    // ==== Gmail SMTP Settings ====
    $mail->Host       = 'smtp.gmail.com';             // Gmail SMTP server
    $mail->SMTPAuth   = true;                         // Enable SMTP authentication
    $mail->Username   = 'smjbolotaulo@gmail.com';     // Replace with your Gmail address
    $mail->Password   = 'dpehuhlskyolzseu';           // Replace with your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
    $mail->Port       = 587;                          // TCP port for TLS

    // Set sender and recipient information
    $mail->setFrom('smjbolotaulo@gmail.com', 'Help Desk');
    $mail->addAddress('smjbolotaulo@gmail.com', 'Admin'); // Replace with the recipient's details (admin email)

    // Set email format to HTML and add content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email via Gmail SMTP';
    $mail->Body    = 'This is a test email sent using PHPMailer with Gmail SMTP.';

    // Send the email
    $mail->send();
    echo 'Message has been sent successfully.';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
}
?>
