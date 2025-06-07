<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Проверка прав администратора
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Нельзя забанить себя
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'You cannot ban your self';
        header('Location: admin.php?tab=users');
        exit;
    }
    
    // Удаляем все посты, комментарии и истории пользователя
    $pdo->beginTransaction();
    
    try {
        // Получаем все посты пользователя для удаления изображений
        $posts = $pdo->prepare("SELECT id, image FROM posts WHERE user_id = ?");
        $posts->execute([$user_id]);
        
        foreach ($posts as $post) {
            if ($post['image'] && file_exists(UPLOAD_DIR . $post['image'])) {
                unlink(UPLOAD_DIR . $post['image']);
            }
        }
        
        // Удаляем посты
        $pdo->prepare("DELETE FROM posts WHERE user_id = ?")->execute([$user_id]);
        
        // Удаляем комментарии
        $pdo->prepare("DELETE FROM comments WHERE user_id = ?")->execute([$user_id]);
        
        // Удаляем истории
        $stories = $pdo->prepare("SELECT id, image FROM stories WHERE user_id = ?");
        $stories->execute([$user_id]);
        
        foreach ($stories as $story) {
            if ($story['image'] && file_exists(UPLOAD_DIR . $story['image'])) {
                unlink(UPLOAD_DIR . $story['image']);
            }
        }
        
        $pdo->prepare("DELETE FROM stories WHERE user_id = ?")->execute([$user_id]);
        
        // Удаляем пользователя
        $user = $pdo->prepare("SELECT avatar FROM users WHERE id = ?")->fetch();
        if ($user['avatar'] != 'default.png' && file_exists(UPLOAD_DIR . $user['avatar'])) {
            unlink(UPLOAD_DIR . $user['avatar']);
        }
        
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        
        $pdo->commit();
        $_SESSION['success'] = 'The user and all his content have been successfully deleted';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error when deleting a user: ' . $e->getMessage();
    }
}

header('Location: admin.php?tab=users');
exit;
?>