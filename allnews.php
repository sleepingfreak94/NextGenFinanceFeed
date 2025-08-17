<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: signin.php");
    exit();
}

$pageTitle = "All News - NextGen Finance Feed";
require_once __DIR__ . '/includes/db/db.php';
include __DIR__ . '/includes/header.php';

$db = new Database();
$conn = $db->conn;

// Pagination settings
$limit = 10; // News per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Get total number of news
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM news_articles");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch news with pagination
$sql = "
    SELECT n.id, n.headline, n.url, n.summary, n.source, n.published_at, c.name AS company_name
    FROM news_articles n
    LEFT JOIN companies c ON n.company_id = c.id
    ORDER BY n.published_at DESC
    LIMIT $limit OFFSET $offset
";
$result = $conn->query($sql);
?>

<section class="page-wrapper">
    <div class="page-header">
        <h1>📰 Latest News</h1>
        <p class="page-subtitle">Stay informed with the most recent updates across top companies.</p>
    </div>

    <div class="news-list-container">
        <?php if ($result && $result->num_rows > 0): ?>
            <ul class="news-list">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="news-item">
                        <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" class="news-link">
                            <h3 class="news-headline"><?= htmlspecialchars($row['headline']) ?></h3>
                            <p class="news-summary">
                                <?= htmlspecialchars($row['summary']) ?: 'No summary available.' ?>
                            </p>
                            <div class="news-meta">
                                <span><?= htmlspecialchars($row['company_name'] ?? 'General') ?></span>
                                <span><?= htmlspecialchars($row['source']) ?></span>
                                <span><?= htmlspecialchars(date('d M Y, H:i', strtotime($row['published_at']))) ?></span>
                            </div>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>

            <!-- Pagination -->
            <nav class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="page-link">Prev</a>
                <?php endif; ?>

                <?php
                $range = 2; // how many pages to show around current page
                for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++): ?>
                    <a href="?page=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="page-link">Next</a>
                <?php endif; ?>
            </nav>

        <?php else: ?>
            <p class="no-news">No news available at the moment.</p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
