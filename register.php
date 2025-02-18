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
<div class="container2" id="authContainer2">
    <div class="register-box" id="Register-Box">
        <h2>Registreren</h2>
        <input type="text" id="registerUser" placeholder="Gebruikersnaam">
        <input type="email" id="emailUser" placeholder="E-Mail">
        <input type="password" id="registerPass" placeholder="Wachtwoord">
        <button>Register</button>
        <p>Heb je al een account? <a href="index.php">Login</a></p>
    </div>
</div>
</body>
</html>