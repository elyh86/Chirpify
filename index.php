<?php
require_once "db.php"; // Include the database connection file
session_start(); // Start the session

if (!isset($_SESSION['user_id'])) { // Check if the user is logged in
    header("Location: login.php"); // Redirect to login page if not logged in
    exit(); // Exit the script
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id"); // Prepare the SQL statement to select the user
    $stmt->bindParam(':user_id', $_SESSION['user_id']); // Bind the user ID parameter
    $stmt->execute(); // Execute the query
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the user data

    if (!$user) { // If the user is not found
        header("Location: login.php"); // Redirect to login page
        exit(); // Exit the script
    }

    if (!isset($user['profile_picture']) || empty($user['profile_picture'])) { // Initialize profile_picture if not set
        $user['profile_picture'] = 'default_avatar.png'; // Default avatar image
    }
} catch (PDOException $e) { // Catch any PDO exceptions
    echo "Database error: " . $e->getMessage(); // Display the database error message
    exit(); // Exit the script
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_post'])) { // Handle new post submission
    $content = trim($_POST['content']); // Get the post content from the form
    if (!empty($content)) { // Check if the content is not empty
        try {
            $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (:user_id, :content)"); // Prepare the SQL statement to insert the post
            $stmt->bindParam(':user_id', $_SESSION['user_id']); // Bind the user ID parameter
            $stmt->bindParam(':content', $content); // Bind the content parameter
            $stmt->execute(); // Execute the insert query
            header("Location: index.php"); // Redirect to the same page to prevent form resubmission
            exit(); // Exit the script
        } catch (PDOException $e) { // Catch any PDO exceptions
            echo "Database error: " . $e->getMessage(); // Display the database error message
        }
    }
}

