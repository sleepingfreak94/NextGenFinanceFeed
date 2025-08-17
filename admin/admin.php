<?php
// admin/admin.php
$pageTitle = "Admin Panel - NextGen Finance Feed";
include __DIR__ . '/header.php'; // admin-specific header
?>

<div class="admin-card">
    <h1>Admin Panel</h1>
    <p class="welcome">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>!</p>
    <p class="instructions">Use the buttons below to manage data:</p>

    <div class="admin-actions">
        <form method="post" action="sync_companies.php">
            <button type="submit" class="btn-primary">🔄 Sync Companies</button>
        </form>

        <form method="post" id="fetch-news-form">
            <button type="submit" class="btn-secondary">📰 Fetch Nifty 50 News</button>
        </form>
    </div>

    <div id="news-progress" style="margin-top:20px; background:#f9f9f9; padding:10px; border:1px solid #ccc; display:none;">
        <strong>Progress:</strong>
        <div id="progress-output" style="max-height:250px; overflow:auto; font-size:14px;"></div>
    </div>
</div>

<script>
document.getElementById('fetch-news-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const button = this.querySelector('button');
    const progressBox = document.getElementById('news-progress');
    const output = document.getElementById('progress-output');

    button.disabled = true;
    button.textContent = 'Fetching...';
    progressBox.style.display = 'block';
    output.innerHTML = "<em>Starting fetch...</em>";

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'sync_nifty50_news.php', true);

    xhr.onprogress = function() {
        output.innerHTML = xhr.responseText;
        output.scrollTop = output.scrollHeight;
    };

    xhr.onload = function() {
        button.disabled = false;
        button.textContent = '📰 Fetch Nifty 50 News';
    };

    xhr.send();
});
</script>


<?php include __DIR__ . '/footer.php'; ?>