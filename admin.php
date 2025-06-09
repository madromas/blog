<?php
ob_start(); // Start output buffering
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    header('Location: index.php');
    exit;
}

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
$allowed_tabs = ['dashboard', 'users', 'posts', 'comments', 'stories', 'reports', 'settings'];

if (!in_array($current_tab, $allowed_tabs)) {
    $current_tab = 'dashboard';
}

// Check for the edit post action from admin_edit_post.php
if (isset($_SESSION['admin_tab']) && $_SESSION['admin_tab'] === 'edit_post' && isset($_SESSION['edit_post_id'])) {
    $current_tab = 'posts';  // Ensure the posts tab is active
    $edit_post_id = (int)$_SESSION['edit_post_id'];  // Get the post ID
    unset($_SESSION['admin_tab']); // Clean session variables to prevent accidental re-display
    unset($_SESSION['edit_post_id']);
} else {
    $edit_post_id = 0; //No edit post, so set to 0.
}


switch ($current_tab) {
    case 'dashboard':
        $stats = [
            'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'posts' => $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
            'comments' => $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
            'stories' => $pdo->query("SELECT COUNT(*) FROM stories")->fetchColumn(),
            'reports' => $pdo->query("SELECT COUNT(*) FROM content_reports WHERE status = 'pending'")->fetchColumn()
        ];
        break;

    case 'users':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $users = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT $offset, $per_page")->fetchAll();
        $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $total_pages = ceil($total_users / $per_page);
        break;

    case 'posts':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $posts = $pdo->query("
            SELECT p.*, u.username 
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC 
            LIMIT $offset, $per_page
        ")->fetchAll();

        $total_posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
        $total_pages = ceil($total_posts / $per_page);
        break;

    case 'comments':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = 25;
        $offset = ($page - 1) * $per_page;

        $comments = $pdo->query("
            SELECT c.*, u.username, p.title as post_title 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            LEFT JOIN posts p ON c.post_id = p.id
            ORDER BY c.created_at DESC 
            LIMIT $offset, $per_page
        ")->fetchAll();

        $total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
        $total_pages = ceil($total_comments / $per_page);
        break;

    case 'stories':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $stories = $pdo->query("
            SELECT s.*, u.username 
            FROM stories s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC 
            LIMIT $offset, $per_page
        ")->fetchAll();

        $total_stories = $pdo->query("SELECT COUNT(*) FROM stories")->fetchColumn();
        $total_pages = ceil($total_stories / $per_page);
        break;

    case 'reports':
        $reports = $pdo->query("
            SELECT r.*, 
                   u1.username as reporter_name, 
                   u2.username as reported_user_name,
                   p.title as post_title,
                   c.content as comment_content,
                   s.content as story_content
            FROM content_reports r
            LEFT JOIN users u1 ON r.reporter_id = u1.id
            LEFT JOIN users u2 ON (
                SELECT user_id FROM posts WHERE id = r.post_id
                UNION
                SELECT user_id FROM comments WHERE id = r.comment_id
                UNION
                SELECT user_id FROM stories WHERE id = r.story_id
            ) = u2.id
            LEFT JOIN posts p ON r.post_id = p.id
            LEFT JOIN comments c ON r.comment_id = c.id
            LEFT JOIN stories s ON r.story_id = s.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at DESC
        ")->fetchAll();
        break;
}

$page_title = 'Admin-panel';
include 'includes/header.php';
?>

<style>
    /* Admin Panel Styles */
    .admin-container {
        display: flex;
        min-height: calc(100vh - 150px);
        margin: 20px 0;
    }

    .admin-sidebar {
        width: 250px;
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 20px;
        margin-right: 20px;
        box-shadow: var(--shadow);
    }

    .admin-profile {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid #333;
    }

    .admin-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--accent-green);
        margin-bottom: 10px;
    }

    .admin-info {
        text-align: center;
    }

    .admin-info h3 {
        margin: 5px 0;
        font-size: 1.1rem;
    }

    .admin-role {
        display: inline-block;
        padding: 3px 8px;
        background-color: rgba(76, 175, 80, 0.2);
        color: var(--accent-green);
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .admin-nav ul {
        list-style: none;
    }

    .admin-nav li {
        margin-bottom: 5px;
    }

    .admin-nav li a {
        display: flex;
        align-items: center;
        padding: 10px;
        border-radius: var(--border-radius);
        color: var(--text-secondary);
    }

    .admin-nav li a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .admin-nav li a:hover {
        background-color: rgba(76, 175, 80, 0.1);
        color: var(--text-primary);
    }

    .admin-nav li.active a {
        background-color: rgba(76, 175, 80, 0.2);
        color: var(--accent-green);
        font-weight: 600;
    }

    .admin-content {
        flex: 1;
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 20px;
        box-shadow: var(--shadow);
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #333;
    }

    .admin-header h1 {
        display: flex;
        align-items: center;
        font-size: 1.5rem;
    }

    .admin-header h1 i {
        margin-right: 10px;
        color: var(--accent-green);
    }

    .admin-actions {
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: var(--border-radius);
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn i {
        margin-right: 5px;
    }

    .btn-primary {
        background: var(--accent-gradient);
        color: white;
    }

    .btn-primary:hover {
        opacity: 0.9;
    }

    .btn-refresh {
        background-color: rgba(76, 175, 80, 0.1);
        color: var(--accent-green);
    }

    .btn-refresh:hover {
        background-color: rgba(76, 175, 80, 0.2);
    }

    .btn-danger {
        background-color: rgba(244, 67, 54, 0.1);
        color: #f44336;
    }

    .btn-danger:hover {
        background-color: rgba(244, 67, 54, 0.2);
    }

    .btn-success {
        background-color: rgba(76, 175, 80, 0.1);
        color: var(--accent-green);
    }

    .btn-success:hover {
        background-color: rgba(76, 175, 80, 0.2);
    }

    .btn-small {
        padding: 5px 10px;
        font-size: 0.8rem;
    }

    .btn-large {
        padding: 10px 20px;
        font-size: 1rem;
    }

    .search-box {
        display: flex;
    }

    .search-box input {
        padding: 8px 12px;
        border: 1px solid #333;
        border-radius: var(--border-radius) 0 0 var(--border-radius);
        background-color: var(--darker-bg);
        color: var(--text-primary);
        outline: none;
    }

    .search-box input:focus {
        border-color: var(--accent-green);
    }

    .btn-search {
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        background-color: var(--accent-green);
        color: white;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-card {
        background-color: var(--darker-bg);
        border-radius: var(--border-radius);
        padding: 15px;
        display: flex;
        align-items: center;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: rgba(76, 175, 80, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: var(--accent-green);
        font-size: 1.2rem;
    }

    .stat-value {
        font-size: 1.3rem;
        font-weight: 700;
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    /* Tables */
    .admin-table-container {
        overflow-x: auto;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table th {
        background-color: var(--darker-bg);
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
    }

    .admin-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #333;
    }

    .admin-table tr:hover {
        background-color: rgba(76, 175, 80, 0.05);
    }

    .user-link {
        display: flex;
        align-items: center;
    }

    .user-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
    }

    .post-link {
        color: var(--accent-green);
    }

    .post-link:hover {
        text-decoration: underline;
    }

    .role-form {
        display: inline-block;
    }

    .role-select {
        padding: 5px 8px;
        border-radius: var(--border-radius);
        background-color: var(--darker-bg);
        color: var(--text-primary);
        border: 1px solid #333;
        outline: none;
    }

    .role-select:focus {
        border-color: var(--accent-green);
    }

    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .story-preview {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 10px;
    }

    /* Reports */
    .reports-list {
        display: grid;
        gap: 15px;
    }

    .report-card {
        background-color: var(--darker-bg);
        border-radius: var(--border-radius);
        padding: 15px;
    }

    .report-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #333;
    }

    .report-id {
        font-weight: 600;
        color: var(--accent-green);
    }

    .report-date {
        color: var(--text-secondary);
        font-size: 0.8rem;
    }

    .report-section {
        margin-bottom: 10px;
    }

    .section-title {
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--accent-green);
    }

    .report-users {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin: 15px 0;
    }

    .user-card {
        background-color: rgba(0, 0, 0, 0.2);
        padding: 10px;
        border-radius: var(--border-radius);
    }

    .report-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .report-form {
        display: inline-block;
    }

    /* Settings Form */
    .settings-form {
        max-width: 600px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .form-input {
        width: 100%;
        padding: 10px;
        border-radius: var(--border-radius);
        background-color: var(--darker-bg);
        border: 1px solid #333;
        color: var(--text-primary);
        outline: none;
    }

    .form-input:focus {
        border-color: var(--accent-green);
    }

    .form-select {
        width: 100%;
        padding: 10px;
        border-radius: var(--border-radius);
        background-color: var(--darker-bg);
        border: 1px solid #333;
        color: var(--text-primary);
        outline: none;
    }

    .form-select:focus {
        border-color: var(--accent-green);
    }

    .form-actions {
        margin-top: 20px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-icon {
        font-size: 3rem;
        color: var(--accent-green);
        margin-bottom: 15px;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .page-link {
        padding: 8px 12px;
        margin: 0 3px;
        border-radius: var(--border-radius);
        background-color: var(--darker-bg);
        color: var(--text-primary);
        transition: var(--transition);
    }

    .page-link:hover {
        background-color: rgba(76, 175, 80, 0.2);
    }

    .page-link.active {
        background: var(--accent-gradient);
        color: white;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .admin-container {
            flex-direction: column;
        }

        .admin-sidebar {
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .admin-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .admin-actions {
            margin-top: 10px;
            width: 100%;
        }

        .search-box {
            width: 100%;
        }

        .search-box input {
            flex: 1;
        }

        .report-users {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-profile">
            <img src="<?= SITE_URL ?>/uploads/<?= getUser($_SESSION['user_id'])['avatar'] ?>" 
                 alt="Avatar" class="admin-avatar">
            <div class="admin-info">
                <h3><?= htmlspecialchars(getUser($_SESSION['user_id'])['username']) ?></h3>
                <span class="admin-role">
                    <?= getUser($_SESSION['user_id'])['role'] == 'admin' ? 'Administrator' : 'Moderator' ?>
                </span>
            </div>
        </div>

        <nav class="admin-nav">
            <ul>
                <li class="<?= $current_tab == 'dashboard' ? 'active' : '' ?>">
                    <a href="admin.php?tab=dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?= $current_tab == 'users' ? 'active' : '' ?>">
                    <a href="admin.php?tab=users">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="<?= $current_tab == 'posts' ? 'active' : '' ?>">
                    <a href="admin.php?tab=posts">
                        <i class="fas fa-newspaper"></i>
                        <span>Posts</span>
                    </a>
                </li>
                <li class="<?= $current_tab == 'comments' ? 'active' : '' ?>">
                    <a href="admin.php?tab=comments">
                        <i class="fas fa-comments"></i>
                        <span>Comments</span>
                    </a>
                </li>
                <li class="<?= $current_tab == 'stories' ? 'active' : '' ?>">
                    <a href="admin.php?tab=stories">
                        <i class="fas fa-history"></i>
                        <span>Stories</span>
                    </a>
                </li>
                <li class="<?= $current_tab == 'reports' ? 'active' : '' ?>">
                    <a href="admin.php?tab=reports">
                        <i class="fas fa-flag"></i>
                        <span>Complaints</span>
                    </a>
                </li>
                <?php if (hasPermission('admin')): ?>
                    <li class="<?= $current_tab == 'settings' ? 'active' : '' ?>">
                        <a href="admin.php?tab=settings">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <div class="admin-content">

       <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type']) ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <?php if ($current_tab == 'dashboard'): ?>
            <div class="admin-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="admin-actions">
                    <button class="btn btn-refresh">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['users'] ?></div>
                        <div class="stat-label">Users</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['posts'] ?></div>
                        <div class="stat-label">Posts</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['comments'] ?></div>
                        <div class="stat-label">Comments</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['stories'] ?></div>
                        <div class="stat-label">Stories</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stats['reports'] ?></div>
                        <div class="stat-label">Complaints</div>
                    </div>
                </div>
            </div>

        <?php elseif ($current_tab == 'users'): ?>

<?php
            // Determine if we are in "edit user" mode
            $edit_user_id = isset($_GET['edit']) && is_numeric($_GET['edit']) ? (int)$_GET['edit'] : 0;

            // Handle User Editing Submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
                $user_id = (int)$_POST['user_id'];
                $username = sanitize($_POST['username']);
                $email = sanitize($_POST['email']);
                $about = sanitize($_POST['about']);

                $errors = [];

                // Validation
                if (empty($username)) {
                    $errors[] = 'Username is required.';
                } elseif (strlen($username) < 3) {
                    $errors[] = 'Username must be at least 3 characters.';
                }

                if (empty($email)) {
                    $errors[] = 'Email is required.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Invalid email format.';
                }

                // Check for duplicate username/email (excluding current user)
                $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $stmt->execute([$username, $email, $user_id]);
                if ($stmt->fetch()) {
                    $errors[] = 'Username or email already exists.';
                }

                if (empty($errors)) {
                    // Update user in database
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, about = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $about, $user_id]);
                    $_SESSION['success'] = 'User information updated successfully.';
                } else {
                    $_SESSION['errors'] = $errors; // Store errors in session for display
                }

                $edit_user_id = 0; // Reset edit mode
                header('Location: admin.php?tab=users&edit=' . $user_id);
                
            }
            ?>

            <div class="admin-header">
                <h1><i class="fas fa-users"></i> User management</h1>
                <div class="admin-actions">
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
            </div>

<?php if ($edit_user_id > 0): ?>
  <?php
                // Fetch user data
                $user = getUser($edit_user_id);

                if (!$user) {
                    $_SESSION['error'] = "User not found.";
                    header("Location: admin.php?tab=users");
                    exit;
                }
                ?>
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
                        <input type="hidden" name="user_id" value="<?= $edit_user_id ?>">
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

                        <input type="submit" value="Update User" class="btn btn-primary">
                    </form>
                </div>

            <?php else: ?>

            <div class="admin-table-container">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registration date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td>
                                    <a href="profile.php?id=<?= $user['id'] ?>" class="user-link">
                                        <img src="<?= SITE_URL ?>/uploads/<?= $user['avatar'] ?>" 
                                                 alt="Avatar" class="user-avatar">
                                        <span><?= htmlspecialchars($user['username']) ?></span>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <form action="admin_update_user.php" method="POST" class="role-form">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <select name="role" onchange="this.form.submit()"
                                                <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>
                                                class="role-select">
                                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="moderator" <?= $user['role'] == 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrator</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="admin_ban_user.php?id=<?= $user['id'] ?>"
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Are you sure you want to ban this user?')">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="admin.php?tab=users&edit=<?= $user['id'] ?>"
                                       class="btn btn-edit btn-small">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="admin.php?tab=users&page=<?= $page - 1 ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="admin.php?tab=users&page=<?= $i ?>"
                               class="page-link <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="admin.php?tab=users&page=<?= $page + 1 ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php elseif ($current_tab == 'posts'): ?>
            <div class="admin-header">
                <h1><i class="fas fa-newspaper"></i> Posts management</h1>
                <div class="admin-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Search posts...">
                        <button class="btn btn-search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <?php if ($edit_post_id > 0): ?>
    <?php
        $_GET['action'] = 'edit';
        $_GET['id'] = $edit_post_id;
        include 'admin_post_form.php';
    ?>
<?php else: ?>
                <div class="admin-table-container">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?= $post['id'] ?></td>
                                    <td>
                                        <a href="post.php?id=<?= $post['id'] ?>" target="_blank" class="post-link">
                                            <?= htmlspecialchars(substr($post['title'], 0, 50)) ?>
                                            <?= strlen($post['title']) > 50 ? '...' : '' ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="profile.php?id=<?= $post['user_id'] ?>" class="user-link">
                                            <?= htmlspecialchars($post['username']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
                                    <td><?= $post['upvotes'] - $post['downvotes'] ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin_delete_post.php?id=<?= $post['id'] ?>"
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Are you sure you want to delete this post?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="admin_edit_post.php?id=<?= $post['id'] ?>"
                                               class="btn btn-edit btn-small">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="admin.php?tab=posts&page=<?= $page - 1 ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="admin.php?tab=posts&page=<?= $i ?>"
                                   class="page-link <?= $i == $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="admin.php?tab=posts&page=<?= $page + 1 ?>" class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?> <!-- End of the  $edit_post_id  check -->


        <?php elseif ($current_tab == 'comments'): ?>
            <div class="admin-header">
                <h1><i class="fas fa-comments"></i> Comments management</h1>
                <div class="admin-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Comments search...">
                        <button class="btn btn-search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="admin-table-container">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Comment</th>
                                <th>Author</th>
                                <th>To post</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><?= $comment['id'] ?></td>
                                    <td>
                                        <?php
$truncated_content = substr($comment['content'], 0, 50); // Truncate the string
?>
<p><a href="post.php?id=<?= $comment['post_id'] ?>"><?= html_entity_decode(htmlspecialchars($truncated_content)) ?></a>
                                    </td>
                                    <td>
                                        <a href="profile.php?id=<?= $comment['user_id'] ?>" class="user-link">
                                            <?= htmlspecialchars($comment['username']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($comment['post_id']): ?>
                                            <a href="post.php?id=<?= $comment['post_id'] ?>" target="_blank" class="post-link">
                                                <?= htmlspecialchars(substr($comment['post_title'], 0, 30)) ?>
                                                <?= strlen($comment['post_title']) > 30 ? '...' : '' ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Deleted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin_delete_comment.php?id=<?= $comment['id'] ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Are you sure you want to delete this comment?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="admin.php?tab=comments&page=<?= $page - 1 ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="admin.php?tab=comments&page=<?= $i ?>" 
                               class="page-link <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="admin.php?tab=comments&page=<?= $page + 1 ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php elseif ($current_tab == 'stories'): ?>
            <div class="admin-header">
                <h1><i class="fas fa-history"></i> Stories management</h1>
                <div class="admin-actions">
                    <button class="btn btn-refresh">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div class="admin-table-container">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Content</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Expired</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stories as $story): ?>
                                <tr>
                                    <td><?= $story['id'] ?></td>
                                    <td>
                                        <?php if ($story['image']): ?>
                                            <img src="<?= SITE_URL ?>/uploads/<?= htmlspecialchars($story['image']) ?>" 
                                                 alt="Story" class="story-preview">
                                        <?php endif; ?>
                                        <?= htmlspecialchars(substr($story['content'], 0, 50)) ?>
                                        <?= strlen($story['content']) > 50 ? '...' : '' ?>
                                    </td>
                                    <td>
                                        <a href="profile.php?id=<?= $story['user_id'] ?>" class="user-link">
                                            <?= htmlspecialchars($story['username']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($story['created_at'])) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($story['expires_at'])) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin_delete_story.php?id=<?= $story['id'] ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Are you sure you want to delete this story?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="admin.php?tab=stories&page=<?= $page - 1 ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="admin.php?tab=stories&page=<?= $i ?>" 
                               class="page-link <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="admin.php?tab=stories&page=<?= $page + 1 ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php elseif ($current_tab == 'reports'): ?>
            <div class="admin-header">
                <h1><i class="fas fa-flag"></i> Content complaints</h1>
                <div class="admin-actions">
                    <button class="btn btn-refresh">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            
            <?php if (empty($reports)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle empty-icon"></i>
                    <h3>No active complaints</h3>
                    <p>All complaints have been processed</p>
                </div>
            <?php else: ?>
                <div class="reports-list">
                    <?php foreach ($reports as $report): ?>
                        <div class="report-card">
                            <div class="report-header">
                                <span class="report-id">Complaint #<?= $report['id'] ?></span>
                                <span class="report-date"><?= date('d.m.Y H:i', strtotime($report['created_at'])) ?></span>
                            </div>
                            
                            <div class="report-content">
                                <div class="report-section">
                                    <div class="section-title">Reason:</div>
                                    <div class="section-content"><?= htmlspecialchars($report['reason']) ?></div>
                                </div>
                                
                                <div class="report-section">
                                    <div class="section-title">Content:</div>
                                    <div class="section-content">
                                        <?php if ($report['post_id']): ?>
                                            <strong>Post:</strong> 
                                            <a href="post.php?id=<?= $report['post_id'] ?>" target="_blank">
                                                <?= htmlspecialchars($report['post_title']) ?>
                                            </a>
                                        <?php elseif ($report['comment_id']): ?>
                                            <strong>Comment:</strong> 
                                            <?= htmlspecialchars(substr($report['comment_content'], 0, 100)) ?>...
                                        <?php elseif ($report['story_id']): ?>
                                            <strong>Story:</strong> 
                                            <?= htmlspecialchars(substr($report['story_content'], 0, 100)) ?>...
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="report-users">
                                    <div class="user-card">
                                        <div class="section-title">Complaint from:</div>
                                        <div class="section-content">
                                            <a href="profile.php?id=<?= $report['reporter_id'] ?>" class="user-link">
                                                <?= htmlspecialchars($report['reporter_name']) ?>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="user-card">
                                        <div class="section-title">Content author:</div>
                                        <div class="section-content">
                                            <a href="profile.php?id=<?= $report['reported_user_id'] ?>" class="user-link">
                                                <?= htmlspecialchars($report['reported_user_name']) ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="report-actions">
                                <form action="admin_process_report.php" method="POST" class="report-form">
                                    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                </form>
                                
                                <form action="admin_process_report.php" method="POST" class="report-form">
                                    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times"></i> Decline
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php elseif ($current_tab == 'settings' && hasPermission('admin')): ?>
            <div class="admin-header">
                <h1><i class="fas fa-cog"></i> Site settings</h1>
            </div>
            
            <form action="admin_update_settings.php" method="POST" class="settings-form">
                <div class="form-group">
                    <label for="site_name" class="form-label">Site name</label>
                    <input type="text" id="site_name" name="site_name" class="form-input" 
                           value="<?= htmlspecialchars(SITE_NAME) ?>">
                </div>
                
                <div class="form-group">
                    <label for="site_url" class="form-label">Site URL</label>
                    <input type="text" id="site_url" name="site_url" class="form-input" 
                           value="<?= htmlspecialchars(SITE_URL) ?>">
                </div>
                
                <div class="form-group">
                    <label for="posts_per_page" class="form-label">Posts on the page</label>
                    <input type="number" id="posts_per_page" name="posts_per_page" class="form-input" 
                           value="10" min="1" max="50">
                </div>
                
                <div class="form-group">
                    <label for="stories_lifetime" class="form-label">Lifetime of stories (hours)</label>
                    <input type="number" id="stories_lifetime" name="stories_lifetime" class="form-input" 
                           value="24" min="1" max="168">
                </div>
                
                <div class="form-group">
                    <label for="default_user_role" class="form-label">The default role for new users</label>
                    <select id="default_user_role" name="default_user_role" class="form-select">
                        <option value="user">User</option>
                        <option value="moderator">Moderator</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-save"></i> Save settings
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>