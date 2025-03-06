<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email WHERE id = :user_id");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':user_id', $user_id);

    if ($stmt->execute()) {
        // Update successful
        header("Location: settings.php");
        exit();
    } else {
        // Update failed
        echo "Failed to update settings";
    }
}
?>
