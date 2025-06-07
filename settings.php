<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = getUser($user_id);
$errors = [];
$success = '';

// Обработка изменения основной информации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $about = sanitize($_POST['about']);
    
    // Валидация
    if (empty($username)) {
        $errors[] = 'Имя пользователя обязательно';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Имя пользователя должно быть не менее 3 символов';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }
    
    // Проверка уникальности username и email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->execute([$username, $email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = 'Пользователь с таким именем или email уже существует';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, about = ? WHERE id = ?");
        $stmt->execute([$username, $email, $about, $user_id]);
        
        $_SESSION['username'] = $username;
        $success = 'Профиль успешно обновлен';
        $user = getUser($user_id); // Обновляем данные пользователя
    }
}

// Обработка смены пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Проверка текущего пароля
    if (!password_verify($current_password, $user['password'])) {
        $errors[] = 'Текущий пароль неверен';
    }
    
    if (empty($new_password)) {
        $errors[] = 'Новый пароль обязателен';
    } elseif (strlen($new_password) < 6) {
        $errors[] = 'Новый пароль должен быть не менее 6 символов';
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed_password, $user_id]);
        $success = 'Пароль успешно изменен';
    }
}

// Обработка загрузки аватара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar']) && isset($_FILES['avatar'])) {
    $avatar = uploadImage($_FILES['avatar']);
    
    if ($avatar) {
        // Удаляем старый аватар, если он не дефолтный
        if ($user['avatar'] !== 'default.png' && file_exists(UPLOAD_DIR . $user['avatar'])) {
            unlink(UPLOAD_DIR . $user['avatar']);
        }
        
        $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?")->execute([$avatar, $user_id]);
        $success = 'Аватар успешно обновлен';
        $user = getUser($user_id); // Обновляем данные пользователя
    } else {
        $errors[] = 'Не удалось загрузить аватар. Проверьте формат и размер файла';
    }
}

// Обработка удаления аватара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_avatar'])) {
    if ($user['avatar'] !== 'default.png' && file_exists(UPLOAD_DIR . $user['avatar'])) {
        unlink(UPLOAD_DIR . $user['avatar']);
    }
    
    $pdo->prepare("UPDATE users SET avatar = 'default.png' WHERE id = ?")->execute([$user_id]);
    $success = 'Аватар удален';
    $user = getUser($user_id); // Обновляем данные пользователя
}

$page_title = 'Настройки профиля';
include 'includes/header.php';
?>

