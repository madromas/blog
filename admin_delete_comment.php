<?php
require_once 'includes/config.php';
require_once 'includes/functions.php'; require_once 'includes/auth_check.php';

// Check if the user is an admin
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php'); // Or redirect to a "not authorized" page
    exit;
}

// Get the comment ID from the query string
$comment_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// If the comment ID is invalid, redirect to the admin panel
if ($comment_id <= 0) {
    header('Location: admin.php?tab=comments');
    exit;
}

// ** 1. Fetch the comment to get the image filename **
$stmt = $pdo->prepare("SELECT image FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if ($comment) {
    $image_filename = $comment['image'];

    // ** 2. Delete the image file (if it exists) **
    if (!empty($image_filename)) {
        $image_path = 'uploads/' . $image_filename; // Construct the full path
        if (file_exists($image_path)) {
            if (unlink($image_path)) {
                // Image deleted successfully
            } else {
                // Error deleting image (log the error if needed)
                error_log("Error deleting image: " . $image_path);
                $_SESSION['message'] = 'Comment deleted, but there was an error deleting the image.';
                $_SESSION['message_type'] = 'warning';
            }
        }
    }

    // ** 3. Delete the comment from the database **
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    if ($stmt->execute([$comment_id])) {
        // Set a success message
        $_SESSION['message'] = 'Comment deleted successfully.';
        $_SESSION['message_type'] = 'success';

        // Decrease comment count
        decreaseCommentCount($comment_id);

    } else {
        // Set an error message
        $_SESSION['message'] = 'Error deleting comment.';
        $_SESSION['message_type'] = 'danger';
    }
} else {
    // Comment not found
    $_SESSION['message'] = 'Comment not found.';
    $_SESSION['message_type'] = 'danger';
}
// Redirect to the admin panel (comments tab)
header('Location: admin.php?tab=comments');
exit;

function decreaseCommentCount($comment_id) {
    global $pdo;
    // Fetch the post_id associated with the comment before deleting
    $stmt = $pdo->prepare("SELECT post_id FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    if ($comment && isset($comment['post_id'])) {
        $post_id = $comment['post_id'];
        // Decrease the comment count in the posts table
        $pdo->prepare("UPDATE posts SET comments_count = GREATEST(comments_count - 1, 0) WHERE id = ?")->execute([$post_id]);
    }
}
?>