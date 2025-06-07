<?php
/**
 * Функции для работы с приложением
 */

// Функция для санитизации ввода
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Проверка авторизации
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

// Получение информации о пользователе с защитой от ошибок
function getUser($id) {
    global $pdo;
    
    // Данные по умолчанию для гостя
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
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return $default_user;
        }
        
        // Убедимся, что все обязательные поля есть
        return array_merge($default_user, $user);
    } catch (PDOException $e) {
        error_log("Error obtaining user: " . $e->getMessage());
        return $default_user;
    }
}

// Проверка прав пользователя (обновленная версия)
function hasPermission($required_role) {
    if (!isLoggedIn()) return false;
    
    $user = getUser($_SESSION['user_id']);
    if (!$user) return false;
    
    // Добавляем гостя с самым низким уровнем прав
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

// Получение популярных постов с обработкой ошибок
function getPopularPosts($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, COUNT(c.id) as comments_count 
            FROM posts p 
            LEFT JOIN users u ON p.user_id = u.id 
            LEFT JOIN comments c ON p.id = c.post_id 
            GROUP BY p.id 
            ORDER BY (p.upvotes - p.downvotes) DESC, comments_count DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error receiving popular posts: " . $e->getMessage());
        return [];
    }
}

// Получение новых постов с обработкой ошибок
function getNewPosts($limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username 
            FROM posts p 
            LEFT JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error receiving new posts: " . $e->getMessage());
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
