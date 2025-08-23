<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/includes/db/db.php';
require_once __DIR__ . '/includes/config.php';

$db = new Database();
$conn = $db->conn;

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    die("Invalid password reset link.");
}

$stmt = $conn->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    die("Invalid or expired password reset link.");
}

$stmt->bind_result($userId, $expiresAt);
$stmt->fetch();

if (strtotime($expiresAt) < time()) {
    die("Password reset link has expired.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

$update = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$update->bind_param("si", $hashedPassword, $userId);
$update->execute();
$update->close();


        $delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $delete->bind_param("s", $token);
        $delete->execute();
        $delete->close();

        $success = "Password successfully reset. <a href='signin.php'>Login here</a>";
    }
}
?>

<?php include 'includes/header.php'; ?>

<section class="auth-wrapper">
    <div class="auth-card">
        <h2>Reset Password</h2>
        <p>Enter your new password below</p>

        <form action="" method="post">
            <div class="input-group">
                <input type="password" name="password" placeholder=" " required>
                <label>New Password</label>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder=" " required>
                <label>Confirm Password</label>
            </div>
            <button type="submit" class="btn-primary">Reset Password</button>
        </form>

        <?php if (!empty($error)): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p class="success-message"><?= $success ?></p>
        <?php endif; ?>

        <p class="auth-footer">
            Remembered your password? <a href="signin.php">Sign In</a>
        </p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
