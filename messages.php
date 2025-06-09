<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id']) && isset($_POST['content'])) {
    $receiver_id = (int)$_POST['receiver_id'];
    $content = sanitize($_POST['content']);
    
    if (!empty($content)) {
        $stmt = $pdo->prepare("
            INSERT INTO private_messages (sender_id, receiver_id, content) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user_id, $receiver_id, $content]);
        
        header("Location: messages.php?user=$receiver_id");
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.avatar, MAX(pm.created_at) as last_message_time
    FROM users u
    JOIN private_messages pm ON (
        (pm.sender_id = u.id AND pm.receiver_id = ?) OR 
        (pm.receiver_id = u.id AND pm.sender_id = ?)
    )
    WHERE u.id != ?
    GROUP BY u.id
    ORDER BY last_message_time DESC
");
$stmt->execute([$user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

$messages = [];
$current_chat_user = null;
if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $chat_user_id = (int)$_GET['user'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$chat_user_id]);
    $current_chat_user = $stmt->fetch();
    
    if ($current_chat_user) {
        $pdo->prepare("
            UPDATE private_messages 
            SET is_read = TRUE 
            WHERE receiver_id = ? AND sender_id = ? AND is_read = FALSE
        ")->execute([$user_id, $chat_user_id]);
        
        $stmt = $pdo->prepare("
            SELECT pm.*, u.username, u.avatar 
            FROM private_messages pm
            JOIN users u ON pm.sender_id = u.id
            WHERE (pm.sender_id = ? AND pm.receiver_id = ?) OR 
                  (pm.sender_id = ? AND pm.receiver_id = ?)
            ORDER BY pm.created_at ASC
        ");
        $stmt->execute([$user_id, $chat_user_id, $chat_user_id, $user_id]);
        $messages = $stmt->fetchAll();
    }
}

$page_title = 'Personal messages';
include 'includes/header.php';
?>
<style>
    .messages-container {
        display: flex;
        height: calc(100vh - 150px);
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    /* Conversations Sidebar */
    .conversations-sidebar {
        width: 300px;
        border-right: 1px solid #333;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 15px;
        border-bottom: 1px solid #333;
    }

    .sidebar-header h2 {
        margin: 0;
        display: flex;
        align-items: center;
    }

    .sidebar-header i {
        margin-right: 10px;
        color: var(--accent-green);
    }

    .empty-conversations {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        text-align: center;
        color: var(--text-secondary);
    }

    .empty-conversations i {
        font-size: 2rem;
        margin-bottom: 15px;
        color: var(--accent-green);
    }

    .conversations-list {
        list-style: none;
        flex: 1;
        overflow-y: auto;
    }

    .conversations-list li {
        border-bottom: 1px solid #333;
    }

    .conversations-list li.active {
        background-color: rgba(76, 175, 80, 0.1);
    }

    .conversation-link {
        display: flex;
        align-items: center;
        padding: 15px;
        transition: var(--transition);
    }

    .conversation-link:hover {
        background-color: rgba(76, 175, 80, 0.1);
    }

    .conversation-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 15px;
    }

    .conversation-info {
        flex: 1;
    }

    .conversation-username {
        display: block;
        font-weight: 600;
    }

    .conversation-time {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    /* Chat Container */
    .chat-container {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        padding: 15px;
        border-bottom: 1px solid #333;
        display: flex;
        align-items: center;
    }

    .chat-user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .chat-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .user-status {
        font-size: 0.8rem;
        color: var(--accent-green);
    }

    .messages-list {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: rgba(0, 0, 0, 0.1);
    }

    .empty-messages {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--text-secondary);
    }

    .empty-messages i {
        font-size: 2rem;
        margin-bottom: 15px;
        color: var(--accent-green);
    }

    /* Message Styles */
    .message {
        display: flex;
        margin-bottom: 15px;
    }

    .message.sent {
        justify-content: end;
    }

    .message.received {
        justify-content: flex-start;
    }

    .message-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
        align-self: flex-end;
    }

    .message-content-container {
        max-width: 70%;
    }

    .message-content {
        padding: 12px 15px;
        border-radius: 18px;
        line-height: 1.4;
        word-break: break-word;
    }

    .message.sent .message-content {
        background: var(--accent-gradient);
        color: white;
        border-top-right-radius: 0;
    }

    .message.received .message-content {
        background-color: var(--card-bg);
        border-top-left-radius: 0;
    }

    .message-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 5px;
        margin-top: 5px;
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .read-icon {
        font-size: 0.7rem;
    }

    /* Message Form */
    .message-form-container {
        padding: 15px;
        border-top: 1px solid #333;
    }

    .message-form {
        display: flex;
    }

    .input-group {
        flex: 1;
        display: flex;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 25px;
        overflow: hidden;
    }

    .message-form textarea {
        flex: 1;
        padding: 12px 15px;
        background: none;
        border: none;
        color: var(--text-primary);
        resize: none;
        max-height: 100px;
    }

    .message-form textarea:focus {
        outline: none;
    }

    .btn-send {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--accent-gradient);
        color: white;
        border: none;
        cursor: pointer;
    }

    /* Chat Placeholder */
    .chat-placeholder {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .placeholder-content {
        text-align: center;
        color: var(--text-secondary);
    }

    .placeholder-icon {
        font-size: 3rem;
        color: var(--accent-green);
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .messages-container {
            flex-direction: column;
            height: auto;
        }

        .conversations-sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid #333;
        }

        .conversations-list {
            max-height: 300px;
        }

        .messages-list {
            min-height: 300px;
        }

        .message-content-container {
            max-width: 85%;
        }
    }
