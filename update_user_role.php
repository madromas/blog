<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';  

if (!isLoggedIn() || getUser($_SESSION['user_id'])['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = in_array($_POST['role'], ['user', 'moderator', 'admin']) ? $_POST['role'] : 'user';
    
    $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $user_id]);
}

header('Location: admin.php?tab=users');
exit;
?>