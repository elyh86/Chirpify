<?php
require_once "db.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: login.php");
        exit();
    }

    if (!isset($user['profile_picture']) || empty($user['profile_picture'])) {
        $user['profile_picture'] = 'default_avatar.png';
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_post'])) {
    $content = trim($_POST['content']);
    $image = $_FILES['image'];

    $image_path = null;
    if ($image['size'] > 0) {
        if ($image['size'] <= 2 * 1024 * 1024) { // Limit image size to 2MB
            $target_dir = "uploads/posts/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image_path = $target_dir . basename($image["name"]);
            move_uploaded_file($image["tmp_name"], $image_path);
        } else {
            $error_message = "Image size must be less than 2MB.";
        }
    }

    if (!empty($content) || $image_path) {
        try {
            $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (:user_id, :content, :image)");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image', $image_path);
            $stmt->execute();
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
}

$posts = [];
try {
    $stmt = $conn->prepare("SELECT posts.*, users.username, users.profile_picture, 
                            (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.post_id) AS like_count, 
                            (SELECT COUNT(*) FROM reposts WHERE reposts.post_id = posts.post_id) AS repost_count 
                            FROM posts JOIN users ON posts.user_id = users.user_id ORDER BY posts.created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

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
    <title>Chirpyfy</title>
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
            <?php if (getUserRole($conn, $_SESSION['user_id']) === 'admin'): ?>
            <li><a href="admin_panel.php"><i class="fas fa-shield-alt"></i> Admin Panel</a></li>
            <?php endif; ?>
            <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
        <button class="btn">Tweet</button>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Home</h1>
        </div>
        <div class="new-post">
            <form method="post" action="" enctype="multipart/form-data">
                <textarea name="content" placeholder="What's happening?" required></textarea>
                <label for="image" class="custom-file-upload">Add a Picture</label>
                <input type="file" id="image" name="image" accept="image/*">
                <button type="submit" name="new_post" style="margin-left: 10px;">Chirp</button>
            </form>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
        </div>
        <div class="posts">
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-header">
                        <img src="<?php echo htmlspecialchars($post['profile_picture']); ?>" alt="Avatar" class="avatar">
                        <div>
                            <p><strong><a href="profile.php?user_id=<?php echo $post['user_id']; ?>"><?php echo htmlspecialchars($post['username']); ?></a></strong></p>
                            <p><small><?php echo $post['created_at']; ?></small></p>
                        </div>
                    </div>
                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                    <?php if ($post['image']): ?>
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="post-image">
                    <?php endif; ?>
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
                        <form method="post" action="like.php" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <button type="submit" name="<?php echo $liked ? 'unlike' : 'like'; ?>" class="action-btn">
                                ❤️ <?php echo $liked ? 'Unlike' : 'Like'; ?> (<?php echo $post['like_count']; ?>)
                            </button>
                        </form>
                        <?php
                        $reposted = false;
                        foreach ($reposts as $repost) {
                            if ($repost['post_id'] == $post['post_id']) {
                                $reposted = true;
                                break;
                            }
                        }
                        ?>
                        <form method="post" action="repost.php" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <button type="submit" name="<?php echo $reposted ? 'unrepost' : 'repost'; ?>" class="action-btn">
                                🔁 <?php echo $reposted ? 'Unrepost' : 'Repost'; ?> (<?php echo $post['repost_count']; ?>)
                            </button>
                        </form>
                        <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                            <form method="post" action="delete.php" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                <button type="submit" name="delete" class="action-btn">🗑️ Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="comments">
                        <h4>Comments</h4>
                        <?php
                        $commentStmt = $conn->prepare("SELECT comments.*, users.username, users.profile_picture 
                                                       FROM comments 
                                                       JOIN users ON comments.user_id = users.user_id 
                                                       WHERE comments.post_id = :post_id ORDER BY comments.created_at ASC");
                        $commentStmt->bindParam(':post_id', $post['post_id'], PDO::PARAM_INT);
                        $commentStmt->execute();
                        $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <img src="<?php echo htmlspecialchars($comment['profile_picture']); ?>" alt="Avatar" class="avatar">
                                <div>
                                    <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong></p>
                                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                    <?php if ($comment['image']): ?>
                                        <img src="<?php echo htmlspecialchars($comment['image']); ?>" alt="Comment Image" class="comment-image">
                                    <?php endif; ?>
                                    <p><small><?php echo $comment['created_at']; ?></small></p>
                                </div>
                                <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>
                                    <form method="post" action="delete_comment.php" style="display: inline;">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                        <button type="submit" name="delete" class="action-btn">🗑️ Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <form method="post" action="comment.php" enctype="multipart/form-data">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                            <textarea name="content" placeholder="Write a comment..." required></textarea>
                            <input type="file" name="image" accept="image/*">
                            <button type="submit" class="action-btn">Comment</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>
