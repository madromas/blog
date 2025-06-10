<?php
require_once 'includes/config.php';
require_once 'includes/functions.php'; require_once 'includes/auth_check.php';

if (!isLoggedIn() || !isset($_GET['post_id']) || !isset($_GET['type'])) {
    // Send an error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$post_id = (int)$_GET['post_id'];
$vote_type = $_GET['type'] === 'up' ? 'upvote' : 'downvote';

try {
    // Проверяем, голосовал ли уже пользователь
    $stmt = $pdo->prepare("SELECT * FROM votes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$_SESSION['user_id'], $post_id]);
    $existing_vote = $stmt->fetch();

    if ($existing_vote) {
        // Если пользователь уже голосовал так же - отменяем голос
        if ($existing_vote['type'] === $vote_type) {
            $stmt = $pdo->prepare("DELETE FROM votes WHERE id = ?");
            $stmt->execute([$existing_vote['id']]);
            $stmt = $pdo->prepare("UPDATE posts SET {$vote_type}s = {$vote_type}s - 1 WHERE id = ?");
            $stmt->execute([$post_id]);
        } else {
            // Если голос другого типа - меняем голос
            $stmt = $pdo->prepare("UPDATE votes SET type = ? WHERE id = ?");
            $stmt->execute([$vote_type, $existing_vote['id']]);

            $update_field = ($vote_type === 'upvote' ? 'downvotes' : 'upvotes');
            $sql = "UPDATE posts SET {$vote_type}s = {$vote_type}s + 1, {$update_field} = {$update_field} - 1 WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$post_id]);
        }
    } else {
        // Новый голос
        $sql = "INSERT INTO votes (user_id, post_id, type) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $post_id, $vote_type]);

        $stmt = $pdo->prepare("UPDATE posts SET {$vote_type}s = {$vote_type}s + 1 WHERE id = ?");
        $stmt->execute([$post_id]);
    }

    // Обновляем рейтинг автора поста
    $stmt = $pdo->prepare("
        UPDATE users u
        JOIN posts p ON u.id = p.user_id
        SET u.rating = (
            SELECT SUM(upvotes - downvotes) 
            FROM posts 
            WHERE user_id = p.user_id
        )
        WHERE p.id = ?
    ");
    $stmt->execute([$post_id]);

    // Get the updated vote counts
    $stmt = $pdo->prepare("SELECT upvotes, downvotes FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    $upvotes = (int)$post['upvotes'];
    $downvotes = (int)$post['downvotes'];
    $newCount = $upvotes - $downvotes;

    // Send a success response with the updated count
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'newCount' => $newCount]);
    exit;

} catch (PDOException $e) {
    // Send an error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>