<?php
// Конфигурация сайта
define('DB_HOST', 'localhost');
define('DB_USER', 'DB_USER');
define('DB_PASS', 'PASS');
define('DB_NAME', 'DB_NAME');

// Настройки сайта
define('SITE_NAME', 'My Website');
define('SITE_URL', 'https://url.com');
define('UPLOAD_DIR', 'uploads/');

// Старт сессии
session_start();

// Подключение к БД
require_once 'db.php';
?>