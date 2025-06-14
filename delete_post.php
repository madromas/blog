<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';  

// Check if the user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get the post ID from the query string
$post_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// Get the post data from the database
$post = getPostById($post_id);

// If the post doesn't exist, redirect to the homepage
if (!$post) {
    header('Location: index.php');
    exit;
}

// Get the current user's ID
$current_user_id = $_SESSION['user_id'];

// Check if the current user is the author of the post, an admin, or a moderator
$is_author = ($current_user_id == $post['user_id']);
$is_admin = hasPermission('admin');
$is_moderator = hasPermission('moderator'); // Replace with your actual moderator permission check

// If the user does not have permission to delete the post, redirect to the homepage
if (!$is_author && !$is_admin && !$is_moderator) {
    header('Location: index.php');
    exit;
}

// **IMAGE DELETION LOGIC STARTS HERE**

// Get the image filename from the database
$stmt = $pdo->prepare("SELECT image FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post_data = $stmt->fetch();

if ($post_data) {
    $image_filename = $post_data['image'];

    // Delete the image file
    if (!empty($image_filename)) {
        $image_path = 'uploads/' . $image_filename; // Adjust the path if necessary

        // Check if the file exists before attempting to delete it
        if (file_exists($image_path)) {
            // Try to delete the file and handle errors
            if (unlink($image_path)) {
                // File deleted successfully
                $_SESSION['message'] = "Post and image deleted successfully.";
                $_SESSION['message_type'] = 'success';
            } else {
                // Error deleting the file
                $_SESSION['message'] = "Post deleted, but error deleting image.";
                $_SESSION['message_type'] = 'warning';
            }
        } else {
            // File doesn't exist
            $_SESSION['message'] = "Post deleted, but image not found.";
            $_SESSION['message_type'] = 'warning';
        }
    }
}

// Delete the post from the database
if (deletePost($post_id)) {
    // Set a success message
    $_SESSION['message'] = 'Post deleted successfully.';
    $_SESSION['message_type'] = 'success';
} else {
    // Set an error message
    $_SESSION['message'] = 'Error deleting post.';
    $_SESSION['message_type'] = 'danger';
}

// Redirect to the homepage
header('Location: index.php');
exit;
?>