<?php
require_once 'includes/config.php';
require_once 'includes/functions.php'; require_once 'includes/auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'following';

// Получаем подписки или подписчиков
if ($type == 'followers') {
    $stmt = $pdo->prepare("
        SELECT u.*, 
               (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as posts_count
        FROM users u
        JOIN subscriptions s ON u.id = s.subscriber_id
        WHERE s.user_id = ?
        ORDER BY s.created_at DESC
    ");
    $title = 'My subscribers';
    $empty_message = 'You do not have any subscribers yet';
} else {
    $stmt = $pdo->prepare("
        SELECT u.*, 
               (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as posts_count
        FROM users u
        JOIN subscriptions s ON u.id = s.user_id
        WHERE s.subscriber_id = ?
        ORDER BY s.created_at DESC
    ");
    $title = 'My subscriptions';
    $empty_message = 'You are not subscribed to anyone yet';
}

$stmt->execute([$user_id]);
$users = $stmt->fetchAll();

$page_title = $title;
include 'includes/header.php';
?>

<style>
    /* Subscriptions Page */
    .subscriptions-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #333;
    }
    
    .subscriptions-header h1 {
        display: flex;
        align-items: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
    
    .subscriptions-header h1 i {
        margin-right: 10px;
        color: var(--accent-green);
    }
    
    .subscriptions-tabs {
        display: flex;
        border-radius: var(--border-radius);
        overflow: hidden;
        background-color: var(--darker-bg);
    }
    
    .subscriptions-tabs a {
        flex: 1;
        text-align: center;
        padding: 10px;
        color: var(--text-secondary);
        transition: var(--transition);
    }
    
    .subscriptions-tabs a:hover {
        background-color: rgba(76, 175, 80, 0.1);
        color: var(--text-primary);
    }
    
    .subscriptions-tabs a.active {
        background: var(--accent-gradient);
        color: white;
        font-weight: 600;
    }
    
    .subscriptions-tabs a i {
        margin-right: 5px;
    }
    
    /* Users Grid */
    .users-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .user-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 15px;
        box-shadow: var(--shadow);
        transition: var(--transition);
        text-align: center;
    }
    
    .user-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }
    
    .user-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
        border: 3px solid var(--accent-green);
    }
    
    .user-info {
        width: 100%;
    }
    
    .user-info h3 {
        margin: 0 0 5px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .user-info h3 a:hover {
        color: var(--accent-green);
    }
    
    .user-stats {
        display: flex;
        justify-content: center;
        gap: 15px;
        font-size: 0.9rem;
        color: var(--text-secondary);
    }
    
    .user-stats i {
        margin-right: 3px;
        color: var(--accent-green);
    }
    
    .user-actions {
        margin-top: 10px;
        width: 100%;
    }
    
    .btn-subscribe {
        width: 100%;
        padding: 8px;
        background: var(--accent-gradient);
        color: white;
        border: none;
        border-radius: var(--border-radius);
        font-size: 0.9rem;
        cursor: pointer;
    }
    
    .btn-subscribe:hover {
        opacity: 0.9;
    }
    
    .btn-unsubscribe {
        width: 100%;
        padding: 8px;
        background-color: rgba(244, 67, 54, 0.1);
        color: #f44336;
        border: none;
        border-radius: var(--border-radius);
        font-size: 0.9rem;
        cursor: pointer;
    }
    
    .btn-unsubscribe:hover {
        background-color: rgba(244, 67, 54, 0.2);
    }
    
    /* Alert */
    .alert {
        padding: 15px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    
    .alert i {
        margin-right: 10px;
        font-size: 1.2rem;
    }
    
    .alert-info {
        background-color: rgba(33, 150, 243, 0.1);
        color: #2196F3;
    }
    
    @media (max-width: 768px) {
        .users-grid {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        }
        
        .user-avatar {
            height: 120px;
        }
    }
    
    @media (max-width: 480px) {
        .users-grid {
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        }
        
        .user-avatar {
            height: 100px;
        }
    }
</style>

<div class="container">
    <div class="subscriptions-header">
        <h1><i class="fas fa-users"></i> <?= $title ?></h1>
        <div class="subscriptions-tabs">
            <a href="?type=following" class="<?= $type == 'following' ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i> Following
            </a>
            <a href="?type=followers" class="<?= $type == 'followers' ? 'active' : '' ?>">
                <i class="fas fa-user-friends"></i> Followers
            </a>
        </div>
    </div>
    
    <?php if (empty($users)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <?= $empty_message ?>
        </div>
    <?php else: ?>
        <div class="users-grid">
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <a href="profile.php?id=<?= $user['id'] ?>">
                        <img src="<?= SITE_URL ?>/uploads/<?= $user['avatar'] ?? 'default.png' ?>" 
                             alt="<?= htmlspecialchars($user['username']) ?>" class="user-avatar">
                    </a>
                    <div class="user-info">
                        <h3><a href="profile.php?id=<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></a></h3>
                        <div class="user-stats">
                            <span><i class="fas fa-file-alt"></i> <?= $user['posts_count'] ?? 0 ?></span>
                            <span><i class="fas fa-star"></i> <?= $user['rating'] ?? 0 ?></span>
                        </div>
                    </div>
                    <?php if ($type == 'followers' && $user['id'] != $_SESSION['user_id']): ?>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE subscriber_id = ? AND user_id = ?");
                        $stmt->execute([$_SESSION['user_id'], $user['id']]);
                        $is_subscribed = $stmt->fetch();
                        ?>
                        <div class="user-actions">
                            <a href="subscribe.php?user_id=<?= $user['id'] ?>" 
                               class="<?= $is_subscribed ? 'btn-unsubscribe' : 'btn-subscribe' ?>">
                                <?= $is_subscribed ? '<i class="fas fa-user-minus"></i>' : '<i class="fas fa-user-plus"></i>' ?>
                                <?= $is_subscribed ? 'Follow' : 'Unfollow' ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>