<style>
    /* Settings Page */
    .settings-page {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .settings-page h1 {
        margin-bottom: 20px;
    }
    
    /* Tabs */
    .settings-tabs {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    
    .tab-header {
        display: flex;
        border-bottom: 1px solid #333;
    }
    
    .tab-link {
        padding: 15px 20px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-secondary);
        transition: var(--transition);
        position: relative;
    }
    
    .tab-link:hover {
        color: var(--text-primary);
        background-color: rgba(76, 175, 80, 0.1);
    }
    
    .tab-link.active {
        color: var(--accent-green);
    }
    
    .tab-link.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--accent-gradient);
    }
    
    .tab-content {
        padding: 20px;
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    /* Forms */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="password"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px 15px;
        border-radius: var(--border-radius);
        background-color: var(--darker-bg);
        border: 1px solid #333;
        color: var(--text-primary);
        font-size: 1rem;
        transition: var(--transition);
    }
    
    .form-group input[type="text"]:focus,
    .form-group input[type="email"]:focus,
    .form-group input[type="password"]:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        border-color: var(--accent-green);
        outline: none;
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    input[type="submit"] {
        background: var(--accent-gradient);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: var(--border-radius);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }
    
    input[type="submit"]:hover {
        opacity: 0.9;
    }
    
    /* Avatar Settings */
    .avatar-settings {
        display: flex;
        gap: 30px;
    }
    
    .current-avatar, .upload-avatar {
        flex: 1;
    }
    
    .avatar-large {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--accent-green);
    }
    
    .upload-avatar h3, .current-avatar h3 {
        margin-top: 0;
        margin-bottom: 15px;
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    
    .checkbox-label input {
        margin-right: 10px;
    }
    
    /* Alerts */
    .alert {
        padding: 15px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
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
    
    /* Responsive */
    @media (max-width: 768px) {
        .tab-header {
            flex-wrap: wrap;
        }
        
        .tab-link {
            flex: 1 0 50%;
        }
        
        .avatar-settings {
            flex-direction: column;
            gap: 20px;
        }
        
        .current-avatar {
            text-align: center;
        }
    }
</style>

<div class="settings-page">
    <h1>Настройки профиля</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="settings-tabs">
        <div class="tab-header">
            <button class="tab-link active" onclick="openTab(event, 'profile')">Профиль</button>
            <button class="tab-link" onclick="openTab(event, 'password')">Пароль</button>
            <button class="tab-link" onclick="openTab(event, 'avatar')">Аватар</button>
            <button class="tab-link" onclick="openTab(event, 'privacy')">Приватность</button>
        </div>
        
        <!-- Вкладка профиля -->
        <div id="profile" class="tab-content active">
            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label for="username">Имя пользователя</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="about">О себе</label>
                    <textarea id="about" name="about" rows="4"><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
                </div>
                
                <input type="submit" name="update_profile" value="Сохранить изменения" class="btn btn-primary">
            </form>
        </div>
        
        <!-- Вкладка смены пароля -->
        <div id="password" class="tab-content">
            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label for="current_password">Текущий пароль</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Новый пароль</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Подтвердите новый пароль</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <input type="submit" name="change_password" value="Изменить пароль" class="btn btn-primary">
            </form>
        </div>
        
        <!-- Вкладка аватара -->
        <div id="avatar" class="tab-content">
            <div class="avatar-settings">
                <div class="current-avatar">
                    <h3>Текущий аватар</h3>
                    <img src="<?= SITE_URL ?>/uploads/<?= $user['avatar'] ?>" alt="Аватар" class="avatar-large">
                    
                    <form action="settings.php" method="POST" style="margin-top: 20px;">
                        <button type="submit" name="delete_avatar" class="btn btn-danger">Удалить аватар</button>
                    </form>
                </div>
                
                <div class="upload-avatar">
                    <h3>Загрузить новый аватар</h3>
                    <form action="settings.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="avatar">Выберите изображение (JPG, PNG, max 5MB)</label>
                            <input type="file" id="avatar" name="avatar" accept="image/*" required>
                        </div>
                        
                        <input type="submit" name="upload_avatar" value="Загрузить" class="btn btn-primary">
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Вкладка приватности -->
        <div id="privacy" class="tab-content">
            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="show_email" <?= $user['show_email'] ? 'checked' : '' ?>> 
                        Показывать email в профиле
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_private_messages" <?= $user['allow_private_messages'] ? 'checked' : '' ?>> 
                        Разрешить личные сообщения
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="show_online_status" <?= $user['show_online_status'] ? 'checked' : '' ?>> 
                        Показывать статус онлайн
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="profile_visibility">Видимость профиля</label>
                    <select id="profile_visibility" name="profile_visibility">
                        <option value="public" <?= $user['profile_visibility'] == 'public' ? 'selected' : '' ?>>Публичный</option>
                        <option value="registered" <?= $user['profile_visibility'] == 'registered' ? 'selected' : '' ?>>Только зарегистрированные</option>
                        <option value="private" <?= $user['profile_visibility'] == 'private' ? 'selected' : '' ?>>Приватный</option>
                    </select>
                </div>
                
                <input type="submit" name="update_privacy" value="Сохранить настройки" class="btn btn-primary">
            </form>
        </div>
    </div>
</div>

<script>
function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    
    // Скрываем все вкладки
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].classList.remove("active");
    }
    
    // Удаляем активный класс у всех кнопок
    tablinks = document.getElementsByClassName("tab-link");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active");
    }
    
    // Показываем текущую вкладку и делаем кнопку активной
    document.getElementById(tabName).classList.add("active");
    evt.currentTarget.classList.add("active");
}
</script>

<?php include 'includes/footer.php'; ?>