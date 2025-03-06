<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = $_POST['content'];
    $image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);
    }

    $stmt = $conn->prepare("INSERT INTO posts (content, image) VALUES (:content, :image)");
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':image', $image);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Failed to post";
    }
}
?>
