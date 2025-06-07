<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn() || !isset($_POST['post_id']) || empty($_POST['content'])) {
    header('Location: index.php');
    exit;
}

$post_id = (int)$_POST['post_id'];
$content = sanitize($_POST['content']);

// Вставляем новый комментарий (без указания upvotes и downvotes)
$stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$post_id, $_SESSION['user_id'], $content]);
$comment_id = $pdo->lastInsertId();

// Увеличиваем счетчик комментариев в посте
$pdo->prepare("UPDATE posts SET comments_count = comments_count + 1 WHERE id = ?")->execute([$post_id]);

// Проверяем достижение "first_comment" (первый комментарий)
checkAchievement($_SESSION['user_id'], 'first_comment');

// Получаем данные о только что созданном комментарии
$commentStmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
$commentStmt->execute([$comment_id]);
$comment = $commentStmt->fetch();

header("Location: post.php?id=$post_id");
exit;
?>