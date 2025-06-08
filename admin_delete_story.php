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
    $story_id = (int)$_GET['id']; // Sanitize ID

    // Call a function to delete the story (defined in functions.php)
    if (deleteStory($story_id)) {
        $_SESSION['message'] = "Story deleted successfully.";
        $_SESSION['message_type'] = 'success'; // For displaying message in admin.php
    } else {
        $_SESSION['message'] = "Error deleting story.";
        $_SESSION['message_type'] = 'danger'; // For displaying message in admin.php
    }
} else {
    $_SESSION['message'] = "Invalid story ID.";
    $_SESSION['message_type'] = 'danger';
}

header('Location: admin.php?tab=stories'); // Redirect back to the storiess tab
exit;
?>