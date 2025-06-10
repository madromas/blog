<?php
require_once 'includes/config.php';
require_once 'includes/functions.php'; require_once 'includes/auth_check.php';

// Проверка прав администратора
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = in_array($_POST['role'], ['user', 'moderator', 'admin']) ? $_POST['role'] : 'user';
    
    // Нельзя изменить свою роль
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'You can not change your role.';
        header('Location: admin.php?tab=users');
        exit;
    }
    
    $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $user_id]);
    $_SESSION['success'] = 'The user role has been successfully updated';
}

header('Location: admin.php?tab=users');
exit;
?>