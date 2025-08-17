<?php
// Allow both admin and user to access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: signin.php");
    exit();
}

require_once __DIR__ . '/includes/db/db.php';
$pageTitle = "News Details";
include __DIR__ . '/includes/header.php';

$db = new Database();
$conn = $db->conn;

$newsId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($newsId <= 0) {
    echo "<main><p>Invalid news ID.</p></main>";
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch news details by ID
$stmt = $conn->prepare("
    SELECT n.headline, n.url, n.summary, n.source, n.published_at, c.name AS company_name, c.ticker
    FROM news_articles n
    JOIN companies c ON n.company_id = c.id
    WHERE n.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $newsId);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();
$stmt->close();

if (!$news) {
    echo "<main><p>News article not found.</p></main>";
    include __DIR__ . '/includes/footer.php';
    exit;
}
?>

<main>
    <section class="news-detail">
        <h2><?= htmlspecialchars($news['headline']) ?></h2>
        <div class="intro">
            <p>
                <strong>Company:</strong> <?= htmlspecialchars($news['company_name']) ?> (<?= htmlspecialchars($news['ticker']) ?>)<br>
                <strong>Source:</strong> <?= htmlspecialchars($news['source']) ?><br>
                <strong>Published At:</strong> <?= htmlspecialchars($news['published_at']) ?>
            </p>
        </div>

        <div class="news-content">
            <p><?= nl2br(htmlspecialchars($news['summary'])) ?></p>
        </div>

        <p>
            <a href="<?= htmlspecialchars($news['url']) ?>" class="button" target="_blank" rel="noopener noreferrer">
                🔗 Read Full Article
            </a>
        </p>

        <p>
            <a href="company.php?ticker=<?= urlencode($news['ticker']) ?>">← Back to <?= htmlspecialchars($news['company_name']) ?> summaries</a>
        </p>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
