<?php
require_once "db.php";

$error_message = isset($_GET['error']) ? $_GET['error'] : "";
?>

<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren op Chirpyfy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container2" id="authContainer2">
    <div class="register-box" id="Register-Box">
        <h2>Registreren op Chirpyfy</h2>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="register_user.php" method="post">
            <input type="text" name="registerUser" placeholder="Gebruikersnaam" required>
            <input type="email" name="emailUser" placeholder="E-Mail" required>
            <input type="password" name="registerPass" placeholder="Wachtwoord" required>
            <button type="submit">Registreren</button>
        </form>
        <p>Heb je al een account? <a href="login.php">Inloggen</a></p>
    </div>
</div>
</body>
</html>