$posts = []; // Initialize the posts array
try {
    $stmt = $conn->prepare("SELECT posts.*, users.username, users.profile_picture, 
                            (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.post_id) AS like_count, 
                            (SELECT COUNT(*) FROM reposts WHERE reposts.post_id = posts.post_id) AS repost_count 
                            FROM posts JOIN users ON posts.user_id = users.user_id ORDER BY posts.created_at DESC"); // Prepare the SQL statement to select all posts
    $stmt->execute(); // Execute the query
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all posts
} catch (PDOException $e) { // Catch any PDO exceptions
    echo "Database error: " . $e->getMessage(); // Display the database error message
}

try {
    $stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = :user_id"); // Prepare the SQL statement to select all likes
    $stmt->bindParam(':user_id', $_SESSION['user_id']); // Bind the user ID parameter
    $stmt->execute(); // Execute the query
    $likes = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all likes

    $stmt = $conn->prepare("SELECT * FROM reposts WHERE user_id = :user_id"); // Prepare the SQL statement to select all reposts
    $stmt->bindParam(':user_id', $_SESSION['user_id']); // Bind the user ID parameter
    $stmt->execute(); // Execute the query
    $reposts = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all reposts
} catch (PDOException $e) { // Catch any PDO exceptions
    echo "Database error: " . $e->getMessage(); // Display the database error message
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"> <!-- Set the character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Set the viewport for responsive design -->
    <title>Chirpyfy</title> <!-- Set the page title -->
    <link rel="stylesheet" href="style.css"> <!-- Link to the CSS stylesheet -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> <!-- Link to Font Awesome for icons -->
</head>
<body>
<div class="container">
    <div class="sidebar">
        <div class="logo">
            <i class="fab fa-twitter"></i> <!-- Twitter icon -->
        </div>
        <ul class="menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li> <!-- Home link -->
            <li><a href="profile.php?user_id=<?php echo $_SESSION['user_id']; ?>"><i class="fas fa-user"></i> Profile</a></li> <!-- Profile link -->
            <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li> <!-- About link -->
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li> <!-- Logout link -->
        </ul>
        <button class="btn">Tweet</button> <!-- Tweet button -->
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Home</h1> <!-- Page header -->
        </div>
        <div class="new-post">
            <form method="post" action="">
                <textarea name="content" placeholder="What's happening?" required></textarea> <!-- Post content textarea -->
                <button type="submit" name="new_post">Chirp</button> <!-- Submit button -->
            </form>
        </div>
        <div class="posts">
            <?php foreach ($posts as $post): ?> <!-- Loop through each post -->
                <div class="post">
                    <div class="post-header">
                        <img src="<?php echo htmlspecialchars($post['profile_picture']); ?>" alt="Avatar" class="avatar"> <!-- Post author's avatar -->
                        <div>
                            <p><strong><a href="profile.php?user_id=<?php echo $post['user_id']; ?>"><?php echo htmlspecialchars($post['username']); ?></a></strong></p> <!-- Post author's username -->
                            <p><small><?php echo $post['created_at']; ?></small></p> <!-- Post creation date -->
                        </div>
                    </div>
                    <p><?php echo htmlspecialchars($post['content']); ?></p> <!-- Post content -->
                    <div class="post-actions">
                        <?php
                        $liked = false;
                        foreach ($likes as $like) { // Check if the post is liked by the user
                            if ($like['post_id'] == $post['post_id']) {
                                $liked = true;
                                break;
                            }
                        }
                        ?>
                        <form method="post" action="like.php" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>"> <!-- Hidden input for post ID -->
                            <button type="submit" name="<?php echo $liked ? 'unlike' : 'like'; ?>" class="action-btn">
                                ❤️ <?php echo $liked ? 'Unlike' : 'Like'; ?> (<?php echo $post['like_count']; ?>)
                            </button>
                        </form>
                        <?php
                        $reposted = false;
                        foreach ($reposts as $repost) { // Check if the post is reposted by the user
                            if ($repost['post_id'] == $post['post_id']) {
                                $reposted = true;
                                break;
                            }
                        }
                        ?>
                        <form method="post" action="repost.php" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>"> <!-- Hidden input for post ID -->
                            <button type="submit" name="<?php echo $reposted ? 'unrepost' : 'repost'; ?>" class="action-btn">
                                🔁 <?php echo $reposted ? 'Unrepost' : 'Repost'; ?> (<?php echo $post['repost_count']; ?>)
                            </button>
                        </form>
                        <?php if ($post['user_id'] == $_SESSION['user_id']): ?> <!-- Check if the post belongs to the user -->
                            <form method="post" action="delete.php" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>"> <!-- Hidden input for post ID -->
                                <button type="submit" name="delete" class="action-btn">🗑️ Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="comments">
                        <h4>Comments</h4>
                        <?php
                        // Prepare a new statement for fetching comments
                        $commentStmt = $conn->prepare("SELECT comments.*, users.username, users.profile_picture 
                                                       FROM comments 
                                                       JOIN users ON comments.user_id = users.user_id 
                                                       WHERE comments.post_id = :post_id ORDER BY comments.created_at ASC");
                        $commentStmt->bindParam(':post_id', $post['post_id'], PDO::PARAM_INT);
                        $commentStmt->execute(); // Execute the query
                        $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all comments for the post
                        ?>
                        <?php foreach ($comments as $comment): ?> <!-- Loop through each comment -->
                            <div class="comment">
                                <img src="<?php echo htmlspecialchars($comment['profile_picture']); ?>" alt="Avatar" class="avatar"> <!-- Comment author's avatar -->
                                <div>
                                    <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong></p> <!-- Comment author's username -->
                                    <p><?php echo htmlspecialchars($comment['content']); ?></p> <!-- Comment content -->
                                    <p><small><?php echo $comment['created_at']; ?></small></p> <!-- Comment creation date -->
                                </div>
                                <?php if ($comment['user_id'] == $_SESSION['user_id']): ?> <!-- Check if the comment belongs to the user -->
                                    <form method="post" action="delete_comment.php" style="display: inline;">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>"> <!-- Hidden input for comment ID -->
                                        <button type="submit" name="delete" class="action-btn">🗑️ Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <form method="post" action="comment.php">
                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>"> <!-- Hidden input for post ID -->
                            <textarea name="content" placeholder="Write a comment..." required></textarea> <!-- Comment content textarea -->
                            <button type="submit" class="action-btn">Comment</button> <!-- Submit button -->
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>
