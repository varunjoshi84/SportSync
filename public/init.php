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

// Include required files
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/match.php';
require_once __DIR__ . '/../backend/news.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/user.php';

// Initialize database tables
try {
    initializeTables();
} catch (Exception $e) {
    // Silent failure in production
}

// Initialize user variable
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Set page variable if not set
if (!isset($page)) {
    $page = isset($_GET['page']) ? $_GET['page'] : 'home';
}
?>