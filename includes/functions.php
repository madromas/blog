<?php
/**
 * Функции для работы с приложением
 */

// Corrected sanitize function
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return trim(strip_tags($data)); // Removed htmlspecialchars()
}

// Проверка авторизации
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

// Получение информации о пользователе с защитой от ошибок (Getting user information with error protection)
function getUser($id) {
    global $pdo;

    // Данные по умолчанию для гостя (Default data for guest)
    $default_user = [
        'id' => 0,
        'username' => 'Guest',
        'email' => '',
        'avatar' => 'default.png',
        'role' => 'guest',
        'rating' => 0,
        'about' => '',
        'created_at' => date('Y-m-d H:i:s')
    ];

    if (empty($id) || !is_numeric($id)) {
        return $default_user;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, email, avatar, role, rating, about, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return $default_user;
        }

        return $user; //No need for array_merge() we have all the colums
    } catch (PDOException $e) {
        error_log("Error obtaining user: " . $e->getMessage());
        return $default_user;
    }
}

// Проверка прав пользователя (обновленная версия) (Checking user rights (updated version))
function hasPermission($required_role) {
    if (!isLoggedIn()) return false;

    $user = getUser($_SESSION['user_id']);
    if (!$user) return false;

    // Добавляем гостя с самым низким уровнем прав (Adding guest with the lowest level of rights)
    $roles = ['guest' => 0, 'user' => 1, 'moderator' => 2, 'admin' => 3];

    $user_level = $roles[$user['role']] ?? 0;
    $required_level = $roles[$required_role] ?? 0;

    return $user_level >= $required_level;
}

// Проверка, является ли пользователь автором контента
function isAuthor($content_type, $content_id) {
    if (!isLoggedIn()) return false;
    
    global $pdo;
    
    try {
        switch ($content_type) {
            case 'post':
                $stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ? LIMIT 1");
                break;
            case 'comment':
                $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ? LIMIT 1");
                break;
            case 'story':
                $stmt = $pdo->prepare("SELECT user_id FROM stories WHERE id = ? LIMIT 1");
                break;
            default:
                return false;
        }
        
        $stmt->execute([$content_id]);
        $content = $stmt->fetch();
        
        return $content && $content['user_id'] == $_SESSION['user_id'];
    } catch (PDOException $e) {
        error_log("Error verifying author: " . $e->getMessage());
        return false;
    }
}

// Загрузка изображения с улучшенной обработкой ошибок
function uploadImage($file) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return false;
    }

    // Проверка ошибок загрузки
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return false;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            error_log('Allowed file size exceeded');
            return false;
        default:
            error_log('Unknown uploading error');
            return false;
    }

    // Проверка на реальное изображение
    if (!getimagesize($file['tmp_name'])) {
        return false;
    }

    $target_dir = rtrim(UPLOAD_DIR, '/') . '/';
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Проверка формата файла
    if (!in_array($imageFileType, $allowed_types)) {
        return false;
    }

    // Проверка размера файла (макс 5MB)
    if ($file['size'] > 5000000) {
        return false;
    }

    // Генерация уникального имени
    $new_filename = uniqid('img_') . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;

    // Создаем папку, если ее нет
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Пытаемся переместить файл
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Устанавливаем правильные права
        chmod($target_file, 0644);
        return $new_filename;
    }

    return false;
}

