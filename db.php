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
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}
?>
