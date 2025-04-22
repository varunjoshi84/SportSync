<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Prevent multiple inclusions
if (!defined('EMAIL_PHP_INCLUDED')) {
    define('EMAIL_PHP_INCLUDED', true);

    function sendEmail($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'joshivarun266@gmail.com';
            $mail->Password = 'cvdy gcfj deal qwps';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('joshivarun266@gmail.com', 'SportSync');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: {$mail->ErrorInfo}");
            return false;
        }
    }

    function sendFeedbackEmail($name, $email, $message) {
        $subject = "Thank you for your feedback - SportSync";
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #dc2626;'>Thank you for your valuable feedback!</h2>
                <p>Dear {$name},</p>
                <p>We have received your feedback and appreciate you taking the time to share your thoughts with us.</p>
                <p>Your message:</p>
                <div style='background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    {$message}
                </div>
                <p>We will review your feedback and get back to you if necessary.</p>
                <p>Best regards,<br>The SportSync Team</p>
            </div>
        ";
        return sendEmail($email, $subject, $body);
    }

    function sendSubscriptionEmail($email) {
        $subject = "Welcome to SportSync Newsletter!";
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #dc2626;'>Thank you for subscribing!</h2>
                <p>Welcome to the SportSync newsletter!</p>
                <p>You have successfully subscribed to receive updates about:</p>
                <ul>
                    <li>Latest match scores and updates</li>
                    <li>Breaking sports news</li>
                    <li>Special events and tournaments</li>
                    <li>Exclusive content and analysis</li>
                </ul>
                <p>We're excited to keep you informed about all the latest happenings in the world of sports!</p>
                <p>Best regards,<br>The SportSync Team</p>
            </div>
        ";
        return sendEmail($email, $subject, $body);
    }
}
?> 