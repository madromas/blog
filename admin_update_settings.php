<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Проверка прав администратора
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // There should be a logic for saving settings to a database or configuration file
// In this example, we simply set a success message
    
    $_SESSION['success'] = 'Settings have been updated successfully';
}

header('Location: admin.php?tab=settings');
exit;
?>