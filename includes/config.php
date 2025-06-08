<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'DB_USER');
define('DB_PASS', 'PASS');
define('DB_NAME', 'DB_NAME');

// Website configuration
define('SITE_NAME', 'My Website');
define('SITE_URL', 'https://url.com');
define('UPLOAD_DIR', 'uploads/');

// Session start
session_start();

// Connect to DB
require_once 'db.php';
?>
