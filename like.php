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

    if (isset($_POST['like'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)");
            $stmt->bindParam(':post_id', $post_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // Update like count
            $stmt = $conn->prepare("UPDATE posts SET like_count = like_count + 1 WHERE post_id = :post_id");
            $stmt->bindParam(':post_id', $post_id);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    } elseif (isset($_POST['unlike'])) {
        try {
            $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id");
            $stmt->bindParam(':post_id', $post_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            // Update like count
            $stmt = $conn->prepare("UPDATE posts SET like_count = like_count - 1 WHERE post_id = :post_id");
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
