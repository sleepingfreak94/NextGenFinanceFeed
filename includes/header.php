<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'NextGen Finance Feed' ?></title>
    <link rel="stylesheet" href="assets/css/styles.css" />
</head>
<body>
<header>
    <h1>NextGen Finance Feed</h1>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="nifty50.php">Nifty 50</a></li>
            <li><a href="sp500.php">S&amp;P 500</a></li>
            <li><a href="allnews.php">All News</a></li>
            <li><a href="about.php">About</a></li>

            <?php if (isset($_SESSION['is_logged_in'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin/admin.php">Admin Panel</a></li>
                <?php endif; ?>
                <li style="float:right;"><a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['name']) ?>)</a></li>
            <?php else: ?>
                <li style="float:right;"><a href="signup.php">Sign Up</a></li>
                <li style="float:right;"><a href="signin.php">Sign In</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>
