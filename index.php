<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Login page if user is not logged in

// Check for "Remember Me" cookie before session-based login
//if (!isLoggedIn()) {  // Only check the cookie if not already logged in
//  if(checkRememberMeCookie($pdo)){
//        header('Location: index.php'); //Redirect to index if cookie login is successful
//        exit;
//    }
//}
//if (!isLoggedIn()) {
//    header('Location: login.php');
//    exit;
//}

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

//Fetch latest comments from the database
$latestComments = getLatestComments();

// Fetch all unique tags from the database
$allTags = getAllTags();

$stmt = $pdo->prepare("
    SELECT c.*, u.username, u.avatar 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = ? 
    ORDER BY c.created_at DESC
");
$comments = $stmt->fetchAll();

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
  
.new-comments-list p{
        margin-bottom: 0px;
    }

    .new-comments-list li{
        background-color: rgba(0, 0, 0, 0.2);
        padding:10px!important;
        margin-bottom: 5px;
    border-radius: 10px;
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

.new-comments-list .post-meta{
    margin-bottom: 10px;
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

.new-comments-list {
        list-style: none;
        word-wrap: break-word;
    }

    .new-comments-list li:last-child {
        border-bottom: none;
    }

    .new-comments-list a:hover {
        color: var(--accent-green);
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

<div class="header-nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="?tab=new" class="<?= ($_GET['tab'] ?? 'new') == 'new' ? 'active' : '' ?>"><i class="fas fa-newspaper"></i> New</a></li>
                    <li class="nav-item"><a href="?tab=popular" class="<?= ($_GET['tab'] ?? 'new') == 'popular' ? 'active' : '' ?>"><i class="fas fa-fire"></i> Popular</a></li>
                </ul>
</div>
    <?php
    $tab = $_GET['tab'] ?? 'new'; // Get the active tab from the query string

    if ($tab == 'popular'): ?>

        <h2><i class="fas fa-fire"></i> Popular posts</h2>
        <?php foreach ($popular_posts as $post): ?>
            <div class="post">
                <div class="post-votes">
                    <button class="vote-btn upvote" data-post-id="<?= $post['id'] ?>" data-type="up"><i class="fas fa-arrow-up"></i></button>
                    <span class="vote-count"><?= $post['upvotes'] - $post['downvotes'] ?></span>
                    <button class="vote-btn downvote" data-post-id="<?= $post['id'] ?>" data-type="down"><i class="fas fa-arrow-down"></i></button>
                </div>
                <div class="post-content">
                    <h2><a href="post.php?id=<?= $post['id'] ?>"><?= nl2br(htmlspecialchars_decode(htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'))) ?>
</a></h2>
                    <?php if (!empty($post['image'])): ?>
                        <div class="post-image">
                            <a href="post.php?id=<?= $post['id'] ?>"><img src="<?= SITE_URL ?>/uploads/<?= $post['image'] ?>" alt="<?= htmlspecialchars($post['title']) ?>"></a>
                        </div>
                    <?php endif; ?>
                    <div class="post-text">
    <?php
    $truncated_content = truncateText($post['content'], 1000);
    ?>
    <p><?= nl2br(embedMediaLinks(html_entity_decode(htmlspecialchars($truncated_content)))) ?></p>
</div>


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

<?php elseif ($tab == 'new'): ?>

        <h2><i class="fas fa-newspaper"></i> New posts</h2>
        <?php foreach ($new_posts as $post): ?>
            <div class="post">
                <div class="post-votes">
                    <button class="vote-btn upvote" data-post-id="<?= $post['id'] ?>" data-type="up"><i class="fas fa-arrow-up"></i></button>
                    <span class="vote-count"><?= $post['upvotes'] - $post['downvotes'] ?></span>
                    <button class="vote-btn downvote" data-post-id="<?= $post['id'] ?>" data-type="down"><i class="fas fa-arrow-down"></i></button>
                </div>
                <div class="post-content">
                    <h2><a href="post.php?id=<?= $post['id'] ?>"><?= nl2br(htmlspecialchars_decode(htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'))) ?>
</a></h2>
                    <?php if (!empty($post['image'])): ?>
                        <div class="post-image">
                            <a href="post.php?id=<?= $post['id'] ?>"><img src="<?= SITE_URL ?>/uploads/<?= $post['image'] ?>" alt="<?= htmlspecialchars($post['title']) ?>"></a>
                        </div>
                    <?php endif; ?>
                    <div class="post-text">
    <?php
    $truncated_content = truncateText($post['content'], 1000);
    ?>
    <p><?= nl2br(embedMediaLinks(html_entity_decode(htmlspecialchars($truncated_content)))) ?></p>
</div>


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
    
    <?php endif; ?>
</div>

    
    <div class="sidebar">

<div class="sidebar-widget">
    <h3><i class="fas fa-comments"></i> Latest Comments</h3>
    <ul class="new-comments-list">
        <?php if ($latestComments): ?>
            <?php foreach ($latestComments as $comment): ?>
                <li><div class="post-meta"><span class="post-author">
                    <img src="<?= SITE_URL ?>/uploads/<?= $comment['avatar'] ?? 'default.png' ?>" alt="Avatar" class="avatar">
                    <a href="profile.php?id=<?= $comment['user_id'] ?>">
         <?= htmlspecialchars($comment['username']) ?>
    </a></span></div>
                    <?php
$truncated_content = substr($comment['content'], 0, 50); // Truncate the string
?>
<p><a href="post.php?id=<?= $comment['post_id'] ?>"><?= nl2br(embedMediaLinks(html_entity_decode(htmlspecialchars($truncated_content)))) ?></a>


</p>
                <p style="font-size:.9rem;text-align: right;"><i class="fa-solid fa-arrow-turn-up" style="transform: rotate(90deg); font-size:.8em"></i> <a href="post.php?id=<?= $comment['post_id'] ?>" target="_blank"><?= htmlspecialchars($comment['post_title']) ?></a></p>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No comments yet.</li>
        <?php endif; ?>
    </ul>
</div>
        
        <div class="sidebar-widget">
            <h3><i class="far fa-newspaper"></i> New posts</h3>
            <ul class="new-posts-list">
                <?php foreach ($new_posts as $post): ?>
                    <li>
                        <a href="post.php?id=<?= $post['id'] ?>"><?= nl2br(htmlspecialchars_decode(htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'))) ?>
</a>
                        <span class="post-meta"><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
      <div class="sidebar-widget">
    <h3><i class="fas fa-tags"></i> Tags</h3>
    <div class="tags-cloud">
        <?php
        foreach ($allTags as $tag) {
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