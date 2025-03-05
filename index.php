<?php
require_once "db.php";
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
        <form action="login.php" method="post">
            <input type="text" name="loginUser" placeholder="Telefoon, e-mail of gebruikersnaam" required>
            <input type="password" name="loginPass" placeholder="Wachtwoord" required>
            <button type="submit">Inloggen</button>
        </form>
        <p><a href="forgot_password.php">Wachtwoord vergeten?</a></p>
        <p>Nog geen account? <a href="register.php">Meld je aan</a></p>
    </div>
</div>
</body>
</html>
