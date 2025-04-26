<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Include initialization file
require_once __DIR__ . '/init.php';

// Check if page requires authentication
if (!isset($_SESSION['user_id']) && ($page === 'dashboard' || $page === 'admin')) {
    header("Location: ?page=login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportSync - <?php echo ucfirst($page); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-black min-h-screen flex flex-col">
    <!-- Add a spacer div for the fixed navbar -->
    <div class="h-16"></div>
    
    <!-- Main Navigation -->
    <nav class="fixed top-0 left-0 w-full bg-black border-b border-gray-800 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="?page=home" class="text-2xl font-bold text-red-600">SportSync</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="?page=home" class="text-gray-300 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium <?php echo $page === 'home' ? 'text-red-600' : ''; ?>">Home</a>
                        <a href="?page=football" class="text-gray-300 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium <?php echo $page === 'football' ? 'text-red-600' : ''; ?>">Football</a>
                        <a href="?page=cricket" class="text-gray-300 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium <?php echo $page === 'cricket' ? 'text-red-600' : ''; ?>">Cricket</a>
                        <a href="?page=live-scores" class="text-gray-300 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium <?php echo $page === 'live-scores' ? 'text-red-600' : ''; ?>">Live Scores</a>
                    </div>
                </div>
                <div class="relative flex space-x-3">
                    <?php if ($isLoggedIn): ?>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-white hover:text-red-500 focus:outline-none">
                                <span class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                </span>
                                <span class="hidden md:inline"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 ease-in-out">
                                <div class="py-1">
                                    <a href="?page=dashboard" class="block px-4 py-2 text-white hover:bg-gray-700 transition-colors">Dashboard</a>
                                    <a href="?page=favorite-matches" class="block px-4 py-2 text-white hover:bg-gray-700 transition-colors">Favorite Matches</a>
                                    <?php if (isset($_SESSION['account_type']) && $_SESSION['account_type'] === 'admin'): ?>
                                        <a href="?page=admin" class="block px-4 py-2 text-white hover:bg-gray-700 transition-colors">Admin Panel</a>
                                    <?php endif; ?>
                                    <a href="?page=logout" class="block px-4 py-2 text-white hover:bg-gray-700 transition-colors">Logout</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="?page=login" class="px-4 py-1 border border-gray-700 rounded text-white hover:bg-gray-800">Login</a>
                        <a href="?page=signup" class="px-4 py-1 bg-red-500 text-white rounded hover:bg-red-600">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <style>
    /* Navbar dropdown styles */
    .group:hover .group-hover\:opacity-100 {
        opacity: 1;
    }
    .group:hover .group-hover\:visible {
        visibility: visible;
    }
    .group .absolute {
        transform: translateY(10px);
        transition: all 0.3s ease-in-out;
    }
    .group:hover .absolute {
        transform: translateY(0);
    }

    /* Mobile menu styles */
    @media (max-width: 640px) {
        .sm\:hidden {
            display: none;
        }
    }
    </style>

    <main class="flex-grow">

</main>
</body>
</html>
