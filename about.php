<?php
session_start();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Chirpyfy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
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
            <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>About Chirpyfy</h1>
        </div>
        <div class="about-content">
            <p>Welcome to <strong>Chirpyfy</strong>, your go-to platform for sharing your thoughts, ideas, and updates with the world. Chirpyfy is a microblogging platform inspired by the simplicity and connectivity of social media platforms like Twitter.</p>
            
            <h2>What is Chirpyfy?</h2>
            <p>Chirpyfy allows users to:</p>
            <ul>
                <li>Post short updates (we call them "Chirps").</li>
                <li>Like and repost Chirps from other users.</li>
                <li>Comment on Chirps to engage in conversations.</li>
                <li>Customize your profile with a biography and profile picture.</li>
            </ul>

            <h2>Why Choose Chirpyfy?</h2>
            <p>Chirpyfy is designed to be simple, user-friendly, and focused on fostering meaningful interactions. Whether you're sharing your daily thoughts or engaging with others, Chirpyfy is the perfect platform to connect with like-minded individuals.</p>

            <div class="features">
                <h3>Key Features:</h3>
                <ul>
                    <li><strong>Real-Time Updates:</strong> Share your thoughts instantly with your followers.</li>
                    <li><strong>Engagement:</strong> Like, repost, and comment on Chirps to join the conversation.</li>
                    <li><strong>Customizable Profiles:</strong> Add a biography and profile picture to express yourself.</li>
                    <li><strong>Dark Mode:</strong> Switch to dark mode for a comfortable browsing experience.</li>
                </ul>
            </div>

            <h2>Our Mission</h2>
            <p>At Chirpyfy, our mission is to create a space where everyone can share their voice and connect with others. We believe in the power of communication and aim to make Chirpyfy a platform that fosters positivity and creativity.</p>

            <h2>Get Started</h2>
            <p>If you haven't already, <a href="register.php">create an account</a> and start Chirping today! Already have an account? <a href="login.php">Log in</a> to join the conversation.</p>

            <div class="team-section">
                <h3>Meet the Team</h3>
                <p>Chirpyfy was built by a passionate team of developers and designers who believe in the power of connection. We are constantly working to improve the platform and bring you the best experience possible.</p>
            </div>

            <h2>Contact Us</h2>
            <p>Have questions or feedback? We'd love to hear from you! Reach out to us at <a href="mailto:support@chirpyfy.com">support@chirpyfy.com</a>.</p>
        </div>
    </div>
</div>
</body>
</html>
