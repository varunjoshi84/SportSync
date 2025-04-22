<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Call the logout function
logoutUser();

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: ?page=home");
exit();
?> 