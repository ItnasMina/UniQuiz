<?php
/**
 * UniQuiz - Main Entry Point
 * 
 * This file serves as the landing page for guests and the main feed for authenticated users.
 * Eventually, it will bootstrap the application by requiring the Composer autoloader
 * and database configurations.
 */

// TODO: Require autoloader and initialize session/database connection here
// require_once __DIR__ . '/../vendor/autoload.php';
// session_start();

$isLoggedIn = false; // Mock variable for now. Will be replaced by actual auth logic.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UniQuiz - The educational social network to create, share, and solve quizzes.">
    <title>UniQuiz | Learn Together</title>
    
    <!-- Link to our future main stylesheet -->
    <link rel="stylesheet" href="/assets/css/main.css">
    
    <!-- Preload critical assets for performance (fonts, etc. will go here) -->
</head>
<body>
    
    <!-- MAIN NAVIGATION -->
    <header class="site-header">
        <nav class="navbar" aria-label="Main Navigation">
            <div class="navbar-brand">
                <a href="/">
                    <h1>UniQuiz 🚀</h1>
                </a>
            </div>
            
            <ul class="navbar-nav">
                <?php if ($isLoggedIn): ?>
                    <li><a href="/quiz_create.php">Create Quiz</a></li>
                    <li><a href="/profile.php">My Profile</a></li>
                    <li><a href="/UniQuiz/public/user_logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/UniQuiz/public/user_login.php">Login</a></li>
                    <li><a href="/UniQuiz/public/user_register.php" class="btn-primary">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- MAIN CONTENT AREA -->
    <main class="site-main">
        <?php if ($isLoggedIn): ?>
            <!-- Authenticated User Feed -->
            <section class="feed-section">
                <h2>Recent Quizzes</h2>
                <!-- Future dynamic quiz list will be injected here via PHP/PDO -->
                <p>Loading your personalized feed...</p>
            </section>
        <?php else: ?>
            <!-- Guest Landing Page -->
            <section class="hero-section">
                <div class="hero-content">
                    <h2>Master Any Subject, Together.</h2>
                    <p>Join thousands of students and teachers. Create interactive quizzes, challenge your friends, and track your progress.</p>
                    <a href="/UniQuiz/public/user_register.php" class="btn-cta">Start Learning for Free</a>
                </div>
            </section>
            
            <section class="features-section">
                <h2>Why UniQuiz?</h2>
                <div class="features-grid">
                    <article class="feature-card">
                        <h3>Create Instantly</h3>
                        <p>Design multiple-choice questions with our intuitive builder.</p>
                    </article>
                    <article class="feature-card">
                        <h3>Global Leaderboards</h3>
                        <p>Compete with students worldwide and climb the ranks.</p>
                    </article>
                    <article class="feature-card">
                        <h3>Detailed Analytics</h3>
                        <p>Identify your weak spots with our automated tracking system.</p>
                    </article>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <!-- FOOTER -->
    <footer class="site-footer">
        <p>&copy; <?php echo date('Y'); ?> UniQuiz. All rights reserved.</p>
    </footer>

    <!-- Scripts at the bottom to avoid render blocking -->
    <script src="/assets/js/main.js" defer></script>
</body>
</html>