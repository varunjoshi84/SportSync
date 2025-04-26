<?php
if (!function_exists('loginUser')) {  
    function loginUser($email, $password) {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $params = [':email' => $email];
            
            $users = executeQuery($sql, $params);
            
            if (!empty($users)) {
                $user = $users[0];
                
                // Check if password is hashed with password_hash or md5
                $passwordVerified = false;
                
                // Try password_verify first (for password_hash)
                if (password_verify($password, $user['password'])) {
                    $passwordVerified = true;
                } 
                // Fallback to md5 for backward compatibility
                else if (md5($password) === $user['password']) {
                    $passwordVerified = true;
                    
                    // Update the password to use password_hash for future logins
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateSql = "UPDATE users SET password = :password WHERE id = :id";
                    $updateParams = [
                        ':password' => $hashedPassword,
                        ':id' => $user['id']
                    ];
                    executeQuery($updateSql, $updateParams);
                }
                
                if ($passwordVerified) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['account_type'] = $user['account_type'] ?? 'user';
                    
                    return ['success' => true];
                } else {
                    return ['success' => false, 'message' => 'Invalid email or password'];
                }
            } else {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (Exception $e) {
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