<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $content = sanitize($_POST['content'] ?? '');
    $image = null;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image']);
    }
    
    if (!empty($content) || $image) {
        $stmt = $pdo->prepare("
            INSERT INTO stories (user_id, content, image) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $content, $image]);
        
        header('Location: stories.php');
        exit;
    }
}

$stmt = $pdo->query("
    SELECT s.*, u.username, u.avatar 
    FROM stories s
    JOIN users u ON s.user_id = u.id
    WHERE s.is_active = TRUE AND s.expires_at > NOW()
    ORDER BY s.created_at DESC
");
$stories = $stmt->fetchAll();

$page_title = 'Stories';
include 'includes/header.php';
?>
<style>
    .stories-page {
        margin: 20px 0;
    }

    /* Create Story Card */
    .create-story-card {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }

    .create-story-card .card-header {
        border-bottom: 1px solid #333;
        padding: 15px;
    }

    .create-story-card h2 {
        margin: 0;
        display: flex;
        align-items: center;
    }

    .create-story-card h2 i {
        margin-right: 10px;
        color: var(--accent-green);
    }

    .story-form {
        padding: 15px;
    }

    .story-form textarea {
        width: 100%;
        padding: 12px 15px;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid #333;
        border-radius: var(--border-radius);
        color: var(--text-primary);
        resize: vertical;
        min-height: 100px;
    }

    .story-form textarea:focus {
        outline: none;
        border-color: var(--accent-green);
        box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
    }

    /* Stories Grid */
    .stories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .story-card {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: var(--transition);
    }

    .story-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }

    .story-card .card-header {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #333;
    }

    .story-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .story-image-container {
        width: 100%;
        height: 200px;
        overflow: hidden;
    }

    .story-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .story-card:hover .story-image-container img {
        transform: scale(1.05);
    }

    .story-content {
        padding: 15px;
        line-height: 1.5;
    }

    .story-card .card-footer {
        padding: 10px 15px;
        border-top: 1px solid #333;
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .story-card .card-footer i {
        margin-right: 5px;
    }

    /* Empty State */
    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-state i {
        color: var(--accent-green);
        margin-bottom: 15px;
    }

    .empty-state h3 {
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .stories-grid {
            grid-template-columns: 1fr;
        }
        
        .create-story-card {
            margin-bottom: 15px;
        }
    }
</style>

<div class="stories-page container">
    <h1><i class="fas fa-history"></i> Active stories</h1>
    
    <?php if (isLoggedIn()): ?>
        <div class="create-story-card">
            <div class="card-header">
                <h2><i class="fas fa-plus-circle"></i> Create story</h2>
            </div>
            <div class="card-body">
                <form action="stories.php" method="POST" enctype="multipart/form-data" class="story-form">
                    <div class="form-group">
                        <textarea name="content" placeholder="Share something interesting..."></textarea>
                    </div>
                    <div class="form-actions">
                        <label class="file-upload-label">
                            <i class="fas fa-camera"></i> Add photo
                            <input type="file" name="image" accept="image/*" class="file-upload-input">
                        </label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (empty($stories)): ?>
        <div class="empty-state">
            <i class="fas fa-history fa-3x"></i>
            <h3>No active stories</h3>
            <p>Be the first to share something interesting!</p>
        </div>
    <?php else: ?>
        <div class="stories-grid">
            <?php foreach ($stories as $story): ?>
                <div class="story-card">
                    <a href="story.php?id=<?= $story['id'] ?>" class="text-decoration-none">
                        <div class="card-header">
                            <img src="<?= SITE_URL ?>/uploads/<?= $story['avatar'] ?>" 
                                 alt="<?= htmlspecialchars($story['username']) ?>" 
                                 class="story-avatar">
                            <span><?= htmlspecialchars($story['username']) ?></span>
                        </div>
                        <?php if ($story['image']): ?>
                            <div class="story-image-container">
                                <img src="<?= SITE_URL ?>/uploads/<?= $story['image'] ?>" 
                                     alt="Story">
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <?php if (!empty($story['content'])): ?>
                                <div class="story-content">
                                   <?php
$truncated_content = truncateText($story['content'], 100);  // Use the truncateText function
?>
<?= nl2br(embedMediaLinks(html_entity_decode(htmlspecialchars($truncated_content)))) ?>
<?= mb_strlen($story['content'], 'UTF-8') > 100 ? '...' : '' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <i class="far fa-clock"></i><?= time_elapsed_string($story['created_at']) ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>