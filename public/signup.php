// filepath: /Applications/XAMPP/xamppfiles/htdocs/sportsync/public/signup.php
/**
 * Signup Page
 *
 * This page handles new user registration for the SportSync platform.
 * Features include:
 * - User registration with username, email, and password
 * - Form validation and error handling
 * - Password strength requirements
 * - Email format validation
 * - Session-based error messaging
 * - Redirection to login page after successful registration
 * - Prevention of duplicate email registrations
 */

<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = 'signup';
include __DIR__ . '/header.php';

// No session_start() here, handled by index.php
include __DIR__ . '/../backend/db.php';
include __DIR__ . '/../backend/user.php';

// Clear any old error from session on fresh load
if (!isset($_POST['username']) && !isset($_POST['email']) && !isset($_POST['password'])) {
    unset($_SESSION['signup_error']);
}

$error = null;
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_submit']) && $_POST['form_submit'] == 'signup') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Attempt to register the user
        $result = registerUser($username, $email, $password);
        
        if ($result === true) {
            $success = true;
            // Redirect to login page after successful registration
            header("Location: ?page=login&registered=1");
            exit();
        } else {
            $error = "Registration failed! Email may already exist.";
        }
    }
    
    // If there was an error, store it in session
    if ($error) {
        $_SESSION['signup_error'] = $error;
    }
}

// Get error from session if it exists
if (isset($_SESSION['signup_error'])) {
    $error = $_SESSION['signup_error'];
    unset($_SESSION['signup_error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportSync - Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-black min-h-screen flex flex-col">
    <div class="flex-grow flex items-center justify-center min-h-screen pt-16">
        <div class="bg-gray-900 p-8 rounded-lg shadow-lg w-full max-w-md mx-4">
            <h2 class="text-2xl font-bold text-white text-center mb-6">Sign Up</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-500 text-white p-3 rounded-lg mb-4 text-center">
                    Registration successful! You can now login.
                </div>
            <?php endif; ?>
            
            <form method="POST" action="?page=signup" class="space-y-4">
                <input type="hidden" name="form_submit" value="signup">
                <div>
                    <input type="text" name="username" placeholder="Username" class="w-full p-2 rounded border border-gray-700 bg-gray-800 text-white" required>
                </div>
                <div>
                    <input type="email" name="email" placeholder="Email" class="w-full p-2 rounded border border-gray-700 bg-gray-800 text-white" required>
                </div>
                <div>
                    <input type="password" name="password" placeholder="Password" class="w-full p-2 rounded border border-gray-700 bg-gray-800 text-white" required>
                </div>
                <button type="submit" class="w-full py-2 bg-red-500 text-white rounded hover:bg-red-600">Sign Up</button>
            </form>
            <p class="text-center text-gray-400 mt-4">Already have an account? <a href="?page=login" class="text-red-500 hover:underline">Login</a></p>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>