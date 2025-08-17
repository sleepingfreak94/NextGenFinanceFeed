<?php
header('Content-Type: text/plain');
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
@ini_set('implicit_flush', true);
ob_implicit_flush(true);

require_once __DIR__ . '/../includes/db/db.php';
require_once __DIR__ . '/../includes/db/functions.php';
$config = require __DIR__ . '/../includes/config.php';

function sendLog($msg) {
    echo $msg . "\n";
    ob_flush();
    flush();
}

try {
    $db = new Database();
    $conn = $db->conn;

    sendLog("🔍 Fetching all active companies from DB...");
    $stmt = $conn->prepare("SELECT id, name FROM companies WHERE is_active = 1");
    $stmt->execute();
    $result  = $stmt->get_result();
    $companies = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($companies)) {
        throw new Exception("No active companies found in the database.");
    }

    foreach ($companies as $company) {
        sendLog("\n📡 Fetching news for {$company['name']}...");
        $articles = fetchBraveNews($company['name'], $config['brave_api_key'], false);

        if (empty($articles)) {
            sendLog("⚠️ No articles found. Trying fallback...");
            $articles = fetchBraveNews($company['name'], $config['brave_api_key'], true);
        }

        if (empty($articles)) {
            sendLog("❌ No articles found for {$company['name']} after both attempts.");
            continue;
        }

        $insertedCount = 0;

        foreach ($articles as $article) {
            if ($insertedCount >= 5) break;

            if (
                empty($article['url']) ||
                empty($article['headline']) ||
                empty($article['source']) ||
                empty($article['published_at'])
            ) {
                sendLog("⏩ Skipping article due to missing fields.");
                continue;
            }

            $normalizedUrl = normalizeUrl($article['url']);
            $stmt = $conn->prepare("SELECT id FROM news_articles WHERE url = ? LIMIT 1");
            $stmt->bind_param("s", $normalizedUrl);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                sendLog("📌 Already in DB: {$normalizedUrl}");
                $stmt->close();
                continue;
            }
            $stmt->close();

            $newsText = strip_tags($article['summary'] ?? $article['headline']);
            $aiSummary = summarizeWithHuggingFace($newsText, $config['huggingface_token']);
            if ($aiSummary === null || stripos($aiSummary, 'disclaimer') !== false || strlen($aiSummary) < 40) {
                $aiSummary = strip_tags($article['summary'] ?? '');
            }

            $stmt = $conn->prepare("
                INSERT INTO news_articles (company_id, headline, url, summary, source, published_at) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssss", $company['id'], $article['headline'], $normalizedUrl, $aiSummary, $article['source'], $article['published_at']);
            if ($stmt->execute()) {
                $insertedCount++;
            }
            $stmt->close();
        }

        sendLog("🎯 Completed for {$company['name']}. Inserted: {$insertedCount}");
        sleep(1);
    }

    sendLog("\n✅ All Nifty 50 news fetched successfully!");
} catch (Exception $e) {
    sendLog("❌ Error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fetch News - NextGen Finance Feed</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<header>
    <h1>NextGen Finance Feed - Admin Action</h1>
</header>
<main>
    <div class="message <?= $statusClass ?>">
        <?= $status ?>
    </div>
    <a href="admin.php" class="back-link">← Back to Admin Panel</a>
</main>
<footer>
    <p>&copy; <?= date('Y') ?> NextGen Finance Feed</p>
</footer>
</body>
</html>
