<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';  

$query = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$tag = isset($_GET['tag']) ? sanitize($_GET['tag']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = [];
$params = [];

if (!empty($query)) {
    $where[] = "(title LIKE ? OR content LIKE ? OR tags LIKE ?)";
    $search_term = "%$query%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if (!empty($tag)) {
    $where[] = "tags LIKE ?";
    $params[] = "%$tag%";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Получение постов
$stmt = $pdo->prepare("
    SELECT p.*, u.username 
    FROM posts p 
    LEFT JOIN users u ON p.user_id = u.id 
    $where_clause 
    ORDER BY p.created_at DESC 
    LIMIT $offset, $per_page
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Получение общего количества постов для пагинации
$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts $where_clause");
$stmt->execute($params);
$total_posts = $stmt->fetchColumn();
$total_pages = ceil($total_posts / $per_page);

$page_title = 'Search';
if (!empty($query)) $page_title .= ': ' . htmlspecialchars($query);
if (!empty($tag)) $page_title .= ' by tag: ' . htmlspecialchars($tag);

http_response_code(404); // Ensure 404 status is sent
$page_title = 'Page Not Found';
include 'includes/header.php';
?>

<style>
    .search-results {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 25px;
        box-shadow: var(--shadow);
        margin: 20px 0;
    }

    .search-results h1 {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .search-results h1 i {
        margin-right: 10px;
        color: var(--accent-green);
    }

    /* Search Form */
    .search-form {
        display: flex;
        margin-bottom: 20px;
    }

    .search-form input {
        flex: 1;
        padding: 12px 15px;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid #333;
        border-radius: var(--border-radius) 0 0 var(--border-radius);
        color: var(--text-primary);
        font-size: 1rem;
        transition: var(--transition);
    }

    .search-form input:focus {
        outline: none;
        border-color: var(--accent-green);
        box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
    }

    .search-form button {
        padding: 0 20px;
        background: var(--accent-gradient);
        color: white;
        border: none;
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        cursor: pointer;
        transition: var(--transition);
    }

    .search-form button:hover {
        opacity: 0.9;
    }

    /* Search Tag */
    .search-tag {
        margin-bottom: 20px;
        padding: 10px 15px;
        background-color: rgba(76, 175, 80, 0.1);
        border-radius: var(--border-radius);
    }

    .search-tag .tag {
        color: var(--accent-green);
        font-weight: 600;
    }

    /* Posts List */
    .posts-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .posts-list .post {
        display: flex;
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: var(--border-radius);
        overflow: hidden;
        transition: var(--transition);
    }

    .posts-list .post:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }

    .posts-list .post-votes {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 15px;
        background-color: rgba(0, 0, 0, 0.3);
    }

    .posts-list .vote-count {
        font-weight: bold;
        margin: 5px 0;
        font-size: 1.1rem;
    }

    .posts-list .post-content {
        flex: 1;
        padding: 15px;
    }

    .posts-list .post-content h2 {
        margin-bottom: 10px;
    }

    .posts-list .post-content h2 a:hover {
        color: var(--accent-green);
    }

    .post-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin-bottom: 10px;
    }

    .post-meta a:hover {
        color: var(--accent-green);
    }

    .post-excerpt {
        margin-bottom: 10px;
        line-height: 1.5;
    }

    .post-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 30px;
    }

    .page-link {
        padding: 8px 12px;
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: var(--border-radius);
        transition: var(--transition);
    }

    .page-link:hover {
        background-color: rgba(76, 175, 80, 0.2);
    }

    .page-link.active {
        background: var(--accent-gradient);
        color: white;
    }

    @media (max-width: 768px) {
        .search-results {
            padding: 15px;
        }
        
        .posts-list .post {
            flex-direction: column;
        }
        
        .posts-list .post-votes {
            flex-direction: row;
            justify-content: center;
            padding: 10px;
        }
        
        .pagination {
            flex-wrap: wrap;
        }
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="error-template">
                <h1>Oops!</h1>
                <h2>404 Not Found</h2>
                <div class="error-details">
                    Sorry, an error has occurred, Requested page not found!
                </div>
                <div class="error-actions">
                    <a href="<?= SITE_URL ?>" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-home"></span>
                        Take Me Home </a><a href="contact.php" class="btn btn-default btn-lg"><span class="glyphicon glyphicon-envelope"></span> Contact Support </a>
                </div>
                <form action="search.php" method="GET" class="search-form">
        <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Site search...">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>
            </div>
        </div>
    </div>
</div>

<style>
    .error-template {padding: 40px 15px;text-align: center;}
    .error-actions {margin-top:15px;margin-bottom:15px;}
    .error-actions .btn { margin-right:10px; }
</style>

<?php include 'includes/footer.php'; ?>