<?php
require_once __DIR__ . '/includes/db/db.php';
$pageTitle = "NextGen Finance Feed - Home";
include __DIR__ . '/includes/header.php';

$db = new Database();
$conn = $db->conn;

// Fetch 5 random active companies from Nifty 50
$companySql = "
    SELECT id, name, ticker 
    FROM companies 
    WHERE index_name = 'Nifty 50' AND is_active = 1 
    ORDER BY RAND() 
    LIMIT 5
";
$companyResult = $conn->query($companySql);

// Fetch latest 10 headlines for ticker
$tickerSql = "
    SELECT headline 
    FROM news_articles 
    ORDER BY published_at DESC 
    LIMIT 10
";
$tickerResult = $conn->query($tickerSql);
?>

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<!-- Custom Styles -->
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f3f4f6;
    margin: 0;
    padding: 0;
}

/* Hero Section */
.hero {
    background: linear-gradient(135deg, #2b5876, #4e4376);
    color: #fff;
    text-align: center;
    padding: 50px 20px;
    border-radius: 10px;
    margin: 20px auto;
    max-width: 1100px;
}
.hero h1 {
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 10px;
}
.hero p {
    font-size: 1.2rem;
    opacity: 0.9;
}

/* Headline Ticker */
.headline-ticker {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    padding: 12px 0;
    margin: 20px auto;
    overflow: hidden;
    position: relative;
    max-width: 1100px;
}
.ticker-container {
    white-space: nowrap;
    overflow: hidden;
}
.ticker-content {
    display: inline-block;
    padding-left: 100%;
    animation: ticker-scroll 30s linear infinite;
}
.ticker-content span {
    margin-right: 30px;
    font-weight: 500;
    color: #333;
    display: inline-block;
}
.ticker-content span::after {
    content: " | ";
    color: #888;
    margin-left: 10px;
}
@keyframes ticker-scroll {
    from { transform: translateX(0); }
    to { transform: translateX(-100%); }
}

/* Company Highlights */
.news-section {
    margin: 30px auto;
    max-width: 1100px;
}
.news-section h2 {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}
.company-news {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    padding: 20px;
    margin-bottom: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.company-news:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
}
.company-news h3 {
    color: #4e4376;
    font-weight: 700;
    margin-bottom: 10px;
}
.news-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.news-list li {
    margin-bottom: 15px;
}
.news-list a {
    font-weight: 600;
    color: #2b5876;
    text-decoration: none;
}
.news-list a:hover {
    text-decoration: underline;
}
.news-list p {
    font-size: 0.9rem;
    color: #555;
    margin: 5px 0;
}
.news-list small {
    color: #888;
    font-size: 0.8rem;
}
</style>

<!-- Hero Section -->
<section class="hero">
    <h1>Welcome to NextGen Finance Feed</h1>
    <p>Your source for real-time updates on Nifty 50 companies.</p>
</section>

<!-- Headline Ticker -->
<section class="headline-ticker">
    <div class="ticker-container">
        <div class="ticker-content">
            <?php if ($tickerResult && $tickerResult->num_rows > 0): ?>
                <?php while ($row = $tickerResult->fetch_assoc()): ?>
                    <span><?= htmlspecialchars($row['headline']) ?></span>
                <?php endwhile; ?>
            <?php else: ?>
                <span>No headlines available at the moment.</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Random Nifty 50 Companies News -->
<main class="news-section">
    <h2>Company Highlights (Nifty 50)</h2>
    <?php if ($companyResult && $companyResult->num_rows > 0): ?>
        <?php while ($company = $companyResult->fetch_assoc()): ?>
            <section class="company-news">
                <h3><?= htmlspecialchars($company['name']) ?> (<?= htmlspecialchars($company['ticker']) ?>)</h3>
                <ul class="news-list">
                    <?php
                    // Fetch latest 2 news for this company
                    $newsSql = "
                        SELECT id, headline, summary, published_at 
                        FROM news_articles 
                        WHERE company_id = {$company['id']} 
                        ORDER BY published_at DESC 
                        LIMIT 2
                    ";
                    $newsResult = $conn->query($newsSql);
                    ?>
                    <?php if ($newsResult && $newsResult->num_rows > 0): ?>
                        <?php while ($news = $newsResult->fetch_assoc()): ?>
                            <li>
                                <a href="news_detail.php?id=<?= (int)$news['id'] ?>">
                                    <?= htmlspecialchars($news['headline']) ?>
                                </a>
                                <p><?= htmlspecialchars($news['summary']) ?: 'No summary available.' ?></p>
                                <small><?= htmlspecialchars($news['published_at']) ?></small>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li>No recent news for this company.</li>
                    <?php endif; ?>
                </ul>
            </section>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No companies available.</p>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
