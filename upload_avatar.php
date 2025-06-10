<?php
require_once 'includes/config.php';
require_once 'includes/functions.php'; require_once 'includes/auth_check.php';

if (!isLoggedIn() || !isset($_FILES['avatar'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$avatar = uploadImage($_FILES['avatar']);

if ($avatar) {
    // Удаляем старый аватар, если он не дефолтный
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $old_avatar = $stmt->fetchColumn();
    
    if ($old_avatar !== 'default.png' && file_exists(UPLOAD_DIR . $old_avatar)) {
        unlink(UPLOAD_DIR . $old_avatar);
    }
    
    // Обновляем аватар в БД
    $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$avatar, $user_id]);
}

header("Location: profile.php?id=$user_id");
exit;
?>