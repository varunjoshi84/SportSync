<?php

require_once __DIR__ . '/init.php';

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['subscribe'])) {
        $email = $_POST['subscribe_email'];
        if (subscribeNewsletter($email)) {
            $message = "Subscribed successfully!";
        } else {
            $error = "Email already subscribed or invalid.";
        }
    }
}

// Set page variable
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Set a global flag to indicate we're including from index.php
$GLOBALS['indexPhpInclude'] = true;

// Include the page content
switch($page) {
    case 'login':
        require __DIR__ . '/login.php';
        break;
    case 'signup':
        require __DIR__ . '/signup.php';
        break;
    case 'admin':
        if (!$user || $user['account_type'] !== 'admin') {
            header("Location: ?page=login");
            exit;
        }
        require __DIR__ . '/admin.php';
        break;
    case 'cricket':
        require __DIR__ . '/cricket.php';
        break;
    case 'football':
        require __DIR__ . '/football.php';
        break;
    case 'live-scores':
        require __DIR__ . '/live-scores.php';
        break;
    case 'feedback':
        include __DIR__ . '/feedback.php';
        break;
    case 'thank-you':
        require __DIR__ . '/thank-you.php';
        break;
    case 'dashboard':
        if (!$user) {
            header("Location: ?page=login");
            exit;
        }
        require __DIR__ . '/dashboard.php';
        break;
    case 'delete-account':
        if (!$user) {
            header("Location: ?page=login");
            exit;
        }
        require __DIR__ . '/delete-account.php';
        break;
    case 'favorite-matches':
        if (!$user) {
            header("Location: ?page=login");
            exit;
        }
        require __DIR__ . '/favorite-matches.php';
        break;
    case 'logout':
        require __DIR__ . '/logout.php';
        break;
    default:
        require __DIR__ . '/home.php';
        break;
}
?>
