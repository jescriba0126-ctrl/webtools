<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust paths depending on where you dropped the PHPMailer folder
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function createMailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    
    // Configure with your Google App Passwords details
    $mail->Username   = 'cubiertos15@gmail.com'; 
    $mail->Password   = 'tleqzyehqnjqbhmz   '; 
    
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    
    $mail->setFrom('cubiertos15@gmail.com', 'Cubiertos Food Hub');
    $mail->CharSet    = 'UTF-8';
    return $mail;
}

function sendVerificationEmail($toEmail, $toName, $token) {
    $mail = createMailer();
    try {
        $mail->addAddress($toEmail, $toName);

        // This URL points back to your local development environment link handler
        $verifyUrl = "http://localhost/webtools-main/PHP/verify.php?token=" . urlencode($token);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your Cubiertos Account';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                <h2 style='color: #606c38;'>Welcome to Cubiertos Food Hub!</h2>
                <p>Hello " . htmlspecialchars($toName) . ",</p>
                <p>Thank you for signing up. Please verify your email address to unlock your account features and order custom catering selections:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='" . $verifyUrl . "' style='background-color: #606c38; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify My Account</a>
                </p>
                <p style='font-size: 0.85rem; color: #7a7a62;'>If you did not request this account registration, you can securely ignore this message.</p>
            </div>";

        $mail->send();
    } catch (Exception $e) {
        // Fallback error messaging dashboard link redirect if dispatch fails
        header("Location: ../HTML/login.html?panel=register&error=" . urlencode("Mailer Error: " . $mail->ErrorInfo));
        exit();
    }
}