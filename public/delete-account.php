<?php
/**
 * Delete Account Page
 * 
 * This file handles the deletion of user accounts.
 * Requires password confirmation for security.
 */

// Start session for user authentication
session_start();

// Include required backend files
include_once __DIR__ . '/../backend/db.php';
include_once __DIR__ . '/../backend/auth.php';
include_once __DIR__ . '/../backend/user.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ?page=login");
    exit();
}

$user = getUserById($_SESSION['user_id']);
$error = null;
$confirmation = false;

// Handle account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['confirm_delete'])) {
        $password = $_POST['password'] ?? '';
        
        // Use the deleteUser function from backend instead of direct DB operations
        $deleted = deleteUser($_SESSION['user_id'], $password);
        
        if ($deleted) {
            // Clear session and redirect to home page
            session_destroy();
            header("Location: ?page=home&deleted=true");
            exit();
        } else {
            $error = "Failed to delete account. Please check your password and try again.";
        }
    } else {
        // First step - show confirmation
        $confirmation = true;
    }
}

include __DIR__ . '/header.php';
?>

<div class="max-w-2xl mx-auto p-6">
    <div class="bg-gray-900 rounded-lg p-6 shadow-lg border border-gray-800">
        <h2 class="text-2xl font-semibold text-white mb-4">Delete Account</h2>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-900/50 border border-red-700 text-red-100 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($confirmation): ?>
            <div class="bg-red-900/50 border border-red-700 text-red-100 px-4 py-3 rounded mb-4">
                <p class="font-bold">Warning: This action cannot be undone.</p>
                <p>All your data, including favorite teams, matches and preferences will be permanently deleted.</p>
            </div>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-400">Enter your password to confirm deletion</label>
                    <input type="password" name="password" class="w-full p-2 bg-gray-700 border border-gray-600 rounded text-white mt-1" required>
                </div>
                
                <div class="flex space-x-4">
                    <button type="submit" name="confirm_delete" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Permanently Delete My Account
                    </button>
                    <a href="?page=dashboard" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        <?php else: ?>
            <p class="text-gray-400 mb-6">
                Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently removed.
            </p>
            
            <form method="POST" class="space-y-4">
                <div class="flex space-x-4">
                    <button type="submit" name="delete_account" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Delete My Account
                    </button>
                    <a href="?page=dashboard" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>