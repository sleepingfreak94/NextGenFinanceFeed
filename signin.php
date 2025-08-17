<?php
session_start();
require_once __DIR__ . '/includes/db/db.php';

$pageTitle = "Sign In - NextGen Finance Feed";

$db = new Database();
$conn = $db->conn;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        $error = "Please enter a valid email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, role, password_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $firstName, $role, $hashedPassword);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                // Set all needed session variables
                $_SESSION['is_logged_in'] = true;
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = $role;
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $firstName;

                // Redirect based on role
                if ($role === 'admin') {
                    header("Location: admin/admin.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "No user found with this email.";
        }
        $stmt->close();
    }
}
?>

<?php include 'includes/header.php'; ?>

<section class="auth-wrapper">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p>Sign in to continue</p>
        <form method="post" action="signin.php">
            <div class="input-group">
                <input type="email" name="email" placeholder=" " required>
                <label>Email Address</label>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder=" " required>
                <label>Password</label>
            </div>
            <button type="submit" class="btn-primary">Sign In</button>
            <div class="extra-links">
                <a href="forgot-password.php">Forgot Password?</a>
            </div>
            <p class="switch-auth">Don't have an account? <a href="signup.php">Sign Up</a></p>
        </form>
    </div>
</section>


<?php include 'includes/footer.php'; ?>
