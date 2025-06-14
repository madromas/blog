<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';  

// Get the post ID from the URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Start or resume the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the post has already been viewed in this session
$session_view_key = 'viewed_post_' . $post_id;

if (!isset($_SESSION[$session_view_key])) {
    // If not viewed, increment the view count in the database
    incrementPostViews($post_id);

    // Mark the post as viewed in this session
    $_SESSION[$session_view_key] = true;
}

// Получение информации о посте
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.avatar 
    FROM posts p 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

// Получение комментариев
$stmt = $pdo->prepare("
    SELECT c.*, u.username, u.avatar 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();

// Увеличение счетчика просмотров this is it :)))
//$pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post_id]); REMOVE THIS!!!

$page_title = $post['title'];
include 'includes/header.php';
?>

<style>    
    .comment-actions {
        margin-top: 5px;
        float:right;
    }
    .post-actions {
        margin-top: 5px;
        float:right;
    }
    .post-full {
        display: flex;
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .post-votes {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        background-color: rgba(0, 0, 0, 0.3);
    }

    .vote-btn {
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: 1.5rem;
        transition: var(--transition);
        padding: 5px;
    }

    .vote-btn:hover {
        color: var(--accent-green);
    }

    .vote-count {
        font-weight: bold;
        margin: 10px 0;
        font-size: 1.5rem;
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

    .post-content h1 {
        margin-bottom: 15px;
    }

    .post-author {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .post-author .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }

    .post-author a:hover {
        color: var(--accent-green);
    }

    .post-image-full {
        margin: 20px 0;
        border-radius: var(--border-radius);
        overflow: hidden;
    }


    .post-text-full {
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .post-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Comments Section */

    .comments-section h2 {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .comments-section h2 i {
        margin-right: 10px;
        color: var(--accent-green);
    }

    .comment-form {
        margin-bottom: 30px;
    }

    .comment-form textarea {
        width: 100%;
        padding: 15px;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid #333;
        border-radius: var(--border-radius);
        color: var(--text-primary);
        min-height: 100px;
        resize: vertical;
        margin-bottom: 10px;
    }

    .comment-form textarea:focus {
        outline: none;
        border-color: var(--accent-green);
        box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
    }

    .comment-author {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .comment-author a {
        font-weight: 600;
    }

    .btn-message {
        margin-left: auto;
        color: var(--accent-green);
        background: none;
        border: none;
        cursor: pointer;
    }

    .comment-content {
        line-height: 1.5;
        margin-bottom: 10px;
    }

    .comment-meta {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .comment-meta i {
        margin-right: 5px;
    }

    @media (max-width: 768px) {
        .post-full {
            flex-direction: column;
        }
        
        .post-votes {
            flex-direction: row;
            justify-content: center;
            padding: 15px;
        }
        
        .vote-count {
            margin: 0 15px;
        }
    }
</style>

<div class="post-full">
    <div class="post-votes">
        <button class="vote-btn upvote" data-post-id="<?= $post['id'] ?>" data-type="up">
            <i class="fas fa-arrow-up"></i>
        </button>
        <span class="vote-count"><?= $post['upvotes'] - $post['downvotes'] ?></span>
        <button class="vote-btn downvote" data-post-id="<?= $post['id'] ?>" data-type="down">
            <i class="fas fa-arrow-down"></i>
        </button>
    </div>
    <div class="post-content">

<div class="post-actions">

<a href="report.php?type=post&id=<?= $post['id'] ?>" class="btn btn-warning btn-small"><i class="fas fa-exclamation-circle"></i></a>



<?php
        // Check if the user is logged in
        if (isLoggedIn()) {
            // Get the current user's ID
            $current_user_id = $_SESSION['user_id'];

            // Check if the current user is the author of the post, an admin, or a moderator
            $is_author = ($current_user_id == $post['user_id']);
            $is_admin = hasPermission('admin');
            $is_moderator = hasPermission('moderator'); // Replace with your actual moderator permission check

            // If the user has permission, display the Edit and Delete buttons
            if ($is_author || $is_admin || $is_moderator) {
        ?>

                    <a href="edit_post.php?id=<?= $post_id ?>" class="btn btn-edit btn-small">
                                                <i class="fas fa-edit"></i></a>
                    <a href="delete_post.php?id=<?= $post_id ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this post?')">
                                                <i class="fas fa-trash"></i></a>
                
        <?php
            }
        }
        ?>
</div>
        
        
        <h1><?= nl2br(htmlspecialchars_decode(htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'))) ?>

</h1>
        
        <div class="post-meta">
            
            <span class="post-author">
                <img src="<?= SITE_URL ?>/uploads/<?= $post['avatar'] ?? 'default.png' ?>" alt="Avatar" class="avatar">
                <a href="profile.php?id=<?= $post['user_id'] ?>"><?= htmlspecialchars($post['username']) ?></a>
            </span>
            <span class="post-date">
                <i class="far fa-clock"></i> <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?>
            </span>
            <span class="post-views">
                <i class="fas fa-eye"></i> <?= $post['views'] ?>
            </span>
        </div>
        <?php if (!empty($post['image'])): ?>
            <div class="post-image-full <?php if ($post['is_nsfw'] && !isLoggedIn()) echo 'nsfw-post'; ?>" data-post-id="<?= $post['id'] ?>">
                <a data-fancybox data-caption="<?= htmlspecialchars($post['title']) ?>" href="<?= SITE_URL ?>/uploads/<?= $post['image'] ?>"><img src="<?= SITE_URL ?>/uploads/<?= $post['image'] ?>" alt="<?= htmlspecialchars($post['title']) ?>"></a>
            </div>
        <?php endif; ?>
        
        <div class="post-text-full">
            <p><?= nl2br(embedMediaLinks(html_entity_decode(htmlspecialchars($post['content'])))) ?></p>
        </div>
        
        <?php if (!empty($post['tags'])): ?>
            <div class="post-tags">
                <?php
                $tags = explode(',', $post['tags']);
                foreach ($tags as $tag) {
                    if (!empty(trim($tag))) {
                        echo '<a href="search.php?tag=' . urlencode(trim($tag)) . '" class="tag">' . htmlspecialchars(trim($tag)) . '</a>';
                    }
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="comments-section">
    <h2><i class="fas fa-comments"></i> Comments (<?= count($comments) ?>)</h2>
    
    <?php if (isLoggedIn()): ?>
        <form action="add_comment.php" method="POST" enctype="multipart/form-data" class="comment-form">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <div class="form-group">
                <textarea name="content" placeholder="Write your comment..." required></textarea>
             
<div class="form-group">
                <label for="comment_image" class="file-upload-label">
                    <i class="fas fa-image"></i>
                    <span id="file-upload-text"></span>
                    <input type="file" id="comment_image" name="comment_image" accept="image/*" class="file-upload-input">
                </label>
                <div id="image-preview" class="image-preview"></div>
            </div>
  
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit
            </button>
        </form>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <a href="login.php">Login</a> or <a href="register.php">register</a>, to leave a comment.
        </div>
    <?php endif; ?>
    
    <div class="comments-list">
        <?php if (empty($comments)): ?>
            <div class="alert alert-info">No comments yet. Be the first!</div>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">



<?php
                    // Check if the user is logged in
                    if (isLoggedIn()) {
                        // Get the current user's ID
                        $current_user_id = $_SESSION['user_id'];

                        // Check if the current user is the author of the comment, an admin, or a moderator
                        $is_author = ($current_user_id == $comment['user_id']); // Assuming you have user_id in the comments table
                        $is_admin = hasPermission('admin');
                        $is_moderator = hasPermission('moderator'); // Replace with your actual moderator permission check

                        // If the user has permission, display the Edit and Delete buttons
                        if ($is_author || $is_admin || $is_moderator) {
                    ?>
                            <div class="comment-actions">
                                <a href="delete_comment.php?id=<?= $comment['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this comment?')"><i class="fas fa-trash"></i></a>
                            </div>
                    <?php
                        }
                    }
                    ?>



                    <div class="comment-author">
                        <img src="<?= SITE_URL ?>/uploads/<?= $comment['avatar'] ?? 'default.png' ?>" alt="Avatar" class="avatar">
                        <a href="profile.php?id=<?= $comment['user_id'] ?>"><?= htmlspecialchars($comment['username']) ?></a>
                        <?php if (isLoggedIn() && $_SESSION['user_id'] != $comment['user_id']): ?>
                            <a href="messages.php?user=<?= $comment['user_id'] ?>" class="btn-message" title="Write a message">
                                <i class="fas fa-envelope"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                   <div class="comment-content">
    <p><?= nl2br(embedMediaLinks(html_entity_decode(htmlspecialchars($comment['content'])), 'comment')) ?>
</p>
    <?php if (!empty($comment['image'])): ?>
        <p><a data-fancybox data-caption="Comment Image" href="uploads/<?= htmlspecialchars($comment['image']) ?>"><img src="uploads/<?= htmlspecialchars($comment['image']) ?>" alt="Comment Image" class="comment-image"></a></p>
    <?php endif; ?>
</div>
                    <div class="comment-meta">
                        <span class="comment-date">
                            <i class="far fa-clock"></i> <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?>
                        </span>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('.file-upload-input');

    // Check if fileInput exists
    if (fileInput) {
        const fileUploadText = document.getElementById('file-upload-text');
        const imagePreview = document.getElementById('image-preview');

        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                fileUploadText.textContent = file.name;

                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.innerHTML = `
                        <div class="preview-container">
                            <img src="" alt="Preview">
                            <button type="button" class="btn btn-remove-image">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;

                    document.querySelector('.btn-remove-image').addEventListener('click', function() {
                        imagePreview.innerHTML = '';
                        fileInput.value = '';
                        fileUploadText.textContent = 'Upload an image (optional)';
                    });
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>