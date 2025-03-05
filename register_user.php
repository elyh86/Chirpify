<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['registerUser'];
    $email = $_POST['emailUser'];
    $password = password_hash($_POST['registerPass'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);

    if ($stmt->execute()) {
        // Registration successful
        echo "Registration successful";
    } else {
        // Registration failed
        echo "Registration failed";
    }
}
?>
