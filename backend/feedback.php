<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/email.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate and sanitize input
    $name = filter_var(trim($_POST['name'] ?? ''), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST['subject'] ?? ''), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message'] ?? ''), FILTER_SANITIZE_STRING);

    // Debug log
    error_log("Processing feedback submission - Name: $name, Email: $email, Subject: $subject");

    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        throw new Exception("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }

    // Insert into database
    $sql = "INSERT INTO feedback (name, email, subject, message) VALUES (:name, :email, :subject, :message)";
    $params = [
        ':name' => $name,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message
    ];
    
    // Debug log
    error_log("Executing SQL: $sql with params: " . print_r($params, true));
    
    $result = executeQuery($sql, $params);
    
    if ($result) {
        // Send thank you email
        $emailSent = sendFeedbackEmail($name, $email, $message);
        error_log("Feedback email sending result: " . ($emailSent ? "Success" : "Failed"));

        echo json_encode([
            'success' => true,
            'message' => $emailSent 
                ? 'Thank you for your feedback! We have sent a confirmation email to your inbox.'
                : 'Thank you for your feedback! However, we could not send the confirmation email.',
            'redirect' => 'index.php?page=thank-you'
        ]);
    } else {
        throw new Exception("Failed to insert feedback into database.");
    }
} catch (Exception $e) {
    error_log("Feedback submission error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 