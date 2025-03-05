<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['loginUser'];
    $password = $_POST['loginPass'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        echo "Login successful";
    } else {
        // Login failed
        echo "Invalid username or password";
    }
}
?>

<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chirpyfy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" id="authContainer">
    <div class="login-box" id="loginBox">
        <h2>Inloggen op Chirpyfy</h2>
        <form method="post" action="">
            <input type="text" id="loginUser" name="loginUser" placeholder="Telefoon, e-mail of gebruikersnaam">
            <input type="password" id="loginPass" name="loginPass" placeholder="Wachtwoord">
            <button type="submit">Inloggen</button>
        </form>
        <p><a href="forgot_password.php">Wachtwoord vergeten?</a></p>
        <p>Nog geen account? <a href="register.php">Meld je aan</a></p>
    </div>
</div>
</body>
</html>
