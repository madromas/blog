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
    $story_id = (int)$_GET['id']; // Sanitize ID

    // 1. Get the story data (including the image filename)
    $stmt = $pdo->prepare("SELECT image FROM stories WHERE id = ?");
    $stmt->execute([$story_id]);
    $story = $stmt->fetch();

    if ($story) {
        $image_filename = $story['image'];

        // 2. Delete the image file
        if (!empty($image_filename)) {
            $image_path = 'uploads/' . $image_filename; // Adjust the path if necessary

            // Check if the file exists before attempting to delete it
            if (file_exists($image_path)) {
                // Try to delete the file and handle errors
                if (unlink($image_path)) {
                    // File deleted successfully
                    $_SESSION['message'] = "Story image and story deleted successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    // Error deleting the file
                    $_SESSION['message'] = "Story deleted, but error deleting image.";
                    $_SESSION['message_type'] = 'warning';
                }
            } else {
                // File doesn't exist
                $_SESSION['message'] = "Image associated with story not found, story deleted successfully";
                $_SESSION['message_type'] = 'warning';
            }
        }

        // 3. Delete the story from the database
        $stmt = $pdo->prepare("DELETE FROM stories WHERE id = ?");
        $stmt->execute([$story_id]);

        if ($stmt->rowCount() > 0) {
            // Story deleted successfully
            $_SESSION['message'] = "Story deleted successfully.";
            $_SESSION['message_type'] = 'success'; // For displaying message in admin.php
        } else {
            // Story deletion failed
            $_SESSION['message'] = "Error deleting story.";
            $_SESSION['message_type'] = 'danger'; // For displaying message in admin.php
        }

    } else {
        $_SESSION['message'] = "Story not found.";
        $_SESSION['message_type'] = 'danger';
    }
} else {
    $_SESSION['message'] = "Invalid story ID.";
    $_SESSION['message_type'] = 'danger';
}

header('Location: admin.php?tab=stories'); // Redirect back to the stories tab
exit;
?>