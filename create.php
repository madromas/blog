<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
 

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$errors = [];
$title = $content = $tags = '';
$is_nsfw = false; // Initialize $is_nsfw

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $tags = sanitize($_POST['tags']);
    $is_nsfw = isset($_POST['is_nsfw']) ? 1 : 0;

    if (empty($title)) {
        $errors[] = 'Title cannot be empty';
    }
    
    if (empty($content)) {
        $errors[] = 'Post content cannot be empty';
    }
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image']);
        if (!$image) {
            $errors[] = 'Could not upload image';
        }
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, image, tags, is_nsfw, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())"); // Added is_nsfw
       $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $content,
            $image,
            $tags,
            $is_nsfw
        ]);
        
        $post_id = $pdo->lastInsertId();
        checkAchievement($_SESSION['user_id'], 'first_post');
        header("Location: post.php?id=$post_id");
        exit;
    }
}



$page_title = 'Create post';
include 'includes/header.php';
?>
<style>
    .create-post-container {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 25px;
        box-shadow: var(--shadow);
        margin: 20px 0;
    }

    .page-title {
        margin-bottom: 25px;
        display: flex;
        align-items: center;
    }

    .page-title i {
        margin-right: 10px;
        color: var(--accent-green);
    }

    /* Alert Styles */
    .alert {
        padding: 15px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
    }

    .alert-error {
        background-color: rgba(244, 67, 54, 0.1);
        border-left: 4px solid #f44336;
    }

    .alert-title {
        font-weight: bold;
        margin-bottom: 10px;
    }

    .error-list {
        list-style: none;
    }

    .error-list li {
        margin-bottom: 5px;
        display: flex;
        align-items: center;
    }

    .error-list i {
        margin-right: 8px;
        color: #f44336;
    }

    /* Form Styles */
    .post-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-label {
        font-weight: 600;
        color: var(--text-primary);
    }

    .form-input, .form-textarea {
        padding: 12px 15px;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid #333;
        border-radius: var(--border-radius);
        color: var(--text-primary);
        font-size: 1rem;
        transition: var(--transition);
    }

    .form-input:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--accent-green);
        box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
    }

    .form-textarea {
        min-height: 200px;
        resize: vertical;
    }

    /* File Upload */
    .file-upload-label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        background-color: rgba(76, 175, 80, 0.1);
        border: 1px dashed var(--accent-green);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
    }

    .file-upload-label:hover {
        background-color: rgba(76, 175, 80, 0.2);
    }

    .file-upload-label i {
        color: var(--accent-green);
    }

    .file-upload-input {
        display: none;
    }

    /* Image Preview */
    .image-preview {
        margin-top: 10px;
    }

    .preview-container {
        position: relative;
        max-width: 300px;
        border-radius: var(--border-radius);
        overflow: hidden;
    }

    .preview-container img {
        width: 100%;
        display: block;
    }

    .btn-remove-image {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    
    @media (max-width: 768px) {
        .create-post-container {
            padding: 15px;
        }
        
        .page-title {
            font-size: 1.5rem;
        }
        
        .form-input, .form-textarea, .file-upload-label {
            padding: 10px 12px;
        }
        
        .btn-large {
            width: 100%;
        }
    }
</style>

<div class="container">
    <div class="create-post-container">
        <h1 class="page-title"><i class="fas fa-plus-circle"></i> Create new post</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <div class="alert-title">Errors when filling out the form:</div>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><i class="fas fa-exclamation-circle"></i> <?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="create.php" method="POST" enctype="multipart/form-data" class="post-form">
            <div class="form-group">
                <label for="title" class="form-label">Title</label>
                <input type="text" id="title" name="title" class="form-input" value="<?= htmlspecialchars($title) ?>" required
                       placeholder="Come up with a catchy headline...">
            </div>
            
            <div class="form-group">
                <label for="content" class="form-label">Content</label>
                <textarea id="content" name="content" class="form-textarea" rows="10" required
                          placeholder="Write something interesting..."><?= htmlspecialchars($content) ?></textarea>
            </div>
            
<div class="">
                <input type="checkbox" name="is_nsfw" id="is_nsfw" value="1">
    <label for="is_nsfw">NSFW (Not Safe For Work)</label>
</div>
            
            <div class="form-group">
                <label for="tags" class="form-label">Tags (separated by commas)</label>
                <input type="text" id="tags" name="tags" class="form-input" value="<?= htmlspecialchars($tags) ?>"
                       placeholder="girl, movie, funny">
            </div>
            
            <div class="form-group">
                <label for="image" class="file-upload-label">
                    <i class="fas fa-image"></i> 
                    <span id="file-upload-text">Upload an image (optional)</span>
                    <input type="file" id="image" name="image" accept="image/*" class="file-upload-input">
                </label>
                <div id="image-preview" class="image-preview"></div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-paper-plane"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('.file-upload-input');
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
                        <img src="${event.target.result}" alt="Preview">
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
});
</script>

<?php include 'includes/footer.php'; ?>