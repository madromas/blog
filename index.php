<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'includes/config.php';
require_once 'includes/functions.php';

$popular_posts = getPopularPosts();
$new_posts = getNewPosts();

// Получаем активные истории для карусели
$stmt = $pdo->query("
    SELECT s.*, u.username, u.avatar 
    FROM stories s
    JOIN users u ON s.user_id = u.id
    WHERE s.is_active = TRUE AND s.expires_at > NOW()
    ORDER BY s.created_at DESC
    LIMIT 10
");
$stories = $stmt->fetchAll();

$page_title = 'Home';
include 'includes/header.php';
?>
<style>
    /* Stories Carousel - Modern Facebook-like */
    .stories-carousel-container {
        margin: 20px 0;
        padding: 15px;
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .stories-carousel-container h2 {
        margin-bottom: 15px;
        font-size: 1.2rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .stories-carousel {
        display: flex;
        overflow-x: auto;
        padding: 10px 0;
        gap: 10px;
        scrollbar-width: none;
    }

    .stories-carousel::-webkit-scrollbar {
        display: none;
    }

    .story-item {
        flex: 0 0 110px;
        height: 190px;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
    }

    .story-item:hover {
        transform: scale(1.03);
    }

    .story-background {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        position: relative;
    }

    .story-gradient {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.6));
    }

    .story-avatar {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 3px solid var(--accent-green);
        background-color: var(--card-bg);
        overflow: hidden;
    }

    .story-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .story-author {
        position: absolute;
        bottom: 10px;
        left: 0;
        right: 0;
        text-align: center;
        color: white;
        font-weight: 500;
        font-size: 0.8rem;
        padding: 0 5px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.5);
    }

    .create-story {
        background-color: var(--card-bg);
        border: 2px dashed var(--accent-green);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .create-story-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--accent-green);
    }

    .create-story-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--accent-green);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    .create-story-icon i {
        color: white;
        font-size: 1.2rem;
    }

    .create-story-text {
        font-size: 0.8rem;
        font-weight: 500;
        max-width: 80px;
    }

    /* Main Content */
    .content {
        display: flex;
        gap: 20px;
        margin: 20px 0;
    }

    .main-content {
        flex: 1;
    }

    .main-content h2 {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .main-content h2 i {
        margin-right: 10px;
        color: var(--accent-green);
    }

    /* Posts */
    .post {
        display: flex;
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 20px;
        overflow: hidden;
        transition: var(--transition);
    }

    .post:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    .post-votes {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 15px;
        background-color: rgba(0, 0, 0, 0.2);
    }

    .vote-btn {
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 1.2rem;
        transition: var(--transition);
        padding: 5px;
    }

    .vote-btn:hover {
        color: var(--accent-green);
    }

    .vote-count {
        font-weight: bold;
        margin: 5px 0;
        font-size: 1.1rem;
    }

    .upvote.active {
        color: var(--accent-green);
    }

    .downvote.active {
        color: #f44336;
    }

    .post-content {
        flex: 1;
        padding: 15px;
    }

    .post-content h2 {
        margin-bottom: 10px;
    }

    .post-content h2 a:hover {
        color: var(--accent-green);
    }

    .post-image {
        margin: 15px 0;
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .post-text {
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .post-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 0.9rem;
        color: var(--text-secondary);
    }

    .post-meta i {
        margin-right: 5px;
        color: var(--accent-green);
    }

    /* Sidebar */
    .sidebar {
        width: 300px;
    }

    .sidebar-widget {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 15px;
        margin-bottom: 20px;
    }

    .sidebar-widget h3 {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }

    .sidebar-widget h3 i {
        margin-right: 10px;
        color: var(--accent-green);
    }

    .new-posts-list {
        list-style: none;
    }

    .new-posts-list li {
        padding: 10px 0;
        border-bottom: 1px solid #333;
    }

    .new-posts-list li:last-child {
        border-bottom: none;
    }

    .new-posts-list a:hover {
        color: var(--accent-green);
    }

    .post-meta {
        font-size: 0.8rem;
    }

    /* Tags */
    .tags-cloud {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tag {
        display: inline-block;
        padding: 5px 10px;
        background-color: rgba(76, 175, 80, 0.1);
        border-radius: 20px;
        font-size: 0.8rem;
        color: var(--accent-green);
        transition: var(--transition);
    }

    .tag:hover {
        background-color: rgba(76, 175, 80, 0.3);
    }

    @media (max-width: 768px) {
        .content {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
        }

        .post {
            flex-direction: column;
        }

        .post-votes {
            flex-direction: row;
            justify-content: center;
            padding: 10px;
        }

        .vote-count {
            margin: 0 15px;
        }

        .story-item {
            flex: 0 0 100px;
            height: 170px;
        }
        
        .create-story-text {
            font-size: 0.7rem;
        }
    }
</style>

<!-- Карусель историй -->
<div class="stories-carousel-container">
    <h2>Fresh stories</h2>
    <div class="stories-carousel">
        <?php if (isLoggedIn()): ?>
            <div class="story-item create-story">
                <a href="create_story.php">
                    <div class="create-story-content">
                        <div class="create-story-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <span class="create-story-text">Create story</span>
                    </div>
                </a>
            </div>
        <?php endif; ?>
        
        <?php foreach ($stories as $story): ?>
            <div class="story-item" data-story-id="<?= $story['id'] ?>">
                <a href="story.php?id=<?= $story['id'] ?>">
                    <div class="story-background" style="background-image: url('<?= SITE_URL ?>/uploads/<?= $story['image'] ?>')">
                        <div class="story-gradient"></div>
                        <div class="story-avatar">
                            <img src="<?= SITE_URL ?>/uploads/<?= $story['avatar'] ?>" alt="<?= htmlspecialchars($story['username']) ?>">
                        </div>
                        <div class="story-author"><?= htmlspecialchars($story['username']) ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="content">
    <div class="main-content">
        <h2><i class="fas fa-fire"></i> Popular posts</h2>
        <?php foreach ($popular_posts as $post): ?>
            <div class="post">
                <div class="post-votes">
                    <button class="vote-btn upvote" data-post-id="<?= $post['id'] ?>" data-type="up"><i class="fas fa-arrow-up"></i></button>
                    <span class="vote-count"><?= $post['upvotes'] - $post['downvotes'] ?></span>
                    <button class="vote-btn downvote" data-post-id="<?= $post['id'] ?>" data-type="down"><i class="fas fa-arrow-down"></i></button>
                </div>
                <div class="post-content">
                    <h2><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                    <?php if (!empty($post['image'])): ?>
                        <div class="post-image">
                            <a href="post.php?id=<?= $post['id'] ?>"><img src="<?= SITE_URL ?>/uploads/<?= $post['image'] ?>" alt="<?= htmlspecialchars($post['title']) ?>"></a>
                        </div>
                    <?php endif; ?>
                    <div class="post-text"><?= nl2br(htmlspecialchars(substr($post['content'], 0, 300))) ?>...</div>
                    <div class="post-meta">
                        <span class="post-author"><a href="profile.php?id=<?= $post['user_id'] ?>"><i class="fas fa-user"></i> <?= htmlspecialchars($post['username']) ?></a></span>
                        <span class="post-date"><i class="far fa-clock"></i> <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                        <span class="post-comments"><i class="fas fa-comment"></i> <?= $post['comments_count'] ?></span>
                        <span class="post-views">
                <i class="fas fa-eye"></i> <?= $post['views'] ?>
            </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="sidebar">
        <div class="sidebar-widget">
            <h3><i class="far fa-newspaper"></i> New posts</h3>
            <ul class="new-posts-list">
                <?php foreach ($new_posts as $post): ?>
                    <li>
                        <a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                        <span class="post-meta"><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="sidebar-widget">
            <h3><i class="fas fa-tags"></i> Tags</h3>
            <div class="tags-cloud">
                <?php
                $tags = ['technology', 'sport', 'movies', 'music', 'games'];
                foreach ($tags as $tag) {
                    echo '<a href="search.php?tag=' . urlencode($tag) . '" class="tag">' . htmlspecialchars($tag) . '</a>';
                }
                ?>
            </div>
        </div>

        <!-- Баннер -->
        <div class="sidebar-widget">
            <div class="banner-container">
                <div class="banner" id="banner">
                    <div class="banner-particles" id="banner-particles"></div>
                    <div class="banner-content">
                      Lolz...
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>