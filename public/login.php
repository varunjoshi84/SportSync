<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session state
error_log("Login page - Session status: " . session_status());
error_log("Login page - Session data: " . json_encode($_SESSION));

$page = 'login';
include __DIR__ . '/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    error_log("Login attempt - Email: " . $email);
    
    $result = loginUser($email, $password);
    error_log("Login result: " . json_encode($result));
    
    if ($result['success']) {
        error_log("Login successful - Session data: " . json_encode($_SESSION));
        error_log("Login successful - Redirecting to home");
        header("Location: ?page=home");
        exit();
    } else {
        error_log("Login failed - Error: " . $result['message']);
        $error = $result['message'];
    }
}

// Debug session after potential login
error_log("Login page - Final session status: " . session_status());
error_log("Login page - Final session data: " . json_encode($_SESSION));
?>

<div class="flex-grow flex items-center justify-center min-h-screen pt-16">
    <div class="bg-gray-900 p-8 rounded-lg shadow-lg w-full max-w-md mx-4">
        <h2 class="text-2xl font-bold text-white text-center mb-6">Login</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-500 text-white p-3 rounded-lg mb-4 text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
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