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
            
            // Hash password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $sql = "INSERT INTO users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())";
            $params = [
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword
            ];
            
            executeQuery($sql, $params);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('updateUserPreferences')) {
    function updateUserPreferences($user_id, $favorite_sport = null, $favorite_team = null) {
        try {
            // Build the SQL query based on provided parameters
            $sql = "UPDATE users SET ";
            $params = [':user_id' => $user_id];
            $updateFields = [];
            
            if ($favorite_sport !== null) {
                $updateFields[] = "favorite_sport = :favorite_sport";
                $params[':favorite_sport'] = $favorite_sport;
            }
            
            if ($favorite_team !== null) {
                $updateFields[] = "favorite_team = :favorite_team";
                $params[':favorite_team'] = $favorite_team;
            }
            
            if (empty($updateFields)) {
                return false; // Nothing to update
            }
            
            $sql .= implode(', ', $updateFields);
            $sql .= " WHERE id = :user_id";
            
            $result = executeQuery($sql, $params);
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('updateUserPassword')) {
    function updateUserPassword($user_id, $new_password_hash) {
        try {
            $db = getDB();
            $sql = "UPDATE users SET password = :password WHERE id = :user_id";
            $params = [
                ':user_id' => $user_id,
                ':password' => $new_password_hash
            ];
            
            $result = executeQuery($sql, $params);
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('deleteUser')) {
    function deleteUser($user_id, $password) {
        try {
            $db = getDB();
            
            // First verify the user's password
            $sql = "SELECT password FROM users WHERE id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return false; // User not found
            }
            
            $stored_hash = $result['password'];
            
            // Verify the provided password matches the stored hash
            $password_verified = password_verify($password, $stored_hash);
            
            if (!$password_verified) {
                return false; // Password verification failed
            }
            
            // Delete user account - using direct PDO execution for DELETE operations
            $sql = "DELETE FROM users WHERE id = :user_id";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([':user_id' => $user_id]);
            
            return $success;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>