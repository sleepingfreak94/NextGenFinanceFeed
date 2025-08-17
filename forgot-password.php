<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/includes/db/db.php';
require_once __DIR__ . '/includes/config.php'; // Load .env config

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/phpmailer/Exception.php';
require 'includes/phpmailer/PHPMailer.php';
require 'includes/phpmailer/SMTP.php';

$db = new Database();
$conn = $db->conn;

$config = require __DIR__ . '/includes/config.php'; // Config array from .env

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if the user exists
        $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows !== 1) {
            $error = "No user found with this email.";
        } else {
            $stmt->bind_result($id, $firstName);
            $stmt->fetch();

            try {
                // Initialize PHPMailer
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $config['smtp_host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $config['smtp_username'];
                $mail->Password   = $config['smtp_password'];
                $mail->SMTPSecure = 'tls';
                $mail->Port       = $config['smtp_port'];

                $mail->setFrom($config['smtp_from_email'], $config['smtp_from_name']);
                $mail->addAddress($email, $firstName);

                // Generate secure token and expiration
                $token = bin2hex(random_bytes(16));
                $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour

                // Save token to DB
                $stmtToken = $conn->prepare(
                    "REPLACE INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)"
                );
                $stmtToken->bind_param("iss", $id, $token, $expiry);
                $stmtToken->execute();
                $stmtToken->close();

                // Reset URL (update domain for production)
                $reset_link = "http://localhost/NextGenFinanceFeed/reset-password.php?token=" . urlencode($token);

                // Email body
                $mailBody = "
                    <p>Hello " . htmlspecialchars($firstName) . ",</p>
                    <p>We received a request to reset your password. Click the link below to choose a new password:</p>
                    <p><a href=\"$reset_link\">Reset your password</a></p>
                    <p>If you did not request a password reset, please ignore this email.</p>
                    <p>Thank you,<br>NextGen News Portal Team</p>
                ";

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Instructions';
                $mail->Body    = $mailBody;

                $mail->send();

                $success = "Password reset instructions have been sent to <strong>" . htmlspecialchars($email) . "</strong>.";
            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
        $stmt->close();
    }
}
?>

<?php include 'includes/header.php'; ?>

<section class="auth-wrapper">
    <div class="auth-card">
        <h2>Forgot Password</h2>
        <p>Enter your email to reset your password</p>
        <form action="forgot-password.php" method="post">
            <div class="input-group">
                <input type="email" name="email" placeholder=" " required>
                <label>Email Address</label>
            </div>
            <button type="submit" class="btn-primary">Send Reset Link</button>
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
