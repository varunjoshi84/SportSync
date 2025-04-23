<?php
/**
 * Database Management File
 * 
 * This file contains all database-related functions for the SportSync application
 * including connection handling, table creation, and query execution.
 * 
 */

if (!function_exists('checkDatabaseStructure')) {
    /**
     * Checks the database structure to ensure tables exist and have correct schema
     * 
     * @param PDO $db Optional database connection
     * @return boolean True if structure is valid, false otherwise
     */
    function checkDatabaseStructure($db = null) {
        try {
            // If db connection not provided, get it
            if ($db === null) {
                $db = getDB();
            }
            
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
    /**
     * Tests the database connection with various connection methods
     * 
     * @return boolean True if connection successful, false otherwise
     */
    function testDatabaseConnection() {
        try {
            $host = 'localhost';
            $dbname = 'sport_sync';
            $username = 'root';
            $password = '';
            
            // Try different connection methods
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            try {
                $db = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                // If direct connection fails, try with socket
                $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
                $dsn = "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8";
                $db = new PDO($dsn, $username, $password, $options);
            }
            
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
    /**
     * Creates and returns a database connection
     * 
     * @return PDO Database connection object
     * @throws Exception If connection fails
     */
    function getDB() {
        $host = 'localhost';
        $dbname = 'sport_sync';
        $username = 'root';
        $password = '';
        
        try {
            // Try different connection methods
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            try {
                $db = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                // If direct connection fails, try with socket
                $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
                $dsn = "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8";
                $db = new PDO($dsn, $username, $password, $options);
            }
            
            // Test the connection
            $db->query("SELECT 1");
            
            // Don't call initializeTables here to avoid circular dependency
            // Tables will be initialized separately
            
            return $db;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}

if (!function_exists('executeQuery')) {
    /**
     * Executes a SQL query with optional parameters
     * 
     * @param string $sql SQL query to execute
     * @param array $params Array of parameters for the prepared statement
     * @return array Result set as associative array
     * @throws Exception If query execution fails
     */
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
    /**
     * Creates the news table if it doesn't exist
     * 
     * @return array Result of query execution
     */
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
    /**
     * Creates the matches table if it doesn't exist
     * 
     * @return array Result of query execution
     */
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
    /**
     * Creates the users table if it doesn't exist
     * 
     * @return boolean True if table created or exists
     * @throws Exception If creation fails
     */
    function createUsersTable() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                account_type ENUM('user', 'admin') DEFAULT 'user',
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
    /**
     * Creates the favorites table if it doesn't exist
     * 
     * @return array Result of query execution
     */
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
    /**
     * Creates the feedback table if it doesn't exist
     * 
     * @return array Result of query execution
     */
    function createFeedbackTable() {
        $sql = "CREATE TABLE IF NOT EXISTS feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        return executeQuery($sql);
    }
}

if (!function_exists('createSubscriptionsTable')) {
    /**
     * Creates the subscriptions table if it doesn't exist
     * 
     * @return array Result of query execution
     */
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
    /**
     * Initializes all database tables
     * 
     * @param PDO $db Optional database connection
     * @return boolean True if all tables initialized successfully
     * @throws Exception If initialization fails
     */
    function initializeTables($db = null) {
        try {
            // Test database connection first
            if (!testDatabaseConnection()) {
                throw new Exception("Database connection test failed");
            }
            
            // If db connection not provided, get it
            if ($db === null) {
                $db = getDB();
            }
            
            createUsersTable();
            
            // Check database structure
            checkDatabaseStructure($db);
            
            error_log("All tables initialized successfully");
            return true;
        } catch (Exception $e) {
            error_log("Error initializing tables: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
