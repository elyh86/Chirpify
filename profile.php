<?php
require_once "db.php";
session_start();

if (!isset($_GET['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_GET['user_id'];

// Fetch user data
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User not found, redirect to home page
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

// Fetch user's posts
try {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

// Fetch likes and reposts
try {
    $stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $likes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM reposts WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $reposts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile - Chirpyfy</title>
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
            <li><a href="profile.php?user_id=<?php echo $_SESSION['user_id']; ?>"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        <button class="btn">Tweet</button>
    </div>
    <div class="main-content">
        <div class="header">
            <h1><?php echo htmlspecialchars($user['username']); ?>'s Profile</h1>
        </div>
        <div class="profile-info">
            <img src="avatar.png" alt="Avatar" class="avatar">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <div class="posts">
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-header">
                        <img src="avatar.png" alt="Avatar" class="avatar">
                        <div>
                            <p><strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                            <p><small><?php echo $post['created_at']; ?></small></p>
                        </div>
                    </div>
                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                    <div class="post-actions">
                        <?php
                        $liked = false;
                        foreach ($likes as $like) {
                            if ($like['post_id'] == $post['post_id']) {
                                $liked = true;
                                break;
                            }
                        }
                        ?>
                        <form method="post" action="like.php">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <button type="submit" name="<?php echo $liked ? 'unlike' : 'like'; ?>">
                                <i class="fas fa-heart"></i> <?php echo $liked ? 'Unlike' : 'Like'; ?>
                            </button>
                        </form>
                        <form method="post" action="repost.php">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <button type="submit" name="repost">
                                <i class="fas fa-retweet"></i> Repost
                            </button>
                        </form>
                        <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                            <form method="post" action="delete.php">
                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                <button type="submit" name="delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>
