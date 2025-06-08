<?php
// admin_delete_post.php

require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $post_id = (int)$_GET['id'];

    // 1. Get the post data (including the image filename)
    $stmt = $pdo->prepare("SELECT image FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if ($post) {
        $image_filename = $post['image'];

        // 2. Delete the image file
        if (!empty($image_filename)) {
            $image_path = 'uploads/' . $image_filename; // Adjust the path if necessary

            // Check if the file exists before attempting to delete it
            if (file_exists($image_path)) {
                // Try to delete the file and handle errors
                if (unlink($image_path)) {
                    // File deleted successfully
                    $_SESSION['message'] = "Post image and post deleted successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    // Error deleting the file
                    $_SESSION['message'] = "Post deleted, but error deleting image.";
                    $_SESSION['message_type'] = 'warning';
                }
            } else {
                // File doesn't exist
                $_SESSION['message'] = "Image associated with post not found, post deleted successfully";
                $_SESSION['message_type'] = 'warning';
            }
        }

        // 3. Delete the post from the database
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);

        $_SESSION['success'] = 'Post deleted successfully.';
    } else {
        $_SESSION['error'] = 'Post not found.';
    }
} else {
    $_SESSION['error'] = 'Invalid post ID.';
}

header('Location: admin.php?tab=posts');
exit;
?>