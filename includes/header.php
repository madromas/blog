<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    <link rel="stylesheet" href="/assets/css/style.css">
    <title><?= isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME ?></title>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"
/>
<style>
        :root {
            --dark-bg: #121212;
            --darker-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --accent-green: #4CAF50;
            --accent-green-dark: #388E3C;
            --accent-gradient: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            color: var(--text-primary);
            text-decoration: none;
            transition: var(--transition);
        }

        a:hover {
            color: var(--accent-green);
        }

        /* Header Styles */
        .mobile-header {
            display: none;
            background-color: var(--darker-bg);
            padding: 10px 0;
            border-bottom: 1px solid #333;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .mobile-header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 15px;
        }

        .menu-toggle, .menu-close {
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .menu-toggle:hover, .menu-close:hover {
            color: var(--accent-green);
        }

        .mobile-logo img {
            height: 30px;
        }

        .mobile-user .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .mobile-login-btn {
            color: var(--text-primary);
            font-size: 1.2rem;
        }

        /* Main Header */
        .main-header {
            background-color: var(--darker-bg);
            padding: 15px 0;
            border-bottom: 1px solid #333;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .header-logo img {
            height: 40px;
        }

        .header-nav .nav-list {
            display: flex;
            list-style: none;
        }

        .nav-item {
            margin: 0 10px;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: var(--border-radius);
        }

        .nav-item a:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }

        .nav-item i {
            margin-right: 8px;
            color: var(--accent-green);
        }

        /* Auth Styles */
        .header-auth .btn {
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-login {
            background: transparent;
            border: 1px solid var(--accent-green);
            color: var(--accent-green);
            margin-right: 10px;
        }

        .btn-login:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }

        .btn-register {
            background: var(--accent-gradient);
            border: none;
            color: white;
        }

        .btn-register:hover {
            opacity: 0.9;
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-trigger {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .user-trigger:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }

        .user-trigger .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
        }

        .username {
            margin-right: 8px;
            font-weight: 600;
        }

        .dropdown-arrow {
            font-size: 0.8rem;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            width: 200px;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            z-index: 100;
        }

        .user-dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px 15px;
            border-bottom: 1px solid #333;
        }

        .dropdown-menu a:hover {
            background-color: rgba(76, 175, 80, 0.1);
        }

        .dropdown-menu i {
            margin-right: 10px;
            color: var(--accent-green);
            width: 20px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background-color: #333;
            margin: 5px 0;
        }

        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: -100%;
            width: 80%;
            max-width: 300px;
            height: 100vh;
            background-color: var(--darker-bg);
            z-index: 1000;
            transition: var(--transition);
            overflow-y: auto;
        }

        .mobile-menu.active {
            left: 0;
        }

        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #333;
        }

        .mobile-user-info {
            display: flex;
            align-items: center;
        }

        .mobile-user-info .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .mobile-auth-buttons {
            display: flex;
        }

        .mobile-auth-buttons .btn {
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .mobile-nav ul {
            list-style: none;
        }

        .mobile-nav li a {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #333;
        }

        .mobile-nav li a i {
            margin-right: 15px;
            color: var(--accent-green);
            width: 20px;
            text-align: center;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 768px) {
            .mobile-header {
                display: block;
            }
            
            .main-header {
                display: none;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script src="/assets/js/script.js"></script>
</head>
<body>
    <script>
        Fancybox.bind("[data-fancybox]", {
});
</script>
    <!-- Mobile Header (виден только на мобильных) -->
    <div class="mobile-header">
        <div class="mobile-header-container">
            <button class="menu-toggle" aria-label="Открыть меню">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="mobile-logo">
                <a href="<?= SITE_URL ?>">
                    <img src="<?= SITE_URL ?>/assets/images/logo-mobile.png" alt="<?= SITE_NAME ?>">
                </a>
            </div>
            
            <div class="mobile-user">
                <?php if (isLoggedIn()): ?>
                    <a href="<?= SITE_URL ?>/profile.php?id=<?= $_SESSION['user_id'] ?>" class="mobile-avatar">
                        <img src="<?= SITE_URL ?>/uploads/<?= getUser($_SESSION['user_id'])['avatar'] ?? 'default.png' ?>" alt="Аватар" class="avatar">
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php" class="mobile-login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Основной Header (скрывается на мобильных) -->
    <header class="main-header">
        <div class="header-container">
            <div class="header-logo">
                <a href="<?= SITE_URL ?>">
                    <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="<?= SITE_NAME ?>">
                </a>
            </div>
            
            <nav class="header-nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Home</a></li>
                    <li class="nav-item"><a href="<?= SITE_URL ?>/create.php"><i class="fas fa-plus-circle"></i> Create</a></li>
                    <li class="nav-item"><a href="<?= SITE_URL ?>/search.php"><i class="fas fa-search"></i> Search</a></li>
                    
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item"><a href="<?= SITE_URL ?>/stories.php"><i class="fas fa-history"></i> Stories</a></li>
                        <li class="nav-item"><a href="<?= SITE_URL ?>/messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                        <?php if (hasPermission('moderator')): ?>
                            <li class="nav-item"><a href="<?= SITE_URL ?>/admin.php"><i class="fas fa-shield-alt"></i> Moderation</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="header-auth">
                <?php if (isLoggedIn()): ?>
                    <div class="user-dropdown">
                        <div class="user-trigger">
                            <img src="<?= SITE_URL ?>/uploads/<?= getUser($_SESSION['user_id'])['avatar'] ?? 'default.png' ?>" alt="Avatar" class="avatar">
                            <span class="username"><?= getUser($_SESSION['user_id'])['username'] ?></span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </div>
                        <div class="dropdown-menu">
                            <a href="<?= SITE_URL ?>/profile.php?id=<?= $_SESSION['user_id'] ?>"><i class="fas fa-user"></i> Profile</a>
                            <a href="<?= SITE_URL ?>/subscriptions.php"><i class="fas fa-users"></i> Subscriptions</a>
                            <a href="<?= SITE_URL ?>/settings.php"><i class="fas fa-cog"></i> Settings</a>
                            <?php if (hasPermission('moderator')): ?>
                                <a href="<?= SITE_URL ?>/admin.php"><i class="fas fa-tools"></i> Admin-panel</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="<?= SITE_URL ?>/login.php" class="btn btn-login">Login</a>
                        <a href="<?= SITE_URL ?>/register.php" class="btn btn-register">Registration</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Mobile Menu (открывается по клику) -->
    <div class="mobile-menu">
        <div class="mobile-menu-header">
            <?php if (isLoggedIn()): ?>
                <div class="mobile-user-info">
                    <img src="<?= SITE_URL ?>/uploads/<?= getUser($_SESSION['user_id'])['avatar'] ?? 'default.png' ?>" alt="Avatar" class="avatar">
                    <span><?= getUser($_SESSION['user_id'])['username'] ?></span>
                </div>
            <?php else: ?>
                <div class="mobile-auth-buttons">
                    <a href="<?= SITE_URL ?>/login.php" class="btn btn-login">Login</a>
                    <a href="<?= SITE_URL ?>/register.php" class="btn btn-register">Registration</a>
                </div>
            <?php endif; ?>
            <button class="menu-close"><i class="fas fa-times"></i></button>
        </div>
        
        <nav class="mobile-nav">
            <ul>
                <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="<?= SITE_URL ?>/create.php"><i class="fas fa-plus-circle"></i> Create post</a></li>
                <li><a href="<?= SITE_URL ?>/search.php"><i class="fas fa-search"></i> Search</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?= SITE_URL ?>/stories.php"><i class="fas fa-history"></i> Stories</a></li>
                    <li><a href="<?= SITE_URL ?>/messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="<?= SITE_URL ?>/profile.php?id=<?= $_SESSION['user_id'] ?>"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="<?= SITE_URL ?>/subscriptions.php"><i class="fas fa-users"></i> Subscriptions</a></li>
                    <li><a href="<?= SITE_URL ?>/settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <?php if (hasPermission('moderator')): ?>
                        <li><a href="<?= SITE_URL ?>/admin.php"><i class="fas fa-shield-alt"></i> Moderation</a></li>
                    <?php endif; ?>
                    <li><a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <div class="overlay"></div>

    <main class="container">