<?php
require_once "db.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comment_id = $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $conn->prepare("SELECT * FROM comments WHERE comment_id = :comment_id AND user_id = :user_id");
        $stmt->bindParam(':comment_id', $comment_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($comment) {
            $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = :comment_id AND user_id = :user_id");
            $stmt->bindParam(':comment_id', $comment_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }

    header("Location: index.php");
    exit();
}
?>
