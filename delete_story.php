<?php
require_once 'includes/config.php';
require_once 'includes/functions.php'; require_once 'includes/auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['story_id'])) {
    $story_id = (int)$_POST['story_id'];

    // Check Permissions and Get Story Data
    $stmt = $pdo->prepare("SELECT user_id, image FROM stories WHERE id = ?");
    $stmt->execute([$story_id]);
    $story = $stmt->fetch();

    if ($story && ($story['user_id'] == $_SESSION['user_id'] || getUser($_SESSION['user_id'])['role'] == 'admin' || getUser($_SESSION['user_id'])['role'] == 'moderator')) {

        // **IMAGE DELETION LOGIC STARTS HERE**
        $image_filename = $story['image'];

        // Delete the image file
        if (!empty($image_filename)) {
            $image_path = 'uploads/' . $image_filename; // Adjust the path if necessary

            // Check if the file exists before attempting to delete it
            if (file_exists($image_path)) {
                // Try to delete the file and handle errors
                if (unlink($image_path)) {
                    // File deleted successfully
                    $_SESSION['message'] = "Story and image deleted successfully.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    // Error deleting the file
                    $_SESSION['message'] = "Story deleted, but error deleting image.";
                    $_SESSION['message_type'] = 'warning';
                }
            } else {
                // File doesn't exist
                $_SESSION['message'] = "Story deleted, but image not found.";
                $_SESSION['message_type'] = 'warning';
            }
        }

        // Delete the story from the database
        $stmt = $pdo->prepare("DELETE FROM stories WHERE id = ?");
        $stmt->execute([$story_id]);

        if ($stmt->rowCount() > 0) {
            // Story deleted successfully
            $_SESSION['message'] = 'Story deleted successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            // Story deletion failed
            $_SESSION['message'] = 'Error deleting story.';
            $_SESSION['message_type'] = 'danger';
        }

    } else {
        $_SESSION['message'] = 'You do not have permission to delete this story.';
        $_SESSION['message_type'] = 'danger';
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'stories.php'));
exit;
?>