// Форматирование времени (сколько времени прошло)
function time_elapsed_string($datetime, $full = false) {
    try {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        
        foreach ($string as $k => &$v) {
            if (!$diff->$k) {
                unset($string[$k]);
                continue;
            }
            $v = $diff->$k . ' ' . $v;
            if ($diff->$k > 1) {
                if ($k == 'm') {
                    $v .= 's';
                } elseif ($k == 'h') {
                    $v .= 's';
                } elseif ($k == 'd') {
                    $v .= 's';
                } elseif ($k == 'i' || $k == 's') {
                    $v .= 's';
                } else {
                    $v .= 's';
                }
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }
        
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    } catch (Exception $e) {
        error_log("Time formatting error: " . $e->getMessage());
        return 'not long ago';
    }
}

// Сколько времени осталось до истечения истории
function time_remaining($datetime) {
    try {
        $now = new DateTime;
        $expires = new DateTime($datetime);
        
        if ($now >= $expires) {
            return 'expired';
        }
        
        $diff = $now->diff($expires);
        
        if ($diff->h > 0) {
            return $diff->h . ' h ' . $diff->i . ' min';
        }
        
        return $diff->i . ' min';
    } catch (Exception $e) {
        error_log("Time calculation error: " . $e->getMessage());
        return 'soon';
    }
}

// Improved getPopularPosts function with pagination support
function getPopularPosts($page, $per_page) {
    global $pdo;

    try {
        $offset = ($page - 1) * $per_page; // Calculate the offset

        // Get posts_per_page setting from the database
        $stmt = $pdo->prepare("SELECT posts_per_page FROM settings WHERE id = 1");
        $stmt->execute();
        $setting = $stmt->fetch(PDO::FETCH_ASSOC);
        $posts_per_page = (int)$setting['posts_per_page'];

        // SQL query with pagination
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, COUNT(c.id) as comments_count
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id
            LEFT JOIN comments c ON p.id = c.post_id
            GROUP BY p.id
            ORDER BY (p.upvotes - p.downvotes) DESC, comments_count DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error receiving popular posts: " . $e->getMessage());
        return [];
    }
}

// Improved getNewPosts function with pagination support
function getNewPosts($page, $per_page) {
    global $pdo;

    try {
        $offset = ($page - 1) * $per_page; // Calculate the offset

        // Get posts_per_page setting from the database
        $stmt = $pdo->prepare("SELECT posts_per_page FROM settings WHERE id = 1");
        $stmt->execute();
        $setting = $stmt->fetch(PDO::FETCH_ASSOC);
        $posts_per_page = (int)$setting['posts_per_page'];

        // SQL query with pagination
        $stmt = $pdo->prepare("
            SELECT p.*, u.username
            FROM posts p
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error receiving popular posts: " . $e->getMessage());
        return [];
    }
}


// Получение активных историй
function getActiveStories($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, u.username, u.avatar 
            FROM stories s
            JOIN users u ON s.user_id = u.id
            WHERE s.is_active = TRUE AND s.expires_at > NOW()
            ORDER BY s.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error receiving history: " . $e->getMessage());
        return [];
    }
}

/**
 * Получение всех достижений
 */
function getAllAchievements() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM achievements ORDER BY points ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Achievement acquisition error: " . $e->getMessage());
        return [];
    }
}

/**
 * Получение прогресса пользователя по достижениям
 */
function getUserAchievements($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, ua.progress, ua.is_completed, ua.completed_at 
            FROM achievements a
            LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
            ORDER BY a.points ASC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error receiving user achievements: " . $e->getMessage());
        return [];
    }
}

function getUnreadMessagesCount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM private_messages 
        WHERE receiver_id = ? AND is_read = FALSE
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}
/**
 * Проверка и обновление прогресса достижения
 */
