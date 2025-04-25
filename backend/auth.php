<?php
/**
 * Authentication Management File
 * 
 * This file contains functions for user authentication including login, logout,
 * and session management functionality for the SportSync application.
 * 
 */

if (!function_exists('loginUser')) {
    /**
     * Authenticates a user by email and password
     * 
     * Supports both password_hash and legacy MD5 hashed passwords,
     * and automatically upgrades MD5 hashes to secure password_hash.
     * 
     * @param string $email User's email address
     * @param string $password User's password
     * @return array Success status and optional error message
     */
    function loginUser($email, $password) {
        try {
            error_log("Attempting login for email: " . $email);
            
            $sql = "SELECT * FROM users WHERE email = :email";
            $params = [':email' => $email];
            
            error_log("Executing query with params: " . json_encode($params));
            $users = executeQuery($sql, $params);
            error_log("Query result: " . json_encode($users));
            
            if (!empty($users)) {
                $user = $users[0];
                error_log("User found: " . json_encode($user));
                
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
                    error_log("Updated password hash for user: " . $email);
                }
                
                if ($passwordVerified) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['account_type'] = $user['account_type'] ?? 'user';
                    
                    error_log("Session variables set: " . json_encode($_SESSION));
                    return ['success' => true];
                } else {
                    error_log("Invalid password for user: " . $email);
                    return ['success' => false, 'message' => 'Invalid email or password'];
                }
            } else {
                error_log("No user found with email: " . $email);
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during login'];
        }
    }
}

if (!function_exists('logoutUser')) {
    /**
     * Logs out the current user by destroying their session
     * 
     * Clears session data, removes session cookies, and terminates the session.
     * 
     * @return array Success status
     */
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
    /**
     * Checks if a user is currently logged in
     * 
     * @return boolean True if user is logged in, false otherwise
     */
    function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}
?>