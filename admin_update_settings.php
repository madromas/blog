<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Проверка прав администратора
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Здесь должна быть логика сохранения настроек в базу данных или конфигурационный файл
    // В данном примере мы просто устанавливаем сообщение об успехе
    
    $_SESSION['success'] = 'Настройки успешно обновлены';
}

header('Location: admin.php?tab=settings');
exit;
?>