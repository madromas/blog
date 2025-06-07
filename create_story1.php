<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = sanitize($_POST['content'] ?? '');
    $image = null;
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image']);
        if (!$image) {
            $error = 'Не удалось загрузить изображение';
        }
    }
    
    if (empty($content) && !$image) {
        $error = 'Добавьте текст или изображение';
    }
    
    $user = getUser($_SESSION['user_id']);
    if (!$user || !isset($user['id'])) {
        $error = 'Пользователь не найден';
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
            
            $success = 'Ваша история опубликована!';
            $content = '';
        } catch (PDOException $e) {
            $error = 'Ошибка при публикации истории: ' . $e->getMessage();
        }
    }
}

$page_title = 'Создать историю';
include 'includes/header.php';
?>

<div class="container">
    <div class="create-story-container">
        <h1 class="page-title"><i class="fas fa-history"></i> Создать историю</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form action="create_story.php" method="POST" enctype="multipart/form-data" class="story-form">
            <div class="form-group">
                <textarea name="content" class="story-textarea" 
                          placeholder="Добавьте текст к истории (необязательно)..."><?= htmlspecialchars($content) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="image" class="file-upload-label story-upload">
                    <div class="upload-content">
                        <i class="fas fa-camera"></i>
                        <span>Добавить фото</span>
                    </div>
                    <input type="file" id="image" name="image" accept="image/*" class="file-upload-input">
                </label>
                <div id="image-preview" class="story-preview"></div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-share"></i> Опубликовать
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('.file-upload-input');
    const imagePreview = document.getElementById('image-preview');
    
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            const reader = new FileReader();
            
            reader.onload = function(event) {
                imagePreview.innerHTML = `
                    <div class="preview-container">
                        <img src="${event.target.result}" alt="Предпросмотр">
                        <button type="button" class="btn btn-remove-image">
                            <i class="fas fa-times"></i> Удалить
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
});
</script>

<?php include 'includes/footer.php'; ?>