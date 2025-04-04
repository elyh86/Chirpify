<?php
$dsn = 'mysql:host=localhost;dbname=chirpify';
$username = 'root';
$password = '';

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if 'like_count' column exists, and add it if it doesn't
    $result = $conn->query("SHOW COLUMNS FROM posts LIKE 'like_count'");
    if ($result->rowCount() == 0) {
        $conn->exec("ALTER TABLE posts ADD COLUMN like_count INT DEFAULT 0;");
    }

    // Check if 'repost_count' column exists, and add it if it doesn't
    $result = $conn->query("SHOW COLUMNS FROM posts LIKE 'repost_count'");
    if ($result->rowCount() == 0) {
        $conn->exec("ALTER TABLE posts ADD COLUMN repost_count INT DEFAULT 0;");
    }

    // Check if 'comments' table exists, and create it if it doesn't
    $result = $conn->query("SHOW TABLES LIKE 'comments'");
    if ($result->rowCount() == 0) {
        $conn->exec("
            CREATE TABLE comments (
                comment_id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                user_id INT NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
            );
        ");
    }
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}
?>
