<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
 

// Check if the user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get the post ID from the query string
$post_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// Get the post data from the database
$post = getPostById($post_id);

// If the post doesn't exist, redirect to the homepage
if (!$post) {
    header('Location: index.php');
    exit;
}

// Get the current user's ID
$current_user_id = $_SESSION['user_id'];

// Check if the current user is the author of the post, an admin, or a moderator
$is_author = ($current_user_id == $post['user_id']);
$is_admin = hasPermission('admin');
$is_moderator = hasPermission('moderator'); // Replace with your actual moderator permission check

// If the user does not have permission to edit the post, redirect to the homepage
if (!$is_author && !$is_admin && !$is_moderator) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$errors = [];
$title = $post['title'];
$content = $post['content'];
$tags = $post['tags'];
$existingImage = $post['image']; // Existing image filename
$is_nsfw = $post['is_nsfw'];// Added $is_nsfw initialization
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $tags = sanitize($_POST['tags']);
    $is_nsfw = isset($_POST['is_nsfw']) ? 1 : 0;

    // Validate the form data
    if (empty($title)) {
        $errors[] = 'Title cannot be empty.';
    }
    if (empty($content)) {
        $errors[] = 'Content cannot be empty.';
    }

    $image = $existingImage; // Keep existing image by default
        // Check if "remove_image" is set, and if so, remove the image
        if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            // Delete the image file from the server
            if ($existingImage && file_exists(UPLOAD_DIR . $existingImage)) {
                unlink(UPLOAD_DIR . $existingImage);
            }
            $image = null; // Set image to null in the database
        } else if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadedImage = uploadImage($_FILES['image']);
            if ($uploadedImage) {
                $image = $uploadedImage; // Use newly uploaded image
                if ($existingImage && file_exists(UPLOAD_DIR . $existingImage)) {
                    unlink(UPLOAD_DIR . $existingImage); // Remove old image
                }
            } else {
                $errors[] = 'Could not upload image';
            }
        }

    // If there are no errors, update the post in the database
    if (empty($errors)) {
        if (updatePost($post_id, $title, $content, $image, $tags, $is_nsfw)) {// Pass is_nsfw to updatePost
            // Set a success message
            $_SESSION['message'] = 'Post updated successfully.';
            $_SESSION['message_type'] = 'success';

            // Redirect to the post page
            header("Location: post.php?id=$post_id");
            exit;
        } else {
            // Set an error message
            $_SESSION['message'] = 'Error updating post.';
            $_SESSION['message_type'] = 'danger';
        }
    }
}

$page_title = 'Edit Post';
include 'includes/header.php';
?>

<style>
    /* Add your form styling here - Consider reusing styles from create.php */
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

        .existing-image-container {
            margin-top: 10px;
            max-width: 300px;
        }

    </style>

<div class="container">
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
    <div class="create-post-container">
    <form action="edit_post.php?id=<?= $post_id ?>" method="POST" enctype="multipart/form-data" class="post-form">
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
    <input type="checkbox" name="is_nsfw" id="is_nsfw" value="1" <?php if ($is_nsfw) echo 'checked'; ?>>
    <label for="is_nsfw">NSFW (Not Safe For Work)</label>
</div>

                <div class="form-group">
            <label for="tags" class="form-label">Tags (separated by commas)</label>
            <input type="text" id="tags" name="tags" class="form-input" value="<?= htmlspecialchars($tags) ?>"
                   placeholder="girl, movie, funny">
        </div>

               <?php if ($existingImage): ?>
                    <div class="existing-image-container">
                        <img src="<?= SITE_URL ?>/uploads/<?= htmlspecialchars($existingImage) ?>" alt="Existing Image" style="max-width: 300px;">
                         <button type="button" class="btn btn-danger btn-small" onclick="removeImage()">
                              <i class="fas fa-trash"></i> Remove Image
                         </button>
                        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($existingImage) ?>">
                    </div>
                <?php endif; ?>

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

        function removeImage() {
            var existingImageContainer = document.querySelector('.existing-image-container');
            existingImageContainer.style.display = 'none';
        }
    </script>
</div>
<?php include 'includes/footer.php'; ?>