<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';  

// Check if the user is logged in
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];

    // Delete the "remember_me" cookie
    setcookie('remember_me', '', time() - 3600, '/'); // Invalidate the cookie

    // Delete the token from the database
    $stmt = $pdo->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Destroy the session
    session_destroy();
}

header('Location: index.php');
exit;
?>