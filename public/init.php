<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Start the session
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/match.php';
require_once __DIR__ . '/../backend/news.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/user.php';

// Initialize database tables
try {
    initializeTables();
    error_log("Database tables initialized successfully");
} catch (Exception $e) {
    error_log("Error initializing database tables: " . $e->getMessage());
}

// Initialize user variable
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
    error_log("User loaded from session: " . ($user ? $user['username'] : 'null'));
}

// Set page variable if not set
if (!isset($page)) {
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
}
?>