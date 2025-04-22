<?php
if (!function_exists('loginUser')) {
    function loginUser($email, $password) {
        try {
            error_log("Attempting login for email: " . $email);
            
            $sql = "SELECT * FROM users WHERE email = :email AND password = :password";
            $params = [
                ':email' => $email,
                ':password' => md5($password) // Note: Using MD5 for demo, should use better hashing in production
            ];
            
            error_log("Executing query with params: " . json_encode($params));
            $users = executeQuery($sql, $params);
            error_log("Query result: " . json_encode($users));
            
            if (!empty($users)) {
                $user = $users[0];
                error_log("User found: " . json_encode($user));
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                error_log("Session variables set: " . json_encode($_SESSION));
                return ['success' => true];
            } else {
                error_log("No user found with provided credentials");
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }
}

if (!function_exists('logoutUser')) {
    function logoutUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        return ['success' => true];
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}
?>