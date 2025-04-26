<?php
if (!function_exists('checkDatabaseStructure')) {
    
    function checkDatabaseStructure($db = null) {
        try {
            if ($db === null) {
                $db = getDB();
            }
            
            $result = $db->query("DESCRIBE users");
            $columns = $result->fetchAll(PDO::FETCH_ASSOC);
            
            $result = $db->query("SELECT COUNT(*) as count FROM users");
            $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('testDatabaseConnection')) {
  
    function testDatabaseConnection() {
        try {
            $host = 'localhost';
            $dbname = 'sport_sync';
            $username = 'root';
            $password = '';
            
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            try {
                $db = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
                $dsn = "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8";
                $db = new PDO($dsn, $username, $password, $options);
            }
            
            $result = $db->query("SHOW TABLES");
            $tables = $result->fetchAll(PDO::FETCH_COLUMN);
            return true;
        } catch (Exception $e) {
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
        
        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            try {
                $db = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
                $dsn = "mysql:unix_socket=$socket;dbname=$dbname;charset=utf8";
                $db = new PDO($dsn, $username, $password, $options);
            }
            
            $db->query("SELECT 1");
            
            return $db;
        } catch (PDOException $e) {
            return null;
        }
    }
}

if (!function_exists('executeQuery')) {
 
    function executeQuery($sql, $params = []) {
        try {
            $db = getDB();
            if (!$db) {
                return [];
            }
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                return [];
            }
            
            $result = $stmt->execute($params);
            if (!$result) {
                return [];
            }
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        } catch (Exception $e) {
            return [];
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
            winner VARCHAR(100) DEFAULT NULL,
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
                account_type ENUM('user', 'admin') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            executeQuery($sql);
            return true;
        } catch (Exception $e) {
            return false;
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
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
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

if (!function_exists('addWinnerColumnToMatchesTable')) {
  
    function addWinnerColumnToMatchesTable() {
        try {
            $db = getDB();
            
            // Check if column exists
            $result = $db->query("SHOW COLUMNS FROM matches LIKE 'winner'");
            $columnExists = $result->rowCount() > 0;
            
            if (!$columnExists) {
                // Add column if it doesn't exist
                $sql = "ALTER TABLE matches ADD COLUMN winner VARCHAR(100) DEFAULT NULL AFTER status";
                $db->exec($sql);
                
                // Update existing completed matches based on scores
                $sql = "UPDATE matches 
                        SET winner = 
                            CASE 
                                WHEN team1_score > team2_score THEN team1
                                WHEN team2_score > team1_score THEN team2
                                WHEN team1_score = team2_score AND status = 'completed' THEN 'Draw'
                                ELSE NULL
                            END
                        WHERE status = 'completed'";
                $db->exec($sql);
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('initializeTables')) {
   
    function initializeTables($db = null) {
        try {
            if ($db === null) {
                $db = getDB();
            }
            
            createUsersTable();
            createMatchTable();
            createNewsTable();
            createFavoritesTable();
            createFeedbackTable();
            createSubscriptionsTable();
            
            addWinnerColumnToMatchesTable();
            
            checkDatabaseStructure($db);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
