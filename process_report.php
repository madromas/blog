<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';  

if (!isLoggedIn() || (getUser($_SESSION['user_id'])['role'] != 'admin' && getUser($_SESSION['user_id'])['role'] != 'moderator')) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id']) && isset($_POST['action'])) {
    $report_id = (int)$_POST['report_id'];
    $action = $_POST['action'];
    $moderator_id = $_SESSION['user_id'];
    
    if ($action == 'approve' || $action == 'reject') {
        $status = $action == 'approve' ? 'approved' : 'rejected';
        
        $pdo->prepare("
            UPDATE content_reports 
            SET status = ?, moderator_id = ?, resolved_at = NOW() 
            WHERE id = ?
        ")->execute([$status, $moderator_id, $report_id]);
        
        // Если жалоба принята, можно удалить контент
        if ($action == 'approve') {
            // Получаем информацию о жалобе
            $stmt = $pdo->prepare("SELECT * FROM content_reports WHERE id = ?");
            $stmt->execute([$report_id]);
            $report = $stmt->fetch();
            
            if ($report) {
                if ($report['post_id']) {
                    $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$report['post_id']]);
                } elseif ($report['comment_id']) {
                    $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$report['comment_id']]);
                } elseif ($report['story_id']) {
                    $pdo->prepare("DELETE FROM stories WHERE id = ?")->execute([$report['story_id']]);
                }
            }
        }
    }
}

header('Location: admin.php?tab=reports');
exit;
?>