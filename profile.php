<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get the user ID from the URL (or however you're passing it)
$profile_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get the ID of the currently logged-in user (if any)
$current_user_id = isLoggedIn() ? $_SESSION['user_id'] : null;

// Fetch the user profile, respecting privacy settings
$userProfile = getUserProfile($profile_user_id, $current_user_id);

if ($userProfile === null) {
    // User not found or not authorized to view the profile
    http_response_code(404);
    include('includes/error.php');  // Or display a "Profile not found" or "Access denied" message
    exit;
}

$user_id = (int)$_GET['id'];
$user = $userProfile;

if (!$user) {
    header('Location: index.php');
    exit;
}

// Получение постов пользователя
$stmt = $pdo->prepare("
    SELECT * FROM posts 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();

// Получаем уровень пользователя
$level = getUserLevel($user_id);

// Получаем достижения пользователя (только завершенные)
$completed_achievements = getCompletedAchievements($user_id);

// Проверяем, подписан ли текущий пользователь (если авторизован)
$is_subscribed = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE subscriber_id = ? AND user_id = ?");
    $stmt->execute([$_SESSION['user_id'], $user['id']]);
    $is_subscribed = $stmt->fetch();
}

$page_title = 'Profile: ' . $user['username'];
include 'includes/header.php';
?>

<style>
    /* Profile Styles */
    .profile {
        margin: 20px 0;
    }

    .profile-header {
        display: flex;
        gap: 30px;
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 25px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }

    .profile-avatar {
        position: relative;
        flex: 0 0 150px;
    }

    .profile-avatar img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--accent-green);
    }

    .avatar-form {
        margin-top: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .avatar-form input[type="file"] {
        display: none;
    }

    .profile-info {
        flex: 1;
    }

    .profile-name-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .profile-name-container h1 {
        margin: 0;
    }

    /* Улучшенные кнопки профиля */
    .profile-actions {
        display: flex;
        gap: 15px;
    }
    
    .profile-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    /* Кнопка подписки */
    .subscribe-btn {
        background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }
    
    .subscribe-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    }
    
    /* Кнопка отписки */
    .unsubscribe-btn {
        background: rgba(244, 67, 54, 0.1);
        color: #f44336;
        border: 1px solid rgba(244, 67, 54, 0.3);
    }
    
    .unsubscribe-btn:hover {
        background: rgba(244, 67, 54, 0.2);
    }
    
    /* Кнопка сообщения */
    .message-btn {
        background: rgba(33, 150, 243, 0.1);
        color: #2196F3;
        border: 1px solid rgba(33, 150, 243, 0.3);
    }
    
    .message-btn:hover {
        background: rgba(33, 150, 243, 0.2);
    }
    
    /* Иконки в кнопках */
    .profile-btn i {
        margin-right: 8px;
        font-size: 1rem;
    }
    
    /* Эффект волны при нажатии */
    .profile-btn::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background: rgba(255, 255, 255, 0.5);
        opacity: 0;
        border-radius: 100%;
        transform: scale(1, 1) translate(-50%);
        transform-origin: 50% 50%;
    }
    
    .profile-btn:focus:not(:active)::after {
        animation: ripple 0.6s ease-out;
    }
    
    @keyframes ripple {
        0% {
            transform: scale(0, 0);
            opacity: 0.5;
        }
        100% {
            transform: scale(20, 20);
            opacity: 0;
        }
    }

    /* User Level Card */
    .user-level-card {
        display: flex;
        align-items: center;
        gap: 15px;
        background-color: rgba(0, 0, 0, 0.2);
        padding: 15px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
    }

    .level-icon {
        font-size: 2rem;
    }

    .level-details {
        flex: 1;
    }

    .level-name {
        font-weight: 600;
        display: block;
        margin-bottom: 8px;
    }

    .progress-container {
        height: 8px;
        background-color: #333;
        border-radius: 4px;
        margin-bottom: 5px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: var(--accent-gradient);
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    .progress-text {
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    /* Profile Stats */
    .profile-stats {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .stat {
        text-align: center;
    }

    .stat-number {
        display: block;
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--accent-green);
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .profile-about {
        padding: 15px;
        background-color: rgba(0, 0, 0, 0.1);
        border-radius: var(--border-radius);
        line-height: 1.6;
    }

    /* Achievements Section */
    .profile-section {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow);
    }

    .profile-section h2 {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .profile-section h2 i {
        margin-right: 10px;
        color: var(--accent-green);
    }

    .achievements-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 15px;
    }

    .achievement-card {
        display: flex;
        gap: 15px;
        padding: 15px;
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: var(--border-radius);
    }

    .achievement-card.completed {
        border-left: 4px solid var(--accent-green);
    }

    .achievement-icon {
        font-size: 1.5rem;
        color: var(--accent-green);
        flex: 0 0 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .achievement-details h3 {
        margin: 0 0 5px 0;
        display: flex;
        align-items: center;
    }

    .points {
        margin-left: auto;
        font-size: 0.9rem;
        color: var(--accent-green);
    }

    .achievement-details p {
        margin: 0 0 5px 0;
        font-size: 0.9rem;
    }

    .achievement-details small {
        color: var(--text-secondary);
        font-size: 0.8rem;
    }

    .text-center {
        text-align: center;
        margin-top: 15px;
    }

    /* Profile Posts */
    .profile-posts h2 {
        margin-bottom: 20px;
    }

    .profile-posts .post {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: var(--shadow);
    }

    .profile-posts .post-votes {
        background-color: rgba(0, 0, 0, 0.2);
    }

    /* Адаптивность */
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            gap: 20px;
        }

        .profile-avatar {
            align-self: center;
        }

        .profile-name-container {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .profile-actions {
            width: 100%;
            flex-direction: column;
        }
        
        .profile-btn {
            width: 100%;
            padding: 12px;
        }

        .profile-stats {
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .stat {
            flex: 0 0 calc(50% - 10px);
        }
        
        .achievements-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile">
    <div class="profile-header">
        <div class="profile-avatar">
            <img src="<?= SITE_URL ?>/uploads/<?= $user['avatar'] ?? 'default.png' ?>" alt="Avatar">
            
            <?php if (isLoggedIn() && $_SESSION['user_id'] == $user['id']): ?>
                <form action="upload_avatar.php" method="POST" enctype="multipart/form-data" class="avatar-form">
                    <input type="file" name="avatar" id="avatar" accept="image/*">
                    <button type="submit" class="btn btn-small">Refresh</button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <div class="profile-name-container">
                <h1><?= htmlspecialchars($user['username']) ?></h1>
                <?php if (isLoggedIn() && $_SESSION['user_id'] != $user['id']): ?>
                    <div class="profile-actions">
                        <a href="subscribe.php?user_id=<?= $user['id'] ?>" 
                           class="profile-btn <?= $is_subscribed ? 'unsubscribe-btn' : 'subscribe-btn' ?>">
                            <i class="fas <?= $is_subscribed ? 'fa-user-minus' : 'fa-user-plus' ?>"></i>
                            <?= $is_subscribed ? 'Unsubscribe' : 'Subscribe' ?>
                        </a>
                        <a href="messages.php?user=<?= $user['id'] ?>" class="profile-btn message-btn">
                            <i class="fas fa-paper-plane"></i> Write
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Блок уровня пользователя -->
            <div class="user-level-card">
                <div class="level-icon">
                    <i class="fas fa-trophy" style="color: <?= getLevelColor($level['name']) ?>"></i>
                </div>
                <div class="level-details">
                    <span class="level-name">Level: <?= $level['name'] ?></span>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= $level['progress'] ?>%"></div>
                    </div>
                    <span class="progress-text">
                        <?= $user['rating'] ?? 0 ?> out of <?= $level['next_level_points'] ?> points
                    </span>
                </div>
            </div>
            
            <div class="profile-stats">
                <div class="stat">
                    <span class="stat-number"><?= count($posts) ?></span>
                    <span class="stat-label">Posts</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= $user['rating'] ?? 0 ?></span>
                    <span class="stat-label">Rating</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= $user['followers_count'] ?? 0 ?></span>
                    <span class="stat-label">Subscribers</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                    <span class="stat-label">User since</span>
                </div>
            </div>
            
            <?php if (!empty($user['about'])): ?>
                <div class="profile-about">
                    <?= nl2br(htmlspecialchars($user['about'])) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Блок последних полученных достижений -->
    <div class="profile-section">
        <h2><i class="fas fa-trophy"></i> Recent achievements</h2>
        <?php if (empty($completed_achievements)): ?>
            <div class="alert alert-info">There are no achievements, yet.</div>
        <?php else: ?>
            <div class="achievements-grid">
                <?php foreach (array_slice($completed_achievements, 0, 3) as $achievement): ?>
                    <div class="achievement-card completed">
                        <div class="achievement-icon">
                            <i class="fas <?= $achievement['icon'] ?>"></i>
                        </div>
                        <div class="achievement-details">
                            <h3><?= $achievement['title'] ?> <span class="points">+<?= $achievement['points'] ?></span></h3>
                            <p><?= $achievement['description'] ?></p>
                            <small>Received: <?= date('d.m.Y', strtotime($achievement['completed_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($completed_achievements) > 3): ?>
                <div class="text-center">
                    <a href="achievements.php?id=<?= $user_id ?>" class="btn btn-more">All achievements</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <div class="profile-posts">
        <h2>User Posts</h2>
        
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">The user has not created any posts, yet.</div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-votes">
                        <span class="vote-count"><?= $post['upvotes'] - $post['downvotes'] ?></span>
                    </div>
                    <div class="post-content">
                        <h3><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                        <div class="post-meta">
                            <span class="post-date"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>