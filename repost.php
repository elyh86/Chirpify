<?php
require_once "db.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['repost'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO reposts (post_id, user_id) VALUES (:post_id, :user_id)");
            $stmt->bindParam(':post_id', $post_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // Update repost count
            $stmt = $conn->prepare("UPDATE posts SET repost_count = repost_count + 1 WHERE post_id = :post_id");
            $stmt->bindParam(':post_id', $post_id);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    } elseif (isset($_POST['unrepost'])) {
        try {
            $stmt = $conn->prepare("DELETE FROM reposts WHERE post_id = :post_id AND user_id = :user_id");
            $stmt->bindParam(':post_id', $post_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // Update repost count
            $stmt = $conn->prepare("UPDATE posts SET repost_count = repost_count - 1 WHERE post_id = :post_id");
            $stmt->bindParam(':post_id', $post_id);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }

    header("Location: index.php");
    exit();
}
?>
