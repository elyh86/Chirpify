<?php
require db.php;
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
        <h2>Inloggen</h2>
        <input type="text" id="loginUser" placeholder="Gebruikersnaam">
        <input type="password" id="loginPass" placeholder="Wachtwoord">
        <button onclick="login()">Login</button>
        <p>Nog geen account? <a href="register.php" onclick="showRegister()">Registreer</a></p>
    </div>
</div>
</body>
</html>
