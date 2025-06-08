<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is an admin
if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

// Get user ID from GET parameter
$user_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user data
$user = getUser($user_id);

if (!$user) {
    // User not found
    $_SESSION['error'] = "User not found.";
    header("Location: admin.php?tab=users");
    exit;
}

$page_title = 'Edit User';
include 'includes/header.php';
?>

<style>
    .admin-edit-user {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .admin-edit-user h1 {
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: var(--border-radius);
        background-color: var(--darker-bg);
        color: var(--text-primary);
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-group input[type="text"]:focus,
    .form-group input[type="email"]:focus,
    .form-group textarea:focus {
        border-color: var(--accent-green);
        outline: none;
    }

    textarea {
        min-height: 100px;
        resize: vertical;
    }

    input[type="submit"] {
        background: var(--accent-gradient);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: var(--border-radius);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
        opacity: 0.9;
    }

    .alert {
        padding: 15px;
        margin-bottom: 15px;
        border-radius: var(--border-radius);
    }

    .alert-success {
        background-color: rgba(76, 175, 80, 0.1);
        color: var(--accent-green);
        border-left: 4px solid var(--accent-green);
    }

    .alert-danger {
        background-color: rgba(244, 67, 54, 0.1);
        color: #f44336;
        border-left: 4px solid #f44336;
    }

    .alert-danger ul {
        margin: 10px 0 0 20px;
    }
</style>

<div class="admin-edit-user">
    <h1>Edit User: <?= htmlspecialchars($user['username']) ?></h1>

    <?php
    // Display errors from the session, if any
    if (isset($_SESSION['errors'])) {
        echo '<div class="alert alert-danger">';
        echo '<ul>';
        foreach ($_SESSION['errors'] as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
        unset($_SESSION['errors']); // Clear the errors
    }

    // Display success message from the session, if any
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
        unset($_SESSION['success']); // Clear the success message
    }
    ?>

    <form action="admin.php?tab=users" method="POST">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <input type="hidden" name="update_user" value="1"> <!-- Flag to identify the update form -->
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label for="about">About</label>
            <textarea id="about" name="about"><?= htmlspecialchars($user['about']) ?></textarea>
        </div>

        <input type="submit" value="Update User">
    </form>
</div>

<?php include 'includes/footer.php'; ?>