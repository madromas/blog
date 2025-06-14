<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once 'includes/config.php';
require_once 'includes/functions.php';
 

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$allowed_types = ['post', 'comment', 'story', 'user'];
$type = isset($_GET['type']) ? $_GET['type'] : '';
$content_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Проверяем тип жалобы
if (!in_array($type, $allowed_types) || $content_id <= 0) {
    header('Location: index.php');
    exit;
}

// Проверяем существование контента
$content_exists = false;
switch ($type) {
    case 'post':
        $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
        break;
    case 'comment':
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ?");
        break;
    case 'story':
        $stmt = $pdo->prepare("SELECT id FROM stories WHERE id = ? AND is_active = TRUE");
        break;
    case 'user':
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND id != ?");
        $stmt->execute([$content_id, $user_id]);
        $content_exists = $stmt->fetch();
        break;
    default:
        break;
}

if ($type != 'user') {
    $stmt->execute([$content_id]);
    $content_exists = $stmt->fetch();
}

if (!$content_exists) {
    header('Location: index.php');
    exit;
}

// Обработка отправки жалобы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = sanitize($_POST['reason']);
    
    if (empty($reason)) {
        $error = 'Please indicate the reason for the complaint.';
    } else {
        // Fix: Explicitly define table and field names
        if ($type == 'user') {
            $table = 'user_reports';
            $field = 'reported_user_id';
        } else {
            $table = 'content_reports';
            $field = $type . '_id'; // post_id, comment_id, story_id
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO $table (reporter_id, $field, reason)
            VALUES (?, ?, ?)
        ");
    
        // Log the parameters and SQL
        error_log("SQL: " . $stmt->queryString . ", Parameters: " . json_encode([$user_id, $content_id, $reason]));
        
        try {
            $stmt->execute([$user_id, $content_id, $reason]);
            $success = 'Your complaint has been sent to the moderators. Thanks!';
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            error_log("Error submitting report: " . $e->getMessage()); // Log the error
        }
    }
}

$page_title = 'Complain about ' . $type;
include 'includes/header.php';
?>

<div class="report-page">
    <h1>Complain about <?= $type ?></h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <a href="javascript:history.back()" class="btn btn-primary">Go back</a>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form action="report.php?type=<?= $type ?>&id=<?= $content_id ?>" method="POST">
            <div class="form-group">
                <label for="reason">Reason for complaint</label>
                <textarea id="reason" name="reason" rows="5" required></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Send a complaint</button>
                <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>