<?php
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/email.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug information
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST data received: " . print_r($_POST, true));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
    try {
        // Validate and sanitize input
        $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $subject = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
        $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);

        // Debug log
        error_log("Processed input - Name: $name, Email: $email, Subject: $subject");

        // Validate inputs
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
            // Debug log
            error_log("Feedback inserted successfully. Redirecting to thank you page.");
            
            // Redirect to thank you page
            header("Location: index.php?page=thank-you");
            exit();
        } else {
            throw new Exception("Failed to insert feedback into database.");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log("Feedback submission error: " . $e->getMessage());
    }
}

// Set the page variable for the header
$page = 'feedback';

// Include the header
include __DIR__ . '/header.php';
?>

<main class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-8 text-center">Send us your Feedback</h1>
        
        <div id="success-message" class="hidden bg-green-500 text-white p-4 rounded-lg mb-6"></div>
        <div id="error-message" class="hidden bg-red-600 text-white p-4 rounded-lg mb-6"></div>

        <div class="bg-gray-900 p-8 rounded-lg shadow-lg">
            <form id="feedbackForm" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                    <input type="text" id="name" name="name" 
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white"
                           required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" 
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white"
                           required>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-300 mb-2">Subject</label>
                    <input type="text" id="subject" name="subject" 
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white"
                           required>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-300 mb-2">Message</label>
                    <textarea id="message" name="message" rows="4" 
                              class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-white"
                              required></textarea>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors">
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const successDiv = document.getElementById('success-message');
    const errorDiv = document.getElementById('error-message');
    
    // Hide any previous messages
    successDiv.classList.add('hidden');
    errorDiv.classList.add('hidden');
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Sending...';
    submitButton.disabled = true;
    
    fetch('../backend/feedback.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            successDiv.textContent = data.message;
            successDiv.classList.remove('hidden');
            this.reset();
        } else {
            errorDiv.textContent = data.message;
            errorDiv.classList.remove('hidden');
        }
    })
    .catch(error => {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    })
    .finally(() => {
        // Reset button state
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    });
});
</script>

<?php include __DIR__ . '/footer.php'; ?> 