</style>

<div class="container">
    <div class="messages-container">
        <div class="conversations-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-comments"></i> Messages</h2>
            </div>
            
            <?php if (empty($conversations)): ?>
                <div class="empty-conversations">
                    <i class="fas fa-inbox"></i>
                    <p>You don't have any messages yet</p>
                </div>
            <?php else: ?>
                <ul class="conversations-list">
                    <?php foreach ($conversations as $conversation): ?>
                        <li class="<?= $current_chat_user && $current_chat_user['id'] == $conversation['id'] ? 'active' : '' ?>">
                            <a href="messages.php?user=<?= $conversation['id'] ?>" class="conversation-link">
                                <img src="<?= SITE_URL ?>/uploads/<?= $conversation['avatar'] ?>" 
                                     alt="<?= htmlspecialchars($conversation['username']) ?>" class="conversation-avatar">
                                <div class="conversation-info">
                                    <span class="conversation-username"><?= htmlspecialchars($conversation['username']) ?></span>
                                    <span class="conversation-time"><?= time_elapsed_string($conversation['last_message_time']) ?></span>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="chat-container">
            <?php if ($current_chat_user): ?>
                <div class="chat-header">
                    <div class="chat-user-info">
                        <img src="<?= SITE_URL ?>/uploads/<?= $current_chat_user['avatar'] ?>" 
                             alt="<?= htmlspecialchars($current_chat_user['username']) ?>" class="chat-avatar">
                        <div>
                            <h3><?= htmlspecialchars($current_chat_user['username']) ?></h3>
                            <span class="user-status">online</span>
                        </div>
                    </div>
                </div>
                
                <div class="messages-list" id="messages-list">
                    <?php if (empty($messages)): ?>
                        <div class="empty-messages">
                            <i class="fas fa-comment-slash"></i>
                            <p>There are no messages in this dialog</p>
                            <p>Start chatting first!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?= $message['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                                <?php if ($message['sender_id'] != $user_id): ?>
                                    <img src="<?= SITE_URL ?>/uploads/<?= $message['avatar'] ?>" 
                                         alt="<?= htmlspecialchars($message['username']) ?>" class="message-avatar">
                                <?php endif; ?>
                                <div class="message-content-container">
                                    <div class="message-content">
                                        <?= nl2br(htmlspecialchars($message['content'])) ?>
                                    </div>
                                    <div class="message-meta">
                                        <span class="message-time"><?= date('H:i', strtotime($message['created_at'])) ?></span>
                                        <?php if ($message['sender_id'] == $user_id): ?>
                                            <?php if ($message['is_read']): ?>
                                                <i class="fas fa-check-double read-icon"></i>
                                            <?php else: ?>
                                                <i class="fas fa-check read-icon"></i>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="message-form-container">
                    <form action="messages.php?user=<?= $current_chat_user['id'] ?>" method="POST" class="message-form">
                        <input type="hidden" name="receiver_id" value="<?= $current_chat_user['id'] ?>">
                        <div class="input-group">
                            <textarea name="content" placeholder="Write a message..." required></textarea>
                            <button type="submit" class="btn btn-send">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="chat-placeholder">
                    <div class="placeholder-content">
                        <i class="fas fa-comments placeholder-icon"></i>
                        <h3>Select a dialog</h3>
<p> or start a new one</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Прокрутка вниз при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    const messagesList = document.getElementById('messages-list');
    if (messagesList) {
        messagesList.scrollTop = messagesList.scrollHeight;
    }
});
</script>

<?php include 'includes/footer.php'; ?>