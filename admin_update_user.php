<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

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
        $_SESSION['error'] = 'Вы не можете изменить свою роль';
        header('Location: admin.php?tab=users');
        exit;
    }
    
    $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $user_id]);
    $_SESSION['success'] = 'Роль пользователя успешно обновлена';
}

header('Location: admin.php?tab=users');
exit;
?>