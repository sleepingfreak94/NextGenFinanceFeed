<?php
// about.php
require_once __DIR__ . '/includes/header.php';
?>

<main class="about-page">
    <div class="container">
        <h1>About Us</h1>
        <p>Welcome to <strong>NextGen Finance Feed</strong> – your go-to source for real-time financial updates and AI-driven news summaries.</p>

        <section>
            <h2>Our Mission</h2>
            <p>
                Our goal is to make financial information accessible, easy to digest, and up-to-date for everyone.
                With the fast-paced nature of markets, we aim to provide AI-powered summaries so you can stay informed without the noise.
            </p>
        </section>

        <section>
            <h2>About This Project</h2>
            <p>
                This is a <strong>portfolio project</strong> developed to demonstrate expertise in modern web development and integration with AI technologies.
                The project showcases a blend of <strong>backend development, API integration, UI/UX design, and automated testing</strong>.
            </p>
        </section>

        <section>
            <h2>Technologies Used</h2>
            <ul>
                <li><strong>Frontend:</strong> HTML5, CSS3 (Responsive Design)</li>
                <li><strong>Backend:</strong> PHP (Structured with includes for modularity)</li>
                <li><strong>Database:</strong> MySQL for storing companies and news data</li>
                <li><strong>AI Integration:</strong> Hugging Face API for text summarization</li>
                <li><strong>Data Source:</strong> Brave Search API for fetching financial news</li>
                <li><strong>Security:</strong> Prepared Statements for SQL queries to prevent SQL Injection</li>
                <li><strong>Automation Testing:</strong> PHPUnit for unit testing and Selenium for end-to-end testing</li>
            </ul>
        </section>

        <section>
            <h2>Automation Testing</h2>
            <p>
                This project also includes <strong>automation testing</strong> to ensure reliability and maintainability.
                We implemented:
            </p>
            <ul>
                <li><strong>Unit Tests:</strong> Using PHPUnit for backend logic and database operations</li>
                <li><strong>Integration Tests:</strong> To verify API integrations (Brave API & Hugging Face)</li>
                <li><strong>UI Tests:</strong> Selenium WebDriver for testing login, signup, and navigation flows</li>
            </ul>
            <p>
                These tests help maintain code quality and prevent regression issues during updates.
            </p>
        </section>

        <section>
            <h2>Why This Project?</h2>
            <p>
                The objective was to create a <strong>realistic and scalable finance platform</strong> where users can sign up, log in, and access curated financial content.
                Admin functionality includes adding companies and fetching news dynamically from APIs, all while maintaining <strong>high testing standards</strong>.
            </p>
        </section>

        <div class="back-home">
            <a href="index.php" class="btn-primary">← Back to Home</a>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
