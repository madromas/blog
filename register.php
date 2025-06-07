<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Валидация
    if (empty($username)) {
        $errors[] = 'User name is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'The user name must be at least 3 characters long.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Incorrect email address';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    
    if ($password !== $password_confirm) {
        $errors[] = 'Passwords do not match';
    }
    
    // Проверка уникальности username и email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $errors[] = 'A user with that name or email already exists';
    }
    
    // Если ошибок нет - регистрируем пользователя
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $avatar = 'default.png';
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, avatar, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$username, $email, $hashed_password, $avatar]);
        
        // Автоматический вход после регистрации
        $user_id = $pdo->lastInsertId();
        $_SESSION['user_id'] = $user_id;
        
        // Проверка достижения "registered"
        checkAchievement($user_id, 'registered');
        
        header('Location: index.php');
        exit;
    }
}

$page_title = 'Registration';
include 'includes/header.php';
?>



<div class="auth-form">
    <h1>Registration</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="username">User name</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Password confirmation</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    
    <div class="auth-links">
        Already have account? <a href="login.php">Login</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>