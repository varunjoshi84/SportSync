<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/email.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if it's a POST request
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }

    // Validate and sanitize email
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

    // Debug log
    error_log("Newsletter subscription attempt - Email: $email");

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }

    // Check if email already exists
    $checkSql = "SELECT * FROM subscriptions WHERE email = :email";
    $checkResult = executeQuery($checkSql, [':email' => $email]);

    if (!empty($checkResult)) {
        throw new Exception("This email is already subscribed to our newsletter.");
    }

    // Insert into database
    $sql = "INSERT INTO subscriptions (email) VALUES (:email)";
    $params = [':email' => $email];
    
    // Debug log
    error_log("Executing SQL: $sql with params: " . print_r($params, true));
    
    $result = executeQuery($sql, $params);
    
    if ($result) {
        // Send welcome email
        $emailSent = sendSubscriptionEmail($email);
        error_log("Welcome email sending result: " . ($emailSent ? "Success" : "Failed"));

        echo json_encode([
            'success' => true,
            'message' => $emailSent 
                ? 'Thank you for subscribing! Please check your email for confirmation.'
                : 'Thank you for subscribing! However, we could not send the confirmation email.'
        ]);
    } else {
        throw new Exception("Failed to save subscription.");
    }
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 