function checkAchievement($user_id, $achievement_id, $progress = 1) {
    global $pdo;
    
    try {
        // Получаем данные достижения
        $stmt = $pdo->prepare("SELECT * FROM achievements WHERE id = ?");
        $stmt->execute([$achievement_id]);
        $achievement = $stmt->fetch();
        
        if (!$achievement) return false;
        
        // Получаем текущий прогресс пользователя
        $stmt = $pdo->prepare("
            SELECT * FROM user_achievements 
            WHERE user_id = ? AND achievement_id = ?
        ");
        $stmt->execute([$user_id, $achievement_id]);
        $user_achievement = $stmt->fetch();
        
        // Если достижение уже выполнено, ничего не делаем
        if ($user_achievement && $user_achievement['is_completed']) {
            return true;
        }
        
        // Рассчитываем новый прогресс
        $new_progress = $user_achievement ? ($user_achievement['progress'] + $progress) : $progress;
        $is_completed = $achievement['target_value'] ? ($new_progress >= $achievement['target_value']) : true;
        
        // Обновляем или создаем запись
        if ($user_achievement) {
            $stmt = $pdo->prepare("
                UPDATE user_achievements 
                SET progress = ?, is_completed = ?, completed_at = ?
                WHERE user_id = ? AND achievement_id = ?
            ");
            $stmt->execute([
                $new_progress,
                $is_completed,
                $is_completed ? date('Y-m-d H:i:s') : null,
                $user_id,
                $achievement_id
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO user_achievements 
                (user_id, achievement_id, progress, is_completed, completed_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $achievement_id,
                $new_progress,
                $is_completed,
                $is_completed ? date('Y-m-d H:i:s') : null
            ]);
        }
        
        // Если достижение выполнено, добавляем очки пользователю
        if ($is_completed) {
            addUserPoints($user_id, $achievement['points']);
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Achievement verification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Добавление очков пользователю
 */
function addUserPoints($user_id, $points) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET rating = rating + ? 
            WHERE id = ?
        ");
        return $stmt->execute([$points, $user_id]);
    } catch (PDOException $e) {
        error_log("Error adding points: " . $e->getMessage());
        return false;
    }
}

/**
 * Получение уровня пользователя
 */
function getUserLevel($user_id) {
    global $pdo;
    
    try {
        // Получаем общее количество очков пользователя
        $stmt = $pdo->prepare("SELECT rating FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) return null;
        
        // Получаем уровни из БД
        $levels = $pdo->query("SELECT * FROM levels ORDER BY min_points ASC")->fetchAll();
        
        foreach ($levels as $level) {
            if ($user['rating'] >= $level['min_points'] && 
                ($level['next_level_points'] > $user['rating'] || $level['next_level_points'] == 0)) {
                
                $progress = $level['next_level_points'] > 0 
                    ? (($user['rating'] - $level['min_points']) / ($level['next_level_points'] - $level['min_points'])) * 100
                    : 100;
                
                return [
                    'name' => $level['level_name'],
                    'progress' => round($progress),
                    'next_level_points' => $level['next_level_points'] ?: '∞'
                ];
            }
        }
        
        return null;
    } catch (PDOException $e) {
        error_log("Error getting the level: " . $e->getMessage());
        return null;
    }
}

/**
 * Получает завершенные достижения пользователя
 */
function getCompletedAchievements($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, ua.completed_at 
            FROM user_achievements ua
            JOIN achievements a ON ua.achievement_id = a.id
            WHERE ua.user_id = ? AND ua.is_completed = TRUE
            ORDER BY ua.completed_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Achievement acquisition error: " . $e->getMessage());
        return [];
    }
}

/**
 * Deletes a post from the database.
 *
 * @param int $post_id The ID of the post to delete.
 * @return bool True on success, false on failure.
 */
function deletePost(int $post_id): bool {
    global $pdo; // Access the PDO database connection

    $sql = "DELETE FROM posts WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement in deletePost: " . print_r($pdo->errorInfo(), true));
        return false;
    }

    $result = $stmt->execute([$post_id]); // Use an array for parameters

    if ($result) {
        $stmt->closeCursor();  // Use closeCursor instead of close
        return true;
    } else {
        error_log("Error executing statement in deletePost: " . print_r($stmt->errorInfo(), true));
        $stmt->closeCursor();  // Use closeCursor instead of close
        return false;
    }
}

/**
 * Retrieves a post from the database by its ID.
 *
 * @param int $post_id The ID of the post to retrieve.
 * @return array|null The post data as an associative array, or null if not found.
 */
function getPostById(int $post_id): ?array {
    global $pdo;

    $sql = "SELECT * FROM posts WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement in getPostById: " . print_r($pdo->errorInfo(), true));
        return null;
    }

    $stmt->execute([$post_id]);  // Use an array for parameters
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt->closeCursor(); // Use closeCursor instead of close

    return $post ?: null;  // Use the null coalescing operator to return null if $post is falsey
}

/**
 * Updates a post in the database.
 *
 * @param int $post_id The ID of the post to update.
 * @param string $title The new title of the post.
 * @param string $content The new content of the post.
 * @param string $image The new image URL of the post.
 * @return bool True on success, false on failure.
 */
function updatePost(int $post_id, string $title, string $content, ?string $image, string $tags, $is_nsfw): bool {
    global $pdo;

    // Explicitly cast $is_nsfw to an integer (0 or 1)
    $is_nsfw_int = (int) $is_nsfw;

    $sql = "UPDATE posts SET title = ?, content = ?, image = ?, tags = ?, is_nsfw = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement in updatePost: " . print_r($pdo->errorInfo(), true));
        return false;
    }

    $result = $stmt->execute([$title, $content, $image, $tags, $is_nsfw_int, $post_id]);

    if ($result) {
        $stmt->closeCursor();
        return true;
    } else {
        error_log("Error executing statement in updatePost: " . print_r($stmt->errorInfo(), true));
        $stmt->closeCursor();
        return false;
    }
}

