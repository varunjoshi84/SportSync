<?php
if (!function_exists('checkDatabaseStructure')) {
    function checkDatabaseStructure() {
        try {
            $db = getDB();
            
            // Check if users table exists and its structure
            $result = $db->query("DESCRIBE users");
            $columns = $result->fetchAll(PDO::FETCH_ASSOC);
            error_log("Users table structure: " . json_encode($columns));
            
            // Check if there are any users
            $result = $db->query("SELECT COUNT(*) as count FROM users");
            $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
            error_log("Number of users in database: " . $count);
            
            return true;
        } catch (Exception $e) {
            error_log("Database structure check failed: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('testDatabaseConnection')) {
    function testDatabaseConnection() {
        try {
            $db = getDB();
            $result = $db->query("SHOW TABLES");
            $tables = $result->fetchAll(PDO::FETCH_COLUMN);
            error_log("Connected to database. Tables found: " . implode(", ", $tables));
            return true;
        } catch (Exception $e) {
            error_log("Database test failed: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('getDB')) {
    function getDB() {
        $host = 'localhost';
        $dbname = 'sport_sync';
        $username = 'root';
        $password = '';
        $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';

        try {
            // First try to connect to MySQL without selecting a database
            $pdo = new PDO("mysql:unix_socket=$socket", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if database exists, if not create it
            $result = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
            if ($result->rowCount() == 0) {
                $pdo->exec("CREATE DATABASE `$dbname`");
                error_log("Database '$dbname' created successfully");
            }
            
            // Now connect to the specific database
            $db = new PDO("mysql:unix_socket=$socket;dbname=$dbname;charset=utf8", $username, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Test the connection
            $db->query("SELECT 1");
            
            // Create matches table if it doesn't exist
            $db->exec("CREATE TABLE IF NOT EXISTS matches (
                id INT AUTO_INCREMENT PRIMARY KEY,
                team1 VARCHAR(255) NOT NULL,
                team2 VARCHAR(255) NOT NULL,
                team1_flag VARCHAR(10) DEFAULT 'gb',
                team2_flag VARCHAR(10) DEFAULT 'gb',
                team1_score INT DEFAULT 0,
                team2_score INT DEFAULT 0,
                venue VARCHAR(255) NOT NULL,
                match_time DATETIME NOT NULL,
                sport VARCHAR(50) NOT NULL,
                status ENUM('upcoming', 'live', 'completed') DEFAULT 'upcoming',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Create users table if it doesn't exist
            $db->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Create newsletter table if it doesn't exist
            $db->exec("CREATE TABLE IF NOT EXISTS newsletter (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Create feedback table if it doesn't exist
            $db->exec("CREATE TABLE IF NOT EXISTS feedback (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                status ENUM('pending', 'read', 'responded') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Create favorites table if it doesn't exist
            $db->exec("CREATE TABLE IF NOT EXISTS favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                match_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_favorite (user_id, match_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
            )");
            
            return $db;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }
}

if (!function_exists('executeQuery')) {
    function executeQuery($sql, $params = []) {
        try {
            error_log("Executing SQL: " . $sql);
            error_log("With params: " . json_encode($params));
            
            $db = getDB();
            if (!$db) {
                error_log("Database connection failed in executeQuery");
                throw new Exception("Database connection failed");
            }
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                error_log("Failed to prepare statement: " . json_encode($db->errorInfo()));
                throw new Exception("Failed to prepare statement");
            }
            
            $result = $stmt->execute($params);
            if (!$result) {
                error_log("Failed to execute statement: " . json_encode($stmt->errorInfo()));
                throw new Exception("Failed to execute statement");
            }
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Query executed successfully. Found " . count($data) . " rows");
            return $data;
        } catch (Exception $e) {
            error_log("Error in executeQuery: " . $e->getMessage());
            throw $e;
        }
    }
}

if (!function_exists('createNewsTable')) {
    function createNewsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS news (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            image_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        return executeQuery($sql);
    }
}

if (!function_exists('createMatchTable')) {
    function createMatchTable() {
        $sql = "CREATE TABLE IF NOT EXISTS matches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            team1 VARCHAR(100) NOT NULL,
            team2 VARCHAR(100) NOT NULL,
            team1_score INT DEFAULT 0,
            team2_score INT DEFAULT 0,
            venue VARCHAR(100) NOT NULL,
            match_time DATETIME NOT NULL,
            sport VARCHAR(50) NOT NULL,
            status VARCHAR(20) DEFAULT 'upcoming',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        return executeQuery($sql);
    }
}

if (!function_exists('createUsersTable')) {
    function createUsersTable() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            executeQuery($sql);
            error_log("Users table created or already exists");
            return true;
        } catch (Exception $e) {
            error_log("Error creating users table: " . $e->getMessage());
            throw $e;
        }
    }
}

if (!function_exists('createFavoritesTable')) {
    function createFavoritesTable() {
        $sql = "CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            match_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
        )";
        return executeQuery($sql);
    }
}

if (!function_exists('createFeedbackTable')) {
    function createFeedbackTable() {
        $sql = "CREATE TABLE IF NOT EXISTS feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        return executeQuery($sql);
    }
}

if (!function_exists('createSubscriptionsTable')) {
    function createSubscriptionsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        return executeQuery($sql);
    }
}

if (!function_exists('initializeTables')) {
    function initializeTables() {
        try {
            // Test database connection first
            if (!testDatabaseConnection()) {
                throw new Exception("Database connection test failed");
            }
            
            createUsersTable();
            
            // Check database structure
            checkDatabaseStructure();
            
            error_log("All tables initialized successfully");
            return true;
        } catch (Exception $e) {
            error_log("Error initializing tables: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
