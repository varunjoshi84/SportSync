<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting test...<br>";

// Check if files exist
$dbFile = __DIR__ . '/../backend/db.php';
$authFile = __DIR__ . '/../backend/auth.php';

echo "Checking required files:<br>";
echo "db.php exists: " . (file_exists($dbFile) ? "✅ Yes" : "❌ No") . "<br>";
echo "auth.php exists: " . (file_exists($authFile) ? "✅ Yes" : "❌ No") . "<br>";

// Include files
try {
    require_once $dbFile;
    echo "✅ db.php included successfully<br>";
    
    require_once $authFile;
    echo "✅ auth.php included successfully<br>";
} catch (Exception $e) {
    echo "❌ Error including files: " . $e->getMessage() . "<br>";
    die();
}

echo "<h2>Database Connection Test</h2>";

try {
    // Test database connection
    $db = getDB();
    echo "✅ Database connection successful<br>";
    
    // Check users table
    $result = $db->query("DESCRIBE users");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Users table structure:<br>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    // Check if test user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => 'test@example.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Test user found:<br>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        
        // Test password
        $testPassword = 'test123';
        $hashedPassword = md5($testPassword);
        echo "Testing password hash:<br>";
        echo "Input password: " . $testPassword . "<br>";
        echo "MD5 hash: " . $hashedPassword . "<br>";
        echo "Stored hash: " . $user['password'] . "<br>";
        echo "Match: " . ($hashedPassword === $user['password'] ? "✅ Yes" : "❌ No") . "<br>";
    } else {
        echo "❌ Test user not found<br>";
        
        // Create test user
        echo "Creating test user...<br>";
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $result = $stmt->execute([
            ':username' => 'testuser',
            ':email' => 'test@example.com',
            ':password' => md5('test123')
        ]);
        
        if ($result) {
            echo "✅ Test user created successfully<br>";
        } else {
            echo "❌ Failed to create test user<br>";
        }
    }
    
    // Test login function
    echo "<h2>Login Test</h2>";
    $loginResult = loginUser('test@example.com', 'test123');
    echo "Login result:<br>";
    echo "<pre>";
    print_r($loginResult);
    echo "</pre>";
    
    // Check session
    echo "<h2>Session Test</h2>";
    echo "Session status: " . session_status() . "<br>";
    echo "Session data:<br>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br>";
    echo "<pre>";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?> 