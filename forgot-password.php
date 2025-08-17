<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/includes/db/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/phpmailer/Exception.php';
require 'includes/phpmailer/PHPMailer.php';
require 'includes/phpmailer/SMTP.php';

$db = new Database();
$conn = $db->conn;

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

            // Now that we have $email and $firstName, send the reset email:
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.mailersend.net';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'MS_mGS8ah@test-r83ql3px08pgzw1j.mlsender.net';
                $mail->Password   = 'mssp.5bXlvBz.neqvygmxr3zl0p7w.DplEV6f';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $mail->setFrom('MS_mGS8ah@test-r83ql3px08pgzw1j.mlsender.net', 'NextGen News Portal');
                $mail->addAddress('kshitijsharma94@gmail.com', $firstName);

                // 1. Generate a secure random token (e.g., 32 characters)
$token = bin2hex(random_bytes(16));

// 2. Save this token to your database along with an expiration time (e.g., 1 hour)
// You'll need to implement this part: update your password_resets or users table
$expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now

// Example SQL to insert/update token (adjust table/column names accordingly)
$stmtToken = $conn->prepare("REPLACE INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
$stmtToken->bind_param("iss", $id, $token, $expiry);
$stmtToken->execute();
$stmtToken->close();

// 3. Create the reset URL - update domain & path accordingly
$reset_link = "http://localhost/NextGenFinanceFeed/reset-password.php?token=" . urlencode($token);


// 4. Build the email body with the link embedded in anchor tag
$mailBody = "
    <p>Hello " . htmlspecialchars($firstName) . ",</p>
    <p>We received a request to reset your password. Click the link below to choose a new password:</p>
    <p><a href=\"$reset_link\">Reset your password</a></p>
    <p>If you did not request a password reset, please ignore this email.</p>
    <p>Thank you,<br>NextGen News Portal Team</p>
";

// 5. Then assign it to PHPMailer body
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