<?php
require_once "db.php";

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    $stmt = $conn->prepare("UPDATE posts SET likes = likes + 1 WHERE id = :post_id");
    $stmt->bindParam(':post_id', $post_id);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Failed to like post";
    }
}
?>
