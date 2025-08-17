<?php
require_once __DIR__ . '/includes/db/db.php';
$pageTitle = "Company News Summaries";
include __DIR__ . '/includes/header.php';

$db = new Database();
$conn = $db->conn;

$ticker = $_GET['ticker'] ?? '';
$company = null;
$news = [];

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

if ($ticker) {
    // Fetch company info
    $stmt = $conn->prepare("SELECT id, name FROM companies WHERE ticker = ? LIMIT 1");
    $stmt->bind_param("s", $ticker);
    $stmt->execute();
    $result = $stmt->get_result();
    $company = $result->fetch_assoc();
    $stmt->close();

    if ($company) {
        // Count total news
        $stmtCount = $conn->prepare("SELECT COUNT(*) AS total FROM news_articles WHERE company_id = ?");
        $stmtCount->bind_param("i", $company['id']);
        $stmtCount->execute();
        $totalResult = $stmtCount->get_result();
        $totalRows = $totalResult->fetch_assoc()['total'];
        $totalPages = ceil($totalRows / $limit);
        $stmtCount->close();

        // Fetch paginated news
        $stmt2 = $conn->prepare("
            SELECT id, summary, headline, url, source, published_at
            FROM news_articles
            WHERE company_id = ?
            ORDER BY published_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt2->bind_param("iii", $company['id'], $limit, $offset);
        $stmt2->execute();
        $news = $stmt2->get_result();
        $stmt2->close();
    }
}
?>

<section class="page-wrapper">
    <?php if (!empty($company)): ?>
        <div class="page-header">
            <h1>📰 <?= htmlspecialchars($company['name']) ?> (<?= htmlspecialchars($ticker) ?>)</h1>
            <p class="page-subtitle">Latest curated news summaries for <?= htmlspecialchars($company['name']) ?>.</p>
        </div>

        <div class="news-list-container">
            <?php if ($news->num_rows > 0): ?>
                <ul class="news-list">
                    <?php while ($n = $news->fetch_assoc()): ?>
                        <li class="news-item">
                            <a href="news_detail.php?id=<?= (int)$n['id'] ?>" target="_blank" class="news-link" title="<?= htmlspecialchars($n['headline']) ?>">
                                <h3 class="news-headline"><?= htmlspecialchars($n['headline']) ?></h3>
                                <p class="news-summary"><?= htmlspecialchars($n['summary']) ?: 'No summary available.' ?></p>
                                <div class="news-meta">
                                    <span><?= htmlspecialchars($n['source']) ?></span>
                                    <span><?= htmlspecialchars(date('d M Y, H:i', strtotime($n['published_at']))) ?></span>
                                </div>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?ticker=<?= urlencode($ticker) ?>&page=<?= $page - 1 ?>" class="page-link">Prev</a>
                        <?php endif; ?>

                        <?php
                        $range = 2;
                        for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++): ?>
                            <a href="?ticker=<?= urlencode($ticker) ?>&page=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?ticker=<?= urlencode($ticker) ?>&page=<?= $page + 1 ?>" class="page-link">Next</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="no-news">No news found for this company.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p class="no-news">Company not found.</p>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
