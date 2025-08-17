<?php
$pageTitle = "Contact Us";
include __DIR__ . '/includes/header.php';

$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $statusMessage = '<p style="color:red;">All fields are required.</p>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $statusMessage = '<p style="color:red;">Invalid email address.</p>';
    } else {
        $to      = 'kshitijsharma94@gmail.com';
        $subject = "New Contact Message from $name";
        $body    = "You have received a new message from your portfolio site:\n\n"
                 . "Name: $name\n"
                 . "Email: $email\n\n"
                 . "Message:\n$message\n";
        $headers = "From: $email\r\nReply-To: $email\r\n";

        if (mail($to, $subject, $body, $headers)) {
            $statusMessage = '<p style="color:green;">✅ Thank you! Your message has been sent.</p>';
            // Clear form fields after successful submission
            $_POST = [];
        } else {
            $statusMessage = '<p style="color:red;">❌ Sorry, something went wrong. Please try again later.</p>';
        }
    }
}
?>

<main>
    <section class="contact-section">
        <h2>Contact Me</h2>
        <p>Feel free to reach out via LinkedIn or email, or use the form below:</p>
        <p><strong>Email:</strong> <a href="mailto:kshitijsharma94@gmail.com">kshitijsharma94@gmail.com</a></p>
        <p><strong>LinkedIn:</strong> <a href="https://www.linkedin.com/in/kshitij-sharma-6305b2139/" target="_blank">View Profile</a></p>

        <?php if (!empty($statusMessage)): ?>
            <div class="status-message">
                <?= $statusMessage ?>
            </div>
        <?php endif; ?>

        <form action="contact.php" method="POST" class="contact-form">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="message">Message:</label>
            <textarea id="message" name="message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>

            <button type="submit">Send Message</button>
        </form>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
