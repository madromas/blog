<?php
// admin_update_settings.php
require_once 'includes/config.php'; // Ensure this file defines SITE_NAME, SITE_URL constants or is loading settings from a file
require_once 'includes/functions.php';
 

// Проверка прав администратора (Check admin rights)
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize the input
    $site_name = filter_var($_POST['site_name'], FILTER_SANITIZE_STRING);
    $site_url = filter_var($_POST['site_url'], FILTER_SANITIZE_URL);
    $posts_per_page = filter_input(INPUT_POST, 'posts_per_page', FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 50)));
    $stories_lifetime = filter_input(INPUT_POST, 'stories_lifetime', FILTER_VALIDATE_INT, array("options" => array("min_range" => 1, "max_range" => 168)));
    $default_user_role = filter_var($_POST['default_user_role'], FILTER_SANITIZE_STRING);

    // Validate that required fields are not empty
    if (empty($site_name) || empty($site_url) || $posts_per_page === false || $stories_lifetime === false || empty($default_user_role)) {
        $_SESSION['error'] = 'All fields are required and must be valid.'; // Use SESSION for error messages
        header('Location: admin.php?tab=settings');
        exit; // Stop execution if validation fails
    }

    try {
        // Prepare the SQL statement to update the settings
        $stmt = $pdo->prepare("
            UPDATE settings
            SET site_name = :site_name,
                site_url = :site_url,
                posts_per_page = :posts_per_page,
                stories_lifetime = :stories_lifetime,
                default_user_role = :default_user_role
            WHERE id = 1  -- Assuming you have a settings row with id=1
        ");

        // Bind the parameters
        $stmt->bindParam(':site_name', $site_name, PDO::PARAM_STR);
        $stmt->bindParam(':site_url', $site_url, PDO::PARAM_STR);
        $stmt->bindParam(':posts_per_page', $posts_per_page, PDO::PARAM_INT);
        $stmt->bindParam(':stories_lifetime', $stories_lifetime, PDO::PARAM_INT);
        $stmt->bindParam(':default_user_role', $default_user_role, PDO::PARAM_STR);

        // Execute the statement
        if ($stmt->execute()) {
            // Success message (optional)
            $_SESSION['success'] = 'Settings have been updated successfully'; // Use SESSION for success messages
        } else {
            // Error message
            $_SESSION['error'] = 'Error updating settings.'; // Use SESSION for error messages
        }
    } catch (PDOException $e) {
        // Handle database errors
        error_log("Database error updating settings: " . $e->getMessage());
        $_SESSION['error'] = 'Database error: ' . htmlspecialchars($e->getMessage()); // Use SESSION for error messages
    }

} else {
    // If the form was not submitted, set an error and redirect
    $_SESSION['error'] = 'Invalid request.';
}

header('Location: admin.php?tab=settings');
exit;
?>