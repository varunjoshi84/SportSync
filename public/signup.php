<?php
// Include initialization file
require_once __DIR__ . '/init.php';

$page = 'signup';
include __DIR__ . '/header.php';

// No session_start() here, handled by index.php
include __DIR__ . '/../backend/db.php';
include __DIR__ . '/../backend/user.php';

// Clear any old error from session on fresh load
if (!isset($_POST['username']) && !isset($_POST['email']) && !isset($_POST['password'])) {
    unset($_SESSION['signup_error']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user = registerUser($username, $email, $password);
    if ($user !== false) {
        header("Location: ?page=login");
        exit();
    } else {
        $_SESSION['signup_error'] = "Registration failed! Email may already exist.";
        header("Location: ?page=signup"); // Reload page with error
        exit();
    }
}
$error = $_SESSION['signup_error'] ?? null;
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
            <?php if ($error) echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>
            <form method="POST" class="space-y-4">
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
<?php
// Clear error after displaying
if (isset($_SESSION['signup_error'])) {
    unset($_SESSION['signup_error']);
}
?>