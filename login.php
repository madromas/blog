<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Валидация
    if (empty($email)) {
        $errors[] = 'Email required';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль required';
    }
    
    // Проверка учетных данных
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            
            // Проверка достижения "returned" (если есть last_login)
            if (isset($user['last_login']) && $user['last_login']) {
                $last_login = strtotime($user['last_login']);
                if ($last_login && (time() - $last_login) > 86400) {
                    checkAchievement($_SESSION['user_id'], 'returned');
                }
            }
            
            // Обновление времени последнего входа
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$_SESSION['user_id']]);
            
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
}

$page_title = 'Login';
include 'includes/header.php';
?>

<style>
    /* Auth Form */
    .auth-form {
        max-width: 450px;
        margin: 30px auto;
        padding: 30px;
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
    
    .auth-form h1 {
        text-align: center;
        margin-bottom: 25px;
    }
    
    /* Alert */
    .alert-danger {
        background-color: rgba(244, 67, 54, 0.1);
        color: #f44336;
        padding: 15px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
        border-left: 4px solid #f44336;
    }
    
    .alert-danger ul {
        margin: 10px 0 0 20px;
    }
    
    /* Form Group */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border-radius: var(--border-radius);
        background-color: var(--darker-bg);
        border: 1px solid #333;
        color: var(--text-primary);
        font-size: 1rem;
        transition: var(--transition);
    }
    
    .form-group input:focus {
        border-color: var(--accent-green);
        outline: none;
    }
    
    /* Button */
    
    .btn-primary {
        background: var(--accent-gradient);
        color: white;
        border: none;
    }
    
    .btn-primary:hover {
        opacity: 0.9;
    }
    
    /* Auth Links */
    .auth-links {
        text-align: center;
        margin-top: 20px;
        color: var(--text-secondary);
    }
    
    .auth-links a {
        color: var(--accent-green);
        font-weight: 600;
    }
    
    .auth-links a:hover {
        text-decoration: underline;
    }
    
    @media (max-width: 768px) {
        .auth-form {
            padding: 20px;
            margin: 20px 15px;
        }
    }
</style>

<div class="auth-form">
    <h1>Login</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    
    <div class="auth-links">
        Don't have an account? <a href="register.php">Register</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
