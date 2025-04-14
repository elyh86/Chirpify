<?php
require_once "db.php";
session_start();
?>

<!DOCTYPE html>
<html lang="nl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Chirpyfy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <button class="theme-toggle" id="themeToggle" title="Toggle dark mode">
        <i class="fas fa-moon"></i>
    </button>
<div class="container">
    <div class="sidebar">
        <div class="logo">
            <i class="fab fa-twitter"></i>
        </div>
        <ul class="menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="profile.php?user_id=<?php echo $_SESSION['user_id'] ?? ''; ?>"><i class="fas fa-user"></i> Profile</a></li>
            <?php if (isset($_SESSION['user_id']) && getUserRole($conn, $_SESSION['user_id']) === 'admin'): ?>
            <li><a href="admin_panel.php"><i class="fas fa-shield-alt"></i> Admin Panel</a></li>
            <?php endif; ?>
            <li><a href="about.php" class="active"><i class="fas fa-info-circle"></i> About</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">
            <h1><i class="fab fa-twitter"></i> About Chirpyfy</h1>
        </div>
        <div class="about-content">
            <div class="team-section">
                <h3><i class="fas fa-users"></i> Welcome to Chirpyfy</h3>
                <p>Your go-to platform for sharing thoughts, ideas, and connecting with others in real-time.</p>
            </div>

            <h2><i class="fas fa-question-circle"></i> What is Chirpyfy?</h2>
            <p>Chirpyfy is a modern microblogging platform designed to make sharing and connecting easier than ever. Our platform provides a seamless experience for users to express themselves and engage with others.</p>
            
            <div class="features">
                <h3><i class="fas fa-star"></i> Key Features</h3>
                <ul>
                    <li><i class="fas fa-comment"></i> Post short updates (Chirps) to share your thoughts</li>
                    <li><i class="fas fa-heart"></i> Like and interact with posts from other users</li>
                    <li><i class="fas fa-retweet"></i> Repost interesting content to your followers</li>
                    <li><i class="fas fa-user-circle"></i> Customize your profile with photos and bio</li>
                    <li><i class="fas fa-comments"></i> Engage in conversations through comments</li>
                </ul>
            </div>

            <h2><i class="fas fa-bullseye"></i> Our Mission</h2>
            <p>At Chirpyfy, we believe in the power of connecting people through meaningful conversations. Our mission is to create a vibrant community where everyone's voice can be heard and where ideas can flourish.</p>

            <div class="features">
                <h3><i class="fas fa-shield-alt"></i> Why Choose Chirpyfy?</h3>
                <ul>
                    <li>User-friendly interface designed for seamless interaction</li>
                    <li>Strong focus on community engagement and meaningful connections</li>
                    <li>Robust privacy features to keep your data secure</li>
                    <li>Regular updates and improvements based on user feedback</li>
                </ul>
            </div>

            <h2><i class="fas fa-rocket"></i> Getting Started</h2>
            <p>Join our growing community today! It only takes a minute to create your account and start sharing your thoughts with the world.</p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
            <p style="text-align: center; margin: 32px 0;">
                <a href="register.php" class="btn-primary" style="margin-right: 16px;">
                    <i class="fas fa-user-plus"></i> Sign Up
                </a>
                <a href="login.php" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </p>
            <?php endif; ?>

            <div class="team-section">
                <h3><i class="fas fa-envelope"></i> Contact Us</h3>
                <p>Have questions or suggestions? We'd love to hear from you!<br>
                Email us at: <a href="mailto:support@chirpyfy.com" style="color: white; text-decoration: underline;">support@chirpyfy.com</a></p>
            </div>
        </div>
    </div>
</div>
    <script>
        // Dark mode functionality
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const icon = themeToggle.querySelector('i');

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });
    </script>
</body>
</html>
