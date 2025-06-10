<?php
require_once 'includes/config.php';
require_once 'includes/functions.php'; require_once 'includes/auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = sanitize($_POST['content'] ?? '');
    $image = null;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image']);
        if (!$image) {
            $error = 'Could not upload image';
        }
    }
    
    if (empty($content) && !$image) {
        $error = 'Add text or image';
    }
    
    $user = getUser($_SESSION['user_id']);
    if (!$user || !isset($user['id'])) {
        $error = 'User not found';
    }
    
    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO stories (user_id, content, image, expires_at) 
                VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $content,
                $image
            ]);
            
            $success = 'Your story has been published!';
            $content = '';
        } catch (PDOException $e) {
            $error = 'Error publishing a story: ' . $e->getMessage();
        }
    }
}

$page_title = 'Create story';
include 'includes/header.php';
?>

<style>
    /* Create Story Page */
    .create-story-container {
        max-width: 600px;
        margin: 20px auto;
    }
    
    .story-form-wrapper {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 25px;
        box-shadow: var(--shadow);
    }
    
    .form-title {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        font-size: 1.5rem;
    }
    
    .form-title i {
        margin-right: 10px;
        color: var(--accent-green);
    }
    
    /* Alerts */
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
    
    .alert-success {
        background-color: rgba(76, 175, 80, 0.1);
        color: var(--accent-green);
        border-left: 4px solid var(--accent-green);
    }
    
    .alert-error {
        background-color: rgba(244, 67, 54, 0.1);
        color: #f44336;
        border-left: 4px solid #f44336;
    }
    
    .btn-view-stories {
        margin-left: auto;
        padding: 5px 10px;
        background-color: rgba(76, 175, 80, 0.2);
        color: var(--accent-green);
        border-radius: var(--border-radius);
        font-size: 0.9rem;
        transition: var(--transition);
    }
    
    .btn-view-stories:hover {
        background-color: rgba(76, 175, 80, 0.3);
    }
    
    /* Story Form */
    .story-form {
        margin-top: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .story-textarea {
        width: 100%;
        min-height: 120px;
        padding: 15px;
        border-radius: var(--border-radius);
        background-color: var(--darker-bg);
        border: 1px solid #333;
        color: var(--text-primary);
        font-size: 1rem;
        resize: vertical;
        transition: var(--transition);
    }
    
    .story-textarea:focus {
        border-color: var(--accent-green);
        outline: none;
    }
    
    .textarea-counter {
        text-align: right;
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin-top: 5px;
    }
    
    /* Media Upload */
    .media-upload {
        margin-bottom: 20px;
    }
    
    .upload-label {
        display: block;
        padding: 20px;
        border: 2px dashed #444;
        border-radius: var(--border-radius);
        text-align: center;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .upload-label:hover {
        border-color: var(--accent-green);
        background-color: rgba(76, 175, 80, 0.05);
    }
    
    .upload-content {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .upload-content i {
        font-size: 2rem;
        color: var(--accent-green);
        margin-bottom: 10px;
    }
    
    .upload-text {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .upload-hint {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }
    
    .upload-input {
        display: none;
    }
    
    /* Image Preview */
    .image-preview {
        margin-top: 15px;
    }
    
    .preview-container {
        position: relative;
        border-radius: var(--border-radius);
        overflow: hidden;
    }
    
    .preview-container img {
        width: 100%;
        max-height: 300px;
        object-fit: contain;
        border-radius: var(--border-radius);
    }
    
    .btn-remove-image {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 5px 10px;
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
    }
    
    .btn-remove-image:hover {
        background-color: rgba(0, 0, 0, 0.9);
    }
    
    /* Form Actions */
    .form-actions {
        text-align: right;
    }
    
    .btn-publish {
        padding: 10px 25px;
        background: var(--accent-gradient);
        color: white;
        border: none;
        border-radius: var(--border-radius);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .btn-publish:hover {
        opacity: 0.9;
    }
    
    .btn-publish i {
        margin-right: 8px;
    }
    
    @media (max-width: 768px) {
        .create-story-container {
            padding: 0 15px;
        }
        
        .story-form-wrapper {
            padding: 20px;
        }
    }
</style>

<div class="create-story-container">
    <div class="story-form-wrapper">
        <h1 class="form-title"><i class="fas fa-camera-retro"></i> New story</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <a href="stories.php" class="btn-view-stories">Show all stories</a>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form action="create_story.php" method="POST" enctype="multipart/form-data" class="story-form">
            <div class="form-group">
                <textarea name="content" id="story-content" class="story-textarea" 
                          placeholder="Write something interesting..."><?= htmlspecialchars($content) ?></textarea>
                <div class="textarea-counter"><span id="char-count">0</span>/500</div>
            </div>
            
            <div class="media-upload">
                <label for="image-upload" class="upload-label">
                    <div class="upload-content">
                        <i class="fas fa-image"></i>
                        <span class="upload-text">Add photo</span>
                        <span class="upload-hint">(up to 5MB, JPG/PNG)</span>
                    </div>
                    <input type="file" id="image-upload" name="image" accept="image/*" class="upload-input">
                </label>
                <div id="image-preview" class="image-preview"></div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-publish">
                    <i class="fas fa-paper-plane"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка загрузки изображения
    const fileInput = document.getElementById('image-upload');
    const imagePreview = document.getElementById('image-preview');
    
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(event) {
                imagePreview.innerHTML = `
                    <div class="preview-container">
                        <img src="${event.target.result}" alt="Preview">
                        <button type="button" class="btn-remove-image">
                            <i class="fas fa-times"></i> Delete
                        </button>
                    </div>
                `;
                
                document.querySelector('.btn-remove-image').addEventListener('click', function() {
                    imagePreview.innerHTML = '';
                    fileInput.value = '';
                });
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    // Счетчик символов
    const textarea = document.getElementById('story-content');
    const charCount = document.getElementById('char-count');
    
    textarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });
});
</script>

<?php include 'includes/footer.php'; ?>