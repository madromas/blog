<?php
// config.php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'DB_USER');
define('DB_PASS', 'DB_PASS');
define('DB_NAME', 'DB_NAME');

define('UPLOAD_DIR', 'uploads/');

// Session start
session_start();

// Connect to DB
require_once 'db.php'; // This file should establish the $pdo connection

// Load settings from the database
try {
    // Ensure $pdo is defined in db.php
    global $pdo; // Important: Make sure $pdo is accessible in this scope

    $stmt = $pdo->prepare("SELECT site_name, site_url FROM settings WHERE id = 1");
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    // Define the constants using the values from the database
    define('SITE_NAME', $settings['site_name'] ?? 'Your Site Name'); // Use ?? for default value
    define('SITE_URL', $settings['site_url'] ?? 'https://your-site-url.com');
    define('SITE_STORIES_LIFETIME', $settings['stories_lifetime'] ?? 24);
} catch (PDOException $e) {
    error_log("Database error loading settings in config.php: " . $e->getMessage());
}

?>
