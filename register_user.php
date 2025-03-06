<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['registerUser'];
    $email = $_POST['emailUser'];
    $password = password_hash($_POST['registerPass'], PASSWORD_DEFAULT);

    // Check if the username already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Username already exists
        header("Location: register.php?error=Gebruikersnaam is al in gebruik");
        exit();
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            // Registration successful
            header("Location: index.php");
            exit();
        } else {
            // Registration failed
            header("Location: register.php?error=Registratie mislukt");
            exit();
        }
    }
}
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
    <div class="logo">
        <img src="logo.png" alt="Chirpyfy Logo"> <!-- Add logo -->
    </div>
    <div class="register-box" id="Register-Box">
        <h2>Registreren op Chirpyfy</h2>
        <?php if (isset($_GET['error'])): ?>
            <p class="error-message"><?php echo $_GET['error']; ?></p>
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