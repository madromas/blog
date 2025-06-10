<?php
require_once 'includes/config.php';
require_once 'includes/functions.php'; require_once 'includes/auth_check.php';

// Check admin rights
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

// Check if the ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = (int)$_GET['id']; // Sanitize ID

    // Set a session variable to indicate that the post form should be displayed
    $_SESSION['admin_tab'] = 'edit_post';
    $_SESSION['edit_post_id'] = $post_id; // Store the post ID

    header('Location: admin.php?tab=posts'); // Redirect back to admin.php
    exit;
} else {
    $_SESSION['message'] = "Invalid post ID.";
    $_SESSION['message_type'] = 'danger';
    header('Location: admin.php?tab=posts');
    exit;
}
?>