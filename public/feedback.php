<?php
session_start();
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/email.php';
require_once __DIR__ . '/../backend/feedback.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$name = '';
$email = '';
$subject = '';
$message = '';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    error_log("Feedback form submitted - Name: $name, Email: $email, Subject: $subject");
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }

    // If validation passes, use the backend processFeedback function
    if (empty($errors)) {
        try {
            $result = processFeedback($name, $email, $subject, $message);
            
            if ($result['success']) {
                // Store success message in session
                $_SESSION['success_message'] = $result['message'];
                
                // Redirect to thank you page
                header("Location: ?page=thank-you");
                exit();
            } else {
                $errors[] = $result['message'] ?? "Failed to save your feedback. Please try again.";
                error_log("Feedback processing failed: " . ($result['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            error_log("General error in feedback processing: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            $errors[] = "An error occurred while processing your feedback. Please try again later.";
        }
    }
}

// Include header
include 'header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center">Send us your Feedback</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <strong class="font-bold">Please correct the following:</strong>
                <ul class="list-disc list-inside mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="bg-gray-900 p-6 rounded-lg shadow-lg">
            <form method="POST" action="?page=feedback" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                    <input type="text" id="name" name="name" 
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white"
                           value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white"
                           value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-300 mb-2">Subject</label>
                    <input type="text" id="subject" name="subject" 
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white"
                           value="<?php echo htmlspecialchars($subject); ?>" required>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-300 mb-2">Message</label>
                    <textarea id="message" name="message" rows="5" 
                              class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white"
                              required><?php echo htmlspecialchars($message); ?></textarea>
                </div>

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded transition-colors duration-200">
                    Submit Feedback
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>