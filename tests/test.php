<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the page variable for the header
$page = 'test';

// Include the header
include __DIR__ . '/header.php';

echo "<div class='container mx-auto px-4 py-8'>";
echo "<h1 class='text-2xl font-bold text-white mb-6'>SportSync Test Page</h1>";

// Check if files exist
$dbFile = __DIR__ . '/../backend/db.php';
$authFile = __DIR__ . '/../backend/auth.php';

echo "<div class='bg-gray-800 p-4 rounded-lg mb-6'>";
echo "<h2 class='text-xl font-semibold text-white mb-4'>File Check</h2>";
echo "<p class='text-gray-300'>db.php exists: " . (file_exists($dbFile) ? "✅ Yes" : "❌ No") . "</p>";
echo "<p class='text-gray-300'>auth.php exists: " . (file_exists($authFile) ? "✅ Yes" : "❌ No") . "</p>";
echo "</div>";

// Include files
try {
    require_once $dbFile;
    echo "<p class='text-green-500'>✅ db.php included successfully</p>";
    
    require_once $authFile;
    echo "<p class='text-green-500'>✅ auth.php included successfully</p>";
} catch (Exception $e) {
    echo "<p class='text-red-500'>❌ Error including files: " . $e->getMessage() . "</p>";
    die();
}

echo "<div class='bg-gray-800 p-4 rounded-lg mb-6'>";
echo "<h2 class='text-xl font-semibold text-white mb-4'>Database Connection Test</h2>";

try {
    // Test database connection
    $db = getDB();
    echo "<p class='text-green-500'>✅ Database connection successful</p>";
    
    // Check users table
    $result = $db->query("DESCRIBE users");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "<p class='text-green-500'>✅ Users table structure:</p>";
    echo "<pre class='bg-gray-900 p-4 rounded text-gray-300 overflow-auto'>";
    print_r($columns);
    echo "</pre>";
    
    // Check if test user exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => 'test@example.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "<p class='text-green-500'>✅ Test user found:</p>";
        echo "<pre class='bg-gray-900 p-4 rounded text-gray-300 overflow-auto'>";
        print_r($user);
        echo "</pre>";
        
        // Test password
        $testPassword = 'test123';
        $hashedPassword = md5($testPassword);
        echo "<p class='text-white'>Testing password hash:</p>";
        echo "<p class='text-gray-300'>Input password: " . $testPassword . "</p>";
        echo "<p class='text-gray-300'>MD5 hash: " . $hashedPassword . "</p>";
        echo "<p class='text-gray-300'>Stored hash: " . $user['password'] . "</p>";
        echo "<p class='text-gray-300'>Match: " . ($hashedPassword === $user['password'] ? "✅ Yes" : "❌ No") . "</p>";
    } else {
        echo "<p class='text-red-500'>❌ Test user not found</p>";
        
        // Create test user
        echo "<p class='text-white'>Creating test user...</p>";
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $result = $stmt->execute([
            ':username' => 'testuser',
            ':email' => 'test@example.com',
            ':password' => md5('test123')
        ]);
        
        if ($result) {
            echo "<p class='text-green-500'>✅ Test user created successfully</p>";
        } else {
            echo "<p class='text-red-500'>❌ Failed to create test user</p>";
        }
    }
    
    // Test login function
    echo "<h2 class='text-xl font-semibold text-white mt-6 mb-4'>Login Test</h2>";
    $loginResult = loginUser('test@example.com', 'test123');
    echo "<p class='text-white'>Login result:</p>";
    echo "<pre class='bg-gray-900 p-4 rounded text-gray-300 overflow-auto'>";
    print_r($loginResult);
    echo "</pre>";
    
    // Check session
    echo "<h2 class='text-xl font-semibold text-white mt-6 mb-4'>Session Test</h2>";
    echo "<p class='text-gray-300'>Session status: " . session_status() . "</p>";
    echo "<p class='text-white'>Session data:</p>";
    echo "<pre class='bg-gray-900 p-4 rounded text-gray-300 overflow-auto'>";
    print_r($_SESSION);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p class='text-red-500'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p class='text-white'>Stack trace:</p>";
    echo "<pre class='bg-gray-900 p-4 rounded text-gray-300 overflow-auto'>";
    echo $e->getTraceAsString();
    echo "</pre>";
}

echo "</div>";
echo "</div>";

// Include the footer
include __DIR__ . '/footer.php';
?> 