function createPost(string $title, string $content, ?string $image, string $tags, bool $is_nsfw): bool {
    global $pdo;

    $sql = "INSERT INTO posts (title, content, image, tags, is_nsfw) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement in createPost: " . print_r($pdo->errorInfo(), true));
        return false;
    }

    $result = $stmt->execute([$title, $content, $image, $tags, $is_nsfw]);

    if ($result) {
        $stmt->closeCursor();
        return true;
    } else {
        error_log("Error executing statement in createPost: " . print_r($stmt->errorInfo(), true));
        $stmt->closeCursor();
        return false;
    }
}

function deleteComment(int $comment_id): bool {
    global $pdo;

    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement in deleteComment: " . print_r($pdo->errorInfo(), true));
        return false;
    }

    $result = $stmt->execute([$comment_id]);

    if ($result) {
        $stmt->closeCursor();
        return true;
    } else {
        error_log("Error executing statement in deleteComment: " . print_r($stmt->errorInfo(), true));
        $stmt->closeCursor();
        return false;
    }
}

function getCommentById(int $comment_id): array|false {
    global $pdo;

    $sql = "SELECT * FROM comments WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement in getCommentById: " . print_r($pdo->errorInfo(), true));
        return false;
    }

    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt->closeCursor();
    return $comment ?: false;
}

function getAllTags(): array
{
    global $pdo;

    $sql = "SELECT DISTINCT tags FROM posts WHERE tags IS NOT NULL AND tags != ''";
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement: " . print_r($pdo->errorInfo(), true));
        return [];
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $tags = [];
    foreach ($results as $result) {
        $tagArray = explode(',', $result);
        foreach ($tagArray as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $tags[] = $tag;
            }
        }
    }

    // Remove duplicate tags
    $tags = array_unique($tags);

    // Sort the tags alphabetically
    sort($tags);

    $stmt->closeCursor();
    return $tags;
}

function getLatestComments(int $limit = 10): array {
    global $pdo;

    $sql = "
        SELECT 
            comments.*,
            users.username,
            users.avatar,
            posts.title AS post_title,
            posts.id AS post_id
        FROM 
            comments
        JOIN 
            users ON comments.user_id = users.id
        JOIN
            posts ON comments.post_id = posts.id
        ORDER BY 
            comments.created_at DESC
        LIMIT ?
    ";

    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement: " . print_r($pdo->errorInfo(), true));
        return [];
    }

    $stmt->bindValue(1, $limit, PDO::PARAM_INT);  // Use bindValue for LIMIT
    $stmt->execute();
    $latestComments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt->closeCursor();
    return $latestComments;
}

function getUserProfile(int $user_id, ?int $viewer_id = null): ?array
{
    global $pdo;

    $sql = "
        SELECT 
            *
        FROM 
            users
        WHERE 
            id = ?
    ";

    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement: " . print_r($pdo->errorInfo(), true));
        return null;
    }

    $stmt->execute([$user_id]);
    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt->closeCursor();

    if (!$userProfile) {
        return null; // User not found
    }

    // Check Profile Visibility
    if ($userProfile['profile_visibility'] === 'private') {
        // Only the user themselves can view a private profile
        if ($viewer_id !== $user_id) {
            return null; // Not authorized
        }
    } elseif ($userProfile['profile_visibility'] === 'registered') {
        // Only registered users can view the profile
        if ($viewer_id === null) {
            return null; // Not authorized - User is not logged in
        }
    }

    return $userProfile;
}

function incrementPostViews(int $post_id): void
{
    global $pdo;

    $sql = "
        UPDATE 
            posts
        SET 
            views = views + 1
        WHERE 
            id = ?
    ";

    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement: " . print_r($pdo->errorInfo(), true));
        return;
    }

    $stmt->execute([$post_id]);
    $stmt->closeCursor();
}

/**
 * Deletes a post from the database.
 *
 * @param int $post_id The ID of the post to delete.
 * @return bool True on success, false on failure.
 */
function deleteStory(int $story_id): bool {
    global $pdo; // Access the PDO database connection

    $sql = "DELETE FROM stories WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement in deleteStory: " . print_r($pdo->errorInfo(), true));
        return false;
    }

    $result = $stmt->execute([$story_id]); // Use an array for parameters

    if ($result) {
        $stmt->closeCursor();  // Use closeCursor instead of close
        return true;
    } else {
        error_log("Error executing statement in deleteStory: " . print_r($stmt->errorInfo(), true));
        $stmt->closeCursor();  // Use closeCursor instead of close
        return false;
    }
}

