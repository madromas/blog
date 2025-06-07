<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_achievements = getUserAchievements($user_id);
$level = getUserLevel($user_id);

// Разделяем достижения на полученные и неполученные
$completed = [];
$incomplete = [];

foreach ($user_achievements as $achievement) {
    if ($achievement['is_completed']) {
        $completed[] = $achievement;
    } else {
        // Не показываем скрытые достижения, если прогресс нулевой
        if (!$achievement['is_hidden'] || $achievement['progress'] > 0) {
            $incomplete[] = $achievement;
        }
    }
}

$page_title = 'My achievements';
include 'includes/header.php';
?>

<div class="achievements-page">
    <div class="achievements-header">
        <h1>My achievements</h1>
        <div class="user-level">
            <div class="level-info">
                <div class="level-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="level-details">
                    <span class="current-level">Current level: <?= $level['name'] ?></span>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= $level['progress'] ?>%"></div>
                    </div>
                    <span class="progress-text"><?= $total_points ?> из <?= $level['next_level_points'] ?> points</span>
                </div>
            </div>
        </div>
    </div>

    <div class="achievements-section">
        <h2><i class="fas fa-check-circle"></i> Achievements received</h2>
        <?php if (empty($all_achievements['completed'])): ?>
            <div class="empty-message">You don't have any achievements, yet.</div>
        <?php else: ?>
            <div class="achievements-grid">
                <?php foreach ($all_achievements['completed'] as $achievement): ?>
                    <div class="achievement-card completed">
                        <div class="achievement-icon">
                            <i class="fas <?= $achievement['icon'] ?>"></i>
                        </div>
                        <div class="achievement-details">
                            <h3><?= $achievement['title'] ?> <span class="points">+<?= $achievement['points'] ?></span></h3>
                            <p><?= $achievement['description'] ?></p>
                        </div>
                        <div class="achievement-badge">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="achievements-section">
        <h2><i class="fas fa-lock"></i> Unreached achievements</h2>
        <div class="achievements-grid">
            <?php foreach ($all_achievements['incomplete'] as $achievement): ?>
                <div class="achievement-card">
                    <div class="achievement-icon">
                        <i class="fas <?= $achievement['icon'] ?>"></i>
                    </div>
                    <div class="achievement-details">
                        <h3><?= $achievement['title'] ?> <span class="points">+<?= $achievement['points'] ?></span></h3>
                        <p><?= $achievement['description'] ?></p>
                        <?php if (isset($achievement['progress'])): ?>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?= ($achievement['progress'] / $achievement['target']) * 100 ?>%"></div>
                                <span class="progress-text"><?= $achievement['progress'] ?> из <?= $achievement['target'] ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php
// Функция для расчета уровня пользователя
function calculateLevel($points) {
    $levels = [
        ['name' => 'Bronze', 'points' => 0, 'next' => 100],
        ['name' => 'Silver', 'points' => 100, 'next' => 300],
        ['name' => 'Gold', 'points' => 300, 'next' => 600],
        ['name' => 'Platinum', 'points' => 600, 'next' => 1000],
        ['name' => 'Legend', 'points' => 1000, 'next' => PHP_INT_MAX]
    ];
    
    foreach ($levels as $level) {
        if ($points >= $level['points'] && $points < $level['next']) {
            $progress = ($points - $level['points']) / ($level['next'] - $level['points']) * 100;
            return [
                'name' => $level['name'],
                'progress' => round($progress),
                'next_level_points' => $level['next']
            ];
        }
    }
    
    return [
        'name' => 'Legend',
        'progress' => 100,
        'next_level_points' => '∞'
    ];
}
?>