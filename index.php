<?php
require_once "db.php";

// Suppress the connection success message
ob_start();
require_once "db.php";
ob_end_clean();

// Fetch all posts
$stmt = $conn->prepare("SELECT * FROM posts ORDER BY created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chirpyfy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sidebar">
    <h2>Chirpyfy</h2>
    <a href="index.php">Home</a>
    <a href="profile.php">Profiel</a>
    <a href="settings.php">Instellingen</a>
    <a href="logout.php">Uitloggen</a>
</div>
<div class="container" id="homeContainer">
    <div class="header">
        <h1>Welkom op Chirpyfy</h1>
        <div class="nav">
            <a href="index.php">Home</a>
            <a href="profile.php">Profiel</a>
            <a href="settings.php">Instellingen</a>
            <a href="logout.php">Uitloggen</a>
        </div>
    </div>
    <div class="post-box">
        <form action="post.php" method="post" enctype="multipart/form-data">
            <textarea name="content" placeholder="Wat is er aan de hand?" required></textarea>
            <input type="file" name="image">
            <button type="submit">Plaatsen</button>
        </form>
    </div>
    <div class="posts">
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <p><?php echo htmlspecialchars($post['content']); ?></p>
                <?php if ($post['image']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image">
                <?php endif; ?>
                <div class="post-actions">
                    <div class="like-count">
                        <span><?php echo $post['likes']; ?></span>
                        <a href="like.php?post_id=<?php echo $post['id']; ?>">Like</a>
                    </div>
                    <a href="repost.php?post_id=<?php echo $post['id']; ?>">Repost</a>
                    <a href="delete.php?post_id=<?php echo $post['id']; ?>">Verwijderen</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