function embedMediaLinks($text, $context = 'post') { // Added $context parameter
  // Regular expression to find URLs ending in common media extensions
  $pattern = '/(https?:\/\/[^\s]+?\.(?:mp4|avi|mov|webm|ogg|mp3|jpg|jpeg|png|gif|webp))/i';

  // Callback function to replace URLs with HTML tags
  $text = preg_replace_callback($pattern, function ($matches) use ($context) { // Added use ($context)
    $url = htmlspecialchars($matches[0]); // Escape the URL for safety
    $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION)); // Get the file extension

    switch ($extension) {
      case 'mp4':
      case 'avi':
      case 'mov':
      case 'webm':
      case 'ogg':
          if ($context === 'post') {
            return '<video src="' . $url . '" type="video/' . $extension . '" controls>Your browser does not support the video tag.</video>';
          } else {
            return $url; // Leave as plain text in comments
          }
      case 'mp3':
          if ($context === 'post') {
            return '<audio controls><source src="' . $url . '" type="audio/mpeg">Your browser does not support the audio element.</audio>';
          } else {
            return $url;
          }
      case 'jpg':
      case 'jpeg':
      case 'png':
      case 'gif':
      case 'webp':
        return '<img src="' . $url . '" alt="Embedded Image">';
      default:
        return $url; // If not a recognized media type, leave the URL as is
    }
  }, $text);

  return $text;
}

function truncateText($text, $length, $ellipsis = "...") {
    if (mb_strlen($text, 'UTF-8') <= $length) {
        return $text;
    }

    $truncated = mb_substr($text, 0, $length, 'UTF-8');

    // Make sure we don't break any HTML tags
    if (preg_match('/<(\w+)[^>]*>$/', $truncated, $matches)) {
        $truncated = mb_substr($truncated, 0, mb_strlen($truncated, 'UTF-8') - mb_strlen($matches[0], 'UTF-8'), 'UTF-8');
    }

    return $truncated . $ellipsis;
}

function checkRememberMeCookie($pdo) {
    if (isset($_COOKIE['remember_me'])) {
        list($selector, $token) = explode(':', $_COOKIE['remember_me']);

        if (ctype_xdigit($selector) && ctype_xdigit($token) && strlen($selector) === 16 && strlen($token) === 64) { //Validate selector and token format
            $hashedToken = hash('sha256', hex2bin($token));

            $stmt = $pdo->prepare("
                SELECT user_id FROM auth_tokens
                WHERE selector = ? AND hashed_token = ? AND expiry > NOW()
            ");
            $stmt->execute([$selector, $hashedToken]);
            $result = $stmt->fetch();

            if ($result) {
                $_SESSION['user_id'] = $result['user_id'];

                // Extend the expiry of the cookie and database entry (optional)
                $expiry = time() + (30 * 24 * 60 * 60); // Extend for another 30 days
                $expiryDate = date('Y-m-d H:i:s', $expiry);

                $updateStmt = $pdo->prepare("
                    UPDATE auth_tokens SET expiry = ? WHERE selector = ?
                ");
                $updateStmt->execute([$expiryDate, $selector]);

                setcookie(
                    'remember_me',
                    $selector . ':' . $token,  //Keep the original token
                    $expiry,
                    '/',
                    '',
                    true,
                    true
                );

                return true; // User was automatically logged in
            } else {
                // Token is invalid or expired - delete the cookie
                setcookie('remember_me', '', time() - 3600, '/');  //Clear the cookie
                $deleteStmt = $pdo->prepare("DELETE FROM auth_tokens WHERE selector = ?");
                $deleteStmt->execute([$selector]);
            }
        } else {
            //Invalid cookie format. Delete the cookie
            setcookie('remember_me', '', time() - 3600, '/');
        }
    }
    return false; // User was not automatically logged in
}

function getReportReasons() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, reason FROM report_reasons");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting report reasons: " . $e->getMessage());
        return [];
    }
}

/**
 * Возвращает цвет для уровня
 */

function getLevelColor($level_name) {
    switch (strtolower($level_name)) {
        case 'bronze': return '#cd7f32';
        case 'silver': return '#c0c0c0';
        case 'gold': return '#ffd700';
        case 'platinum': return '#e5e4e2';
        case 'legend': return '#ff4500';
        default: return '#6c757d';
    }
}
