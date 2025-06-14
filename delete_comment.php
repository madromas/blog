<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';  

// Check if the user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get the comment ID from the query string
$comment_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// Get the comment data from the database
$comment = getCommentById($comment_id);

// If the comment doesn't exist, redirect to the homepage
if (!$comment) {
    header('Location: index.php');
    exit;
}

// Get the current user's ID
$current_user_id = $_SESSION['user_id'];

// Check if the current user is the author of the comment, an admin, or a moderator
$is_author = ($current_user_id == $comment['user_id']);
$is_admin = hasPermission('admin');
$is_moderator = hasPermission('moderator'); // Replace with your actual moderator permission check

// If the user does not have permission to delete the comment, redirect to the homepage
if (!$is_author && !$is_admin && !$is_moderator) {
    header('Location: index.php');
    exit;
}

// ** 1. Get the image filename **
$stmt = $pdo->prepare("SELECT image, post_id FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$commentData = $stmt->fetch();

if ($commentData) {
    $image_filename = $commentData['image'];
    $post_id = $commentData['post_id'];  // Store the post_id

    // ** 2. Delete the image file (if it exists) **
    if (!empty($image_filename)) {
        $image_path = 'uploads/' . $image_filename;
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

     // Redirect to the post page from which the comment was deleted
    header("Location: post.php?id=" . $post_id);
    exit;

} else {
    // Comment not found
    $_SESSION['message'] = 'Comment not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');  // Redirect if comment is not found
    exit;
}
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