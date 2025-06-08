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

// Delete the comment from the database
if (deleteComment($comment_id)) {
    // Set a success message
    $_SESSION['message'] = 'Comment deleted successfully.';
    $_SESSION['message_type'] = 'success';
} else {
    // Set an error message
    $_SESSION['message'] = 'Error deleting comment.';
    $_SESSION['message_type'] = 'danger';
}

// Redirect to the post page from which the comment was deleted
header("Location: post.php?id=" . $comment['post_id']);
exit;
?>