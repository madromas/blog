<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';  

// Проверка прав модератора
if (!isLoggedIn() || !hasPermission('moderator')) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id']) && isset($_POST['action'])) {
    $report_id = (int)$_POST['report_id'];
    $action = $_POST['action'];
    $moderator_id = $_SESSION['user_id'];
    
    if ($action == 'approve' || $action == 'reject') {
        $status = $action == 'approve' ? 'approved' : 'rejected';
        
        // Обновляем статус жалобы
        $pdo->prepare("
            UPDATE content_reports 
            SET status = ?, moderator_id = ?, resolved_at = NOW() 
            WHERE id = ?
        ")->execute([$status, $moderator_id, $report_id]);
        
        // Если жалоба принята, удаляем контент
        if ($action == 'approve') {
            // Получаем информацию о жалобе
            $stmt = $pdo->prepare("SELECT * FROM content_reports WHERE id = ?");
            $stmt->execute([$report_id]);
            $report = $stmt->fetch();
            
            if ($report) {
                if ($report['post_id']) {
                    // Удаляем пост и связанные с ним комментарии
                    $post = $pdo->prepare("SELECT image FROM posts WHERE id = ?")->fetch();
                    if ($post['image'] && file_exists(UPLOAD_DIR . $post['image'])) {
                        unlink(UPLOAD_DIR . $post['image']);
                    }
                    $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$report['post_id']]);
                } elseif ($report['comment_id']) {
                    $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$report['comment_id']]);
                } elseif ($report['story_id']) {
                    $story = $pdo->prepare("SELECT image FROM stories WHERE id = ?")->fetch();
                    if ($story['image'] && file_exists(UPLOAD_DIR . $story['image'])) {
                        unlink(UPLOAD_DIR . $story['image']);
                    }
                    $pdo->prepare("DELETE FROM stories WHERE id = ?")->execute([$report['story_id']]);
                }
            }
            
            $_SESSION['success'] = 'The complaint has been accepted and the content has been deleted';
        } else {
            $_SESSION['success'] = 'The complaint was rejected';
        }
    }
}

header('Location: admin.php?tab=reports');
exit;
?>