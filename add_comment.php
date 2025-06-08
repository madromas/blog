<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn() || !isset($_POST['post_id']) || empty($_POST['content'])) {
    header('Location: index.php');
    exit;
}

$post_id = (int)$_POST['post_id'];
$content = sanitize($_POST['content']);
$image_filename = ''; // Initialize image filename

// **FILE UPLOAD HANDLING STARTS HERE**
if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['comment_image'];

    // Validate file type (Example)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['message'] = "Invalid file type. Only JPEG, PNG, and GIF images are allowed.";
        $_SESSION['message_type'] = 'danger';
        header("Location: post.php?id=" . $post_id);
        exit;
    }

    // Validate file size (Example)
    $max_file_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_file_size) {
        $_SESSION['message'] = "File size exceeds the maximum allowed size (2MB).";
        $_SESSION['message_type'] = 'danger';
        header("Location: post.php?id=" . $post_id);
        exit;
    }

    // Generate a unique filename
    $filename = uniqid() . '_' . $file['name'];
    $destination = 'uploads/' . $filename; // Adjust the path if necessary

    // Debugging: Output the destination path
    echo "<p>Destination: " . htmlspecialchars($destination) . "</p>";

    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $image_filename = $filename; // Save the filename for the database
        echo "<p>File uploaded successfully!</p>"; // Debugging
    } else {
        $_SESSION['message'] = "Error uploading file.";
        $_SESSION['message_type'] = 'danger';
        header("Location: post.php?id=" . $post_id);
        exit;
    }
}
// **FILE UPLOAD HANDLING ENDS HERE**

// Insert the new comment with the image filename
$stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, image, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$post_id, $_SESSION['user_id'], $content, $image_filename]);
$comment_id = $pdo->lastInsertId();

// Update comment count
$pdo->prepare("UPDATE posts SET comments_count = comments_count + 1 WHERE id = ?")->execute([$post_id]);

// Check achievement
checkAchievement($_SESSION['user_id'], 'first_comment');

// Get comment data (Not used in this version, but can be useful later)
$commentStmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
$commentStmt->execute([$comment_id]);
$comment = $commentStmt->fetch();

header("Location: post.php?id=$post_id");
exit;
?>