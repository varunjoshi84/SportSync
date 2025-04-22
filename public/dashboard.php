<?php
session_start();
include_once __DIR__ . '/../backend/db.php';
include_once __DIR__ . '/../backend/auth.php';
include_once __DIR__ . '/../backend/user.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}
$user = getUserById($_SESSION['user_id']);

// Handle profile update (basic implementation)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $favorite_sport = $_POST['favorite_sport'];
    $favorite_team = $_POST['favorite_team'];
    // Add update logic here (e.g., update database)
    $message = "Profile updated successfully!";
}

// Handle password change (basic implementation)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_new_password'];
    // Add password change logic here (e.g., verify current password and update)
    if ($new_password === $confirm_password) {
        $message = "Password changed successfully!";
    } else {
        $error = "Passwords do not match!";
    }
}
?>

<?php include __DIR__ . '/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportSync - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-black text-white">
    <div class="max-w-4xl mx-auto mt-20 p-6">
        <div class="bg-gray-900 rounded-lg p-6 shadow-lg border border-gray-800">
            <h2 class="text-2xl font-semibold text-white mb-4">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
            <p class="text-gray-400 mb-6">Here's what's happening in your sports world.</p>
            <div class="flex space-x-6">
                <div class="w-1/3 bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <button class="focus:outline-none">
                            <span class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?>
                            </span>
                        </button>
                        <div>
                            <p class="text-white"><?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                    <div class="space-y-2 text-gray-400">
                        <p>Favorite Team: <?php echo htmlspecialchars($user['favorite_team'] ?? 'Not set'); ?></p>
                        <p>Favorite Sport: <?php echo htmlspecialchars($user['favorite_sport'] ?? 'Not set'); ?></p>
                        <p>Account Type: <?php echo htmlspecialchars($user['account_type'] ?? 'User'); ?></p>
                    </div>
                    <div class="flex justify-between mt-4">
                        <a href="?page=favorites" class="text-gray-400 hover:text-red-500"><i data-feather="heart"></i> Favorites</a>
                        <a href="?page=notifications" class="text-gray-400 hover:text-red-500"><i data-feather="bell"></i> Notifications</a>
                        <a href="?page=delete-account" class="text-red-500 hover:text-red-600"><i data-feather="trash-2"></i> Delete Account</a>
                    </div>
                </div>
                <div class="w-2/3 space-y-6">
                    <div class="bg-gray-800 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-4">Update Profile</h3>
                        <?php if (isset($message)) echo "<p class='text-green-500 mb-2'>$message</p>"; ?>
                        <?php if (isset($error)) echo "<p class='text-red-500 mb-2'>$error</p>"; ?>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-gray-400">Username</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" readonly>
                            </div>
                            <div>
                                <label class="block text-gray-400">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" readonly>
                            </div>
                            <div class="flex space-x-4">
                                <div class="w-1/2">
                                    <label class="block text-gray-400">Favorite Sport</label>
                                    <input type="text" name="favorite_sport" value="<?php echo htmlspecialchars($user['favorite_sport'] ?? ''); ?>" class="w-full p-2 bg-gray-700 border border-gray-600 rounded">
                                </div>
                                <div class="w-1/2">
                                    <label class="block text-gray-400">Favorite Team</label>
                                    <input type="text" name="favorite_team" value="<?php echo htmlspecialchars($user['favorite_team'] ?? ''); ?>" class="w-full p-2 bg-gray-700 border border-gray-600 rounded">
                                </div>
                            </div>
                            <button type="submit" name="update_profile" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Update Profile</button>
                        </form>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-4">Change Password</h3>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-gray-400">Current Password</label>
                                <input type="password" name="current_password" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                            </div>
                            <div class="flex space-x-4">
                                <div class="w-1/2">
                                    <label class="block text-gray-400">New Password</label>
                                    <input type="password" name="new_password" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                                </div>
                                <div class="w-1/2">
                                    <label class="block text-gray-400">Confirm New Password</label>
                                    <input type="password" name="confirm_new_password" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                                </div>
                            </div>
                            <button type="submit" name="change_password" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>
    <script>
        feather.replace();
    </script>
</body>
</html>