<?php
// PDO Database Connection

// Require the configuration file that holds the credentials.
// This file is not committed to Git.
require_once __DIR__ . '/db_config.php';

// Set DSN (Data Source Name) using variables from db_config.php
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// Set PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // If connection fails, stop the script and show an error
    die("Database connection failed: " . $e->getMessage());
}

// The $pdo object is now available for all other scripts that include this file.
?>