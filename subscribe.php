<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';  

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header('Location: index.php');
    exit;
}

$subscriber_id = $_SESSION['user_id'];
$user_id = (int)$_GET['user_id'];

// Проверяем, что пользователь не пытается подписаться на себя
if ($subscriber_id == $user_id) {
    $_SESSION['error'] = 'You can not subscribe to yourself.';
    header("Location: profile.php?id=$user_id");
    exit;
}

// Проверяем существование пользователя
$user = getUser($user_id);
if (!$user || $user['id'] == 0) {
    $_SESSION['error'] = 'User not found';
    header('Location: index.php');
    exit;
}

// Проверяем, есть ли уже подписка
$stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE subscriber_id = ? AND user_id = ?");
$stmt->execute([$subscriber_id, $user_id]);
$existing_subscription = $stmt->fetch();

if ($existing_subscription) {
    // Отписываемся
    $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE subscriber_id = ? AND user_id = ?");
    $stmt->execute([$subscriber_id, $user_id]);
    $_SESSION['success'] = 'You have unfollowed this user';
    
    // Обновляем счетчик подписчиков
    $pdo->prepare("UPDATE users SET followers_count = followers_count - 1 WHERE id = ?")->execute([$user_id]);
} else {
    // Подписываемся
    $stmt = $pdo->prepare("INSERT INTO subscriptions (subscriber_id, user_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$subscriber_id, $user_id]);
    $_SESSION['success'] = 'You are following this user';
    
    // Обновляем счетчик подписчиков
    $pdo->prepare("UPDATE users SET followers_count = followers_count + 1 WHERE id = ?")->execute([$user_id]);
    
    // Проверяем достижения
    checkAchievement($subscriber_id, 'follow_users', 1);
    
    // Можно добавить уведомление для пользователя
    // createNotification($user_id, 'new_follower', $subscriber_id);
}

header("Location: profile.php?id=$user_id");
exit;