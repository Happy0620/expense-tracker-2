<?php

/**
 * Database Configuration
 * Update these credentials according to your server
 */

// Development Configuration (Local)
define('DB_HOST', 'localhost');
define('DB_NAME', 'expense_tracker');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Production Configuration (School Server)
// Uncomment these when deploying to school server
// define('DB_HOST', 'your_school_db_host');
// define('DB_NAME', 'your_database_name');
// define('DB_USER', 'your_username');
// define('DB_PASS', 'your_password');
// define('DB_CHARSET', 'utf8mb4');

/**
 * Create PDO Database Connection
 * Using PDO for security (prepared statements)
 */
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}

// Timezone setting
date_default_timezone_set('Asia/Kathmandu');
