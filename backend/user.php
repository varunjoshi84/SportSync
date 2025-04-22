<?php
if (!function_exists('getUserById')) {
    function getUserById($id) {
        $db = getDB();
        $sql = "SELECT * FROM users WHERE id = :id";
        $params = [':id' => $id];
        $users = executeQuery($sql, $params);
        return count($users) > 0 ? $users[0] : null;
    }
}

if (!function_exists('registerUser')) {
    function registerUser($username, $email, $password) {
        try {
            $db = getDB();
            
            // Check if email already exists
            $sql = "SELECT * FROM users WHERE email = :email";
            $params = [':email' => $email];
            $existingUsers = executeQuery($sql, $params);
            
            if (count($existingUsers) > 0) {
                return false; // Email already exists
            }
            
            // Insert new user
            $sql = "INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())";
            $params = [
                ':username' => $username,
                ':email' => $email,
                ':password' => md5($password) // Note: Use proper password hashing in production
            ];
            
            executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
    }
}
?>