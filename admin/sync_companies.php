<?php
ob_start();
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db/db.php';        // database connection
require_once __DIR__ . '/../includes/db/functions.php'; // helper functions

$status = '';
$statusClass = '';

try {
    $db = new Database();
    $conn = $db->conn;

    $status .= "<p>🔄 Starting companies sync...</p>";

    // Fetch company data
    $niftyCompanies = fetchNifty50Companies();
    $sp500Companies = fetchSP500Companies();

    if (!is_array($niftyCompanies) || !is_array($sp500Companies)) {
        throw new Exception("Failed to fetch company data. Please check API or helper functions.");
    }

    // Sync to DB
    syncCompanies($niftyCompanies, $conn);
    syncCompanies($sp500Companies, $conn);

    $status .= "<p>✅ Companies sync finished successfully!</p>";
    $statusClass = "success";

} catch (Exception $e) {
    $status .= "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    $statusClass = "error";
}
$content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Companies Sync - NextGen Finance Feed</title>
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
