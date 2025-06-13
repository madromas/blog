<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php';

// Check admin rights
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

// Debugging: Log the entire $_POST array
error_log("POST Data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['update_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $about = sanitize($_POST['about']);
    $role = in_array($_POST['role'], ['user', 'moderator', 'admin']) ? $_POST['role'] : 'user';

    // Validate input
    $errors = [];
    if ($user_id <= 0) {
        $errors[] = "Invalid user ID.";
    }
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    // Cannot change own role
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'You cannot change your own role.';
        header('Location: admin.php?tab=users');
        exit;
    }

    if (empty($errors)) {
        try {
            // Check if PDO is defined
            if (!isset($pdo)) {
                error_log("PDO connection is not defined!");
                $_SESSION['error'] = "Database connection error. Please check the logs.";
                header("Location: admin_edit_user.php?id=" . $user_id);
                exit;
            }

            // Update user data including username, email, about and role
            $stmt = $pdo->prepare("
                UPDATE users 
                SET username = ?, 
                    email = ?, 
                    about = ?,
                    role = ?
                WHERE id = ?
            ");
            $stmt->execute([$username, $email, $about, $role, $user_id]);

            // Check if any rows were affected
            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = 'The user has been successfully updated.';
            } else {
                // No rows were updated (user not found, or data was the same)
                $_SESSION['success'] = 'The user information was the same, nothing to update.';
            }

            header('Location: admin_edit_user.php?id=' . $user_id); // Redirect to edit page
            exit;

        } catch (PDOException $e) {
            error_log("Database error updating user: " . $e->getMessage());
            $_SESSION['error'] = 'Database error: ' . htmlspecialchars($e->getMessage());
            header('Location: admin_edit_user.php?id=' . $user_id); // Redirect to edit page
            exit;
        }
    } else {
        $_SESSION['errors'] = $errors;
        header('Location: admin_edit_user.php?id=' . $user_id); // Redirect to edit page
        exit;
    }

} else {
    // If the form was not submitted correctly, redirect to the user list
    header('Location: admin.php?tab=users');
    exit;
}
?>