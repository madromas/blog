<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: stories.php');
    exit;
}

$story_id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT s.*, u.username, u.avatar 
    FROM stories s
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ? AND s.is_active = TRUE AND s.expires_at > NOW()
");
$stmt->execute([$story_id]);
$story = $stmt->fetch();

if (!$story) {
    header('Location: stories.php');
    exit;
}

$page_title = 'Story from ' . $story['username'];
include 'includes/header.php';
?>
<style>
    .story-container {
        max-width: 600px;
        margin: 20px auto;
    }

    .story-card {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    /* Story Header */
    .story-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #333;
    }

    .author-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .author-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .author-details {
        display: flex;
        flex-direction: column;
    }

    .author-name {
        font-weight: 600;
        color: var(--text-primary);
    }

    .author-name:hover {
        color: var(--accent-green);
    }

    .story-meta {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .time-ago, .time-left {
        display: inline-block;
    }

    .report-button {
        color: var(--text-secondary);
        font-size: 1rem;
        transition: var(--transition);
    }

    .report-button:hover {
        color: #f44336;
    }

    /* Story Content */
    .story-content {
        padding: 0;
    }

    .story-media {
        width: 100%;
    }

    .story-image {
        width: 100%;
        max-height: 600px;
        object-fit: contain;
    }

    .story-text {
        padding: 15px;
        line-height: 1.5;
    }

    /* Story Actions */
    .story-actions {
        padding: 15px;
        border-top: 1px solid #333;
    }

    @media (max-width: 768px) {
        .story-container {
            margin: 10px;
        }
    }
</style>

<div class="story-container">
    <div class="story-card">
        <!-- Шапка истории -->
        <div class="story-header">
            <div class="author-info">
                <img src="<?= SITE_URL ?>/uploads/<?= $story['avatar'] ?>" 
                     alt="<?= htmlspecialchars($story['username']) ?>" 
                     class="author-avatar">
                <div class="author-details">
                    <a href="profile.php?id=<?= $story['user_id'] ?>" class="author-name">
                        <?= htmlspecialchars($story['username']) ?>
                    </a>
                    <div class="story-meta">
                        <span class="time-ago"><?= time_elapsed_string($story['created_at']) ?></span>
                        <span class="time-left">• Expires after <?= time_remaining($story['expires_at']) ?></span>
                    </div>
                </div>
            </div>
            
            <?php if (isLoggedIn() && $_SESSION['user_id'] != $story['user_id']): ?>
                <a href="report.php?type=story&id=<?= $story['id'] ?>" class="report-button">
                    <i class="fas fa-flag"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Контент истории -->
        <div class="story-content">
            <?php if ($story['image']): ?>
                <div class="story-media">
                    <img src="<?= SITE_URL ?>/uploads/<?= $story['image'] ?>" 
                         alt="Story" 
                         class="story-image">
                </div>
            <?php endif; ?>
            
            <?php if (!empty($story['content'])): ?>
                <div class="story-text">
                    <?= nl2br(htmlspecialchars($story['content'])) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Действия для автора/модератора -->
        <?php if (isLoggedIn() && ($_SESSION['user_id'] == $story['user_id'] || hasPermission('moderator'))): ?>
            <div class="story-actions">
                <form action="delete_story.php" method="POST">
                    <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                    <button type="submit" class="delete-button">
                        <i class="fas fa-trash"></i> Delete story
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>