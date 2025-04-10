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
    $content = trim($_POST['content']);
    $image = $_FILES['image'];

    $image_path = null;
    if ($image['size'] > 0) {
        if ($image['size'] <= 2 * 1024 * 1024) { // Limit image size to 2MB
            $target_dir = "uploads/comments/";
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
            $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, image) VALUES (:post_id, :user_id, :content, :image)");
            $stmt->bindParam(':post_id', $post_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image', $image_path);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
    header("Location: index.php");
    exit();
}
?>
