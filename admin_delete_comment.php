<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

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

// Redirect to the admin panel (comments tab)
header('Location: admin.php?tab=comments');
exit;
?>