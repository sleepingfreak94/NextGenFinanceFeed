<?php
session_start();

// Allow both admin and user to access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: signin.php");
    exit();
}

$pageTitle = "Nifty 50 Companies - NextGen Finance Feed";
include __DIR__ . '/includes/header.php';

require_once __DIR__ . '/includes/db/db.php';
$db = new Database();
$conn = $db->conn;

$sql = "SELECT name, ticker, is_active FROM companies WHERE index_name = 'Nifty 50' ORDER BY is_active DESC, name";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<section class="page-wrapper">
    <div class="page-header">
        <h1>Nifty 50 Companies</h1>
        <p class="page-subtitle">Explore the companies listed under Nifty 50 Index</p>
    </div>

    <div class="company-list-container">
        <ul class="company-list">
            <?php while ($company = $result->fetch_assoc()): ?>
                <li class="company-item <?php if(!$company['is_active']) echo 'inactive'; ?>">
                    <a href="company.php?ticker=<?= urlencode($company['ticker']) ?>" class="company-link">
                        <span class="company-name"><?= htmlspecialchars($company['name']) ?></span>
                        <span class="company-ticker">(<?= htmlspecialchars($company['ticker']) ?>)</span>
                    </a>
                    <?php if(!$company['is_active']): ?>
                        <span class="status-label">Inactive</span>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
