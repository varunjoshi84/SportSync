// filepath: /Applications/XAMPP/xamppfiles/htdocs/sportsync/public/login.php
/**
 * Login Page
 *
 * This page handles user authentication to the SportSync platform.
 * Features include:
 * - User login with email and password
 * - Form validation and error handling
 * - Success message display after registration
 * - Session management
 * - Redirection to home page after successful login
 */

<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = 'login';
include __DIR__ . '/header.php';

$error = null;
$success = null;

// Check if user just registered
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success = "Registration successful! Please login with your credentials.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_submit']) && $_POST['form_submit'] == 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            header("Location: ?page=home");
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="flex-grow flex items-center justify-center min-h-screen pt-16">
    <div class="bg-gray-900 p-8 rounded-lg shadow-lg w-full max-w-md mx-4">
        <h2 class="text-2xl font-bold text-white text-center mb-6">Login</h2>
        
        <?php if ($success): ?>
            <div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="?page=login" class="space-y-4">
            <input type="hidden" name="form_submit" value="login">
            <div>
                <input type="email" name="email" placeholder="Email" class="w-full p-2 rounded border border-gray-700 bg-gray-800 text-white" required>
            </div>
            <div>
                <input type="password" name="password" placeholder="Password" class="w-full p-2 rounded border border-gray-700 bg-gray-800 text-white" required>
            </div>
            <button type="submit" class="w-full py-2 bg-red-500 text-white rounded hover:bg-red-600">Login</button>
        </form>
        <p class="text-center text-gray-400 mt-4">Don't have an account? <a href="?page=signup" class="text-red-500 hover:underline">Sign Up</a></p>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>