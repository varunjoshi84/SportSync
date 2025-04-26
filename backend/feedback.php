<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/email.php';

// Define this constant at the beginning so included files can check it
define('FEEDBACK_INCLUDED', true);

// Check if this is a direct API call or included from another file
$isApi = (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__));

if ($isApi) {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
}

/**
 * Process feedback submission
 * @param string $name The name of the person submitting feedback
 * @param string $email The email of the person submitting feedback
 * @param string $subject The subject of the feedback
 * @param string $message The feedback message
 * @return array Result array with success status and message
 */
function processFeedback($name, $email, $subject, $message) {
    try {
        // Validate required fields
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            throw new Exception("All fields are required.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        // Get database connection
        $db = getDB();
        
        // Ensure the feedback table exists with the correct structure
        ensureFeedbackTableExists($db);
        
        // Insert into database
        $sql = "INSERT INTO feedback (name, email, subject, message, status) VALUES (:name, :email, :subject, :message, :status)";
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':subject' => $subject,
            ':message' => $message,
            ':status' => 'pending'
        ];
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: Failed to prepare statement");
        }
        
        $result = $stmt->execute($params);
        
        if (!$result) {
            throw new Exception("Database error: Failed to save feedback");
        }
        
        // Send thank you email, but don't fail if email sending fails
        try {
            $emailSent = sendFeedbackEmail($name, $email, $subject, $message);
        } catch (Exception $emailErr) {
            $emailSent = false;
        }

        return [
            'success' => true,
            'message' => $emailSent 
                ? 'Thank you for your feedback! We have sent a confirmation email to your inbox.'
                : 'Thank you for your feedback! However, we could not send the confirmation email.',
            'redirect' => '?page=thank-you'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "An error occurred while processing your feedback. Please try again later."
        ];
    }
}

/**
 * Ensure the feedback table exists with proper structure
 * @param PDO $db Database connection
 */
function ensureFeedbackTableExists($db) {
    try {
        // Check if table exists
        $result = $db->query("SHOW TABLES LIKE 'feedback'");
        $tableExists = ($result->rowCount() > 0);
        
        if (!$tableExists) {
            createFeedbackTable();
        } else {
            // Check if table has the correct structure
            $result = $db->query("DESCRIBE feedback");
            $columns = $result->fetchAll(PDO::FETCH_COLUMN);
            
            // Check if 'subject' and 'status' columns exist
            if (!in_array('subject', $columns) || !in_array('status', $columns)) {
                // Add missing columns
                if (!in_array('subject', $columns)) {
                    $db->exec("ALTER TABLE feedback ADD COLUMN subject VARCHAR(200) NOT NULL DEFAULT 'General Feedback' AFTER email");
                }
                
                if (!in_array('status', $columns)) {
                    $db->exec("ALTER TABLE feedback ADD COLUMN status VARCHAR(20) DEFAULT 'pending' AFTER message");
                }
            }
        }
    } catch (Exception $e) {
        throw new Exception("Database structure error");
    }
}

// Handle API requests directly
if ($isApi && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var(trim($_POST['name'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST['subject'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $message = filter_var(trim($_POST['message'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    $result = processFeedback($name, $email, $subject, $message);
    
    if (!$result['success']) {
        http_response_code(400);
    }
    
    echo json_encode($result);
    exit;
}
?>