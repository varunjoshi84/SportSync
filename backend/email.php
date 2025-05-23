<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Define a constant to prevent multiple inclusions
if (!defined('EMAIL_PHP_INCLUDED')) {
    define('EMAIL_PHP_INCLUDED', true);
}

// Email sending functions - moved outside conditional block
function sendEmail($to, $subject, $body) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USERNAME'] ?? 'joshivarun266@gmail.com'; 
        $mail->Password = $_ENV['SMTP_PASSWORD'] ?? 'cvdy gcfj deal qwps'; // App password, not regular password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        // Disable debugging in production
        $mail->SMTPDebug = 0;

        // Recipients
        $mail->setFrom($mail->Username, 'SportSync');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags(str_replace('<br>', "\n", $body));

        $result = $mail->send();
        return $result;
    } catch (Exception $e) {
        // Try alternative method
        try {
            // Fall back to PHP mail() function if SMTP fails
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: SportSync <noreply@sportsync.com>\r\n";
            
            $altResult = mail($to, $subject, $body, $headers);
            return $altResult;
        } catch (Exception $mailErr) {
            return false;
        }
    }
}

function sendFeedbackEmail($name, $email, $subject, $message) {
    try {
        $emailSubject = "Thank you for your feedback - SportSync";
        $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #dc2626;'>Thank you for your valuable feedback!</h2>
                <p>Dear {$name},</p>
                <p>We have received your feedback regarding <strong>{$subject}</strong> and appreciate you taking the time to share your thoughts with us.</p>
                <p>Your message:</p>
                <div style='background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    {$message}
                </div>
                <p>We will review your feedback and get back to you if necessary.</p>
                <p>Best regards,<br>The SportSync Team</p>
            </div>
        ";
        return sendEmail($email, $emailSubject, $body);
    } catch (Exception $e) {
        // Return true to allow the process to continue even if email fails
        // We still want to save the feedback in the database
        return true;
    }
}

function sendSubscriptionEmail($email) {
    try {
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
    } catch (Exception $e) {
        return false;
    }
}
?>