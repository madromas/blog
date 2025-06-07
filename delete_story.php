<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['story_id'])) {
    $story_id = (int)$_POST['story_id'];
    
    // Проверяем права
    $stmt = $pdo->prepare("SELECT user_id FROM stories WHERE id = ?");
    $stmt->execute([$story_id]);
    $story = $stmt->fetch();
    
    if ($story && ($story['user_id'] == $_SESSION['user_id'] || getUser($_SESSION['user_id'])['role'] == 'admin' || getUser($_SESSION['user_id'])['role'] == 'moderator')) {
        $pdo->prepare("DELETE FROM stories WHERE id = ?")->execute([$story_id]);
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'stories.php'));
exit;
?>