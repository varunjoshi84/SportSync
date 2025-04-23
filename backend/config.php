<?php
/**
 * Database Configuration File
 * 
 * This file contains database connection parameters and establishes
 * a PDO connection to the MySQL database for the SportSync application.
 */

// Database connection parameters
$host = 'localhost';
$dbname = 'sport_sync';
$username = 'root';
$password = '';

try {
    // Establish PDO database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Terminate script if connection fails
    die("Connection failed: " . $e->getMessage());
}
// echo "configuration sucessfully";
?>