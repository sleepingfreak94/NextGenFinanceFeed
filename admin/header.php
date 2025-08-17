<?php
// admin/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: Force admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../signin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Panel - NextGen Finance Feed' ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css" />
</head>
<body>
<header class="admin-header">
    <h1>🛠 Admin Panel - NextGen Finance Feed</h1>
    <nav class="admin-nav">
        <ul>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="../nifty50.php">Nifty 50</a></li>
            <li><a href="../sp500.php">S&amp;P 500</a></li>
            <li><a href="../allnews.php">All News</a></li>
            <li><a href="../about.php">About</a></li>
            <li style="float:right;"><a href="../logout.php">Logout (<?= htmlspecialchars($_SESSION['name']) ?>)</a></li>
        </ul>
    </nav>
</header>
<main class="admin-main">
