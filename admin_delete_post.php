<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check admin rights
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

// Check if the ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = (int)$_GET['id']; // Sanitize ID

    // Call a function to delete the post (defined in functions.php)
    if (deletePost($post_id)) {
        $_SESSION['message'] = "Post deleted successfully.";
        $_SESSION['message_type'] = 'success'; // For displaying message in admin.php
    } else {
        $_SESSION['message'] = "Error deleting post.";
        $_SESSION['message_type'] = 'danger'; // For displaying message in admin.php
    }
} else {
    $_SESSION['message'] = "Invalid post ID.";
    $_SESSION['message_type'] = 'danger';
}

header('Location: admin.php?tab=posts'); // Redirect back to the posts tab
exit;
?>