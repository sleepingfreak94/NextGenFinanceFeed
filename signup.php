<?php
session_start();
require_once __DIR__ . '/includes/db/db.php';

$db = new Database();
$conn = $db->conn;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (strlen($firstName) < 2 || strlen($lastName) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 4) {
        $error = "Please fill all fields correctly.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Insert new user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $firstName, $lastName, $email, $passwordHash);

            if ($stmt->execute()) {
                $_SESSION['is_logged_in'] = true;
                $_SESSION['role'] = 'user';
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $firstName;
                header("Location: index.php");
                exit;
            } else {
                $error = "Signup failed. Try again.";
            }
        }
        $stmt->close();
    }
}
?>

<?php include 'includes/header.php'; ?>
<section class="auth-wrapper">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p>Join us and start your journey today</p>

        <form method="post" action="signup.php">
            <div class="input-group">
                <input type="text" name="first_name" placeholder=" " required>
                <label>First Name</label>
            </div>
            <div class="input-group">
                <input type="text" name="last_name" placeholder=" " required>
                <label>Last Name</label>
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder=" " required>
                <label>Email Address</label>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder=" " required>
                <label>Password</label>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder=" " required>
                <label>Confirm Password</label>
            </div>

            <button type="submit" class="btn-primary">Sign Up</button>

            <p class="switch-auth">Already have an account? <a href="signin.php">Sign In</a></p>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
