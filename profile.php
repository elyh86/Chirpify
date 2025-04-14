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

    // Initialize biography and profile_picture if not set
    if (!isset($user['biography'])) {
        $user['biography'] = '';
    }
    if (!isset($user['profile_picture']) || empty($user['profile_picture'])) {
        $user['profile_picture'] = 'uploads/default_avatar.png'; // Default avatar image
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

// Fetch user's posts and reposts
try {
    $stmt = $conn->prepare("
        SELECT 
            posts.*,
            users.username,
            users.profile_picture,
            (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.post_id) AS like_count,
            (SELECT COUNT(*) FROM reposts WHERE reposts.post_id = posts.post_id) AS repost_count,
            GROUP_CONCAT(DISTINCT l_users.username) as likers
        FROM posts 
        JOIN users ON posts.user_id = users.user_id
        LEFT JOIN likes ON posts.post_id = likes.post_id
        LEFT JOIN users l_users ON likes.user_id = l_users.user_id
        WHERE posts.user_id = :user_id 
            OR posts.post_id IN (SELECT post_id FROM reposts WHERE user_id = :user_id)
        GROUP BY posts.post_id, posts.user_id, posts.content, posts.created_at, posts.image,
                 users.username, users.profile_picture
        ORDER BY posts.created_at DESC
    ");
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
<html lang="nl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile - Chirpyfy</title>
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
                <li><a href="profile.php?user_id=<?php echo $_SESSION['user_id']; ?>"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
                <?php if (getUserRole($conn, $_SESSION['user_id']) === 'admin'): ?>
                <li><a href="admin_panel.php" class="admin-link"><i class="fas fa-shield-alt"></i> Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <button class="btn">Tweet</button>
        </div>
        <div class="main-content">
            <div class="profile-wrapper">
                <div class="profile-cover"></div>
                <div class="profile-avatar-wrapper">
                    <img src="<?php echo htmlspecialchars(file_exists($user['profile_picture']) ? $user['profile_picture'] : 'uploads/default_avatar.png'); ?>" alt="Profile Picture" class="profile-avatar">
                </div>
                <div class="profile-content">
                    <div class="profile-header">
                        <div class="profile-info">
                            <h1 class="profile-name"><?php echo htmlspecialchars($user['username']); ?></h1>
                            <div class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="profile-bio"><?php echo htmlspecialchars($user['biography']); ?></div>
                            <div class="profile-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('F Y', strtotime($user['created_at'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($user_id == $_SESSION['user_id']): ?>
                            <div class="profile-actions">
                                <a href="edit_profile.php" class="profile-action-btn action-primary">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="posts">
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-header">
                            <img src="<?php echo htmlspecialchars($post['profile_picture']); ?>" alt="Avatar" class="avatar">
                            <div>
                                <p><strong><?php echo htmlspecialchars($post['username']); ?></strong></p>
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
                            $likers_array = $post['likers'] ? explode(',', $post['likers']) : [];
                            ?>
                            <form method="post" action="like.php" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                <button type="submit" name="<?php echo $liked ? 'unlike' : 'like'; ?>" class="action-btn" 
                                        title="<?php echo $likers_array ? 'Liked by: ' . implode(', ', $likers_array) : 'No likes yet'; ?>">
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
                                <button type="submit" name="<?php echo $reposted ? 'unrepost' : 'repost'; ?>" style="background-color: #1d9bf0; border: none; cursor: pointer;">
                                    <i class="fas fa-retweet"></i> <?php echo $reposted ? 'Unrepost' : 'Repost'; ?> (<?php echo $post['repost_count']; ?>)
                                </button>
                            </form>
                            <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                                <a href="delete.php?type=post&id=<?php echo $post['post_id']; ?>" style="text-decoration: none;">
                                    <button style="background-color: #1d9bf0; border: none; cursor: pointer;">
                                        <i class="fas fa-trash"></i> 🗑️ Delete
                                    </button>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
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
