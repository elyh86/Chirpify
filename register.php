<?php
require_once "db.php";
session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['registerUser']);
    $email = trim($_POST['registerEmail']);
    $password = $_POST['registerPass'];
    $confirm_password = $_POST['confirmPass'];

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $error_message = "Username or email already exists";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->execute();
                $_SESSION['user_id'] = $conn->lastInsertId();
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chirpyfy - Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="authContainer">
    <div class="register-box">
        <div class="twitter-icon">
            <i class="fab fa-twitter"></i>
        </div>
        <h2>Registreer je op Chirpyfy</h2>
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-field">
                <input type="text" 
                       id="registerUser" 
                       name="registerUser" 
                       placeholder="Gebruikersnaam"
                       required>
            </div>
            <div class="form-field">
                <input type="email" 
                       id="registerEmail" 
                       name="registerEmail" 
                       placeholder="E-mail"
                       required>
            </div>
            <div class="form-field">
                <input type="password" 
                       id="registerPass" 
                       name="registerPass" 
                       placeholder="Wachtwoord"
                       required>
            </div>
            <div class="form-field">
                <input type="password" 
                       id="confirmPass" 
                       name="confirmPass" 
                       placeholder="Bevestig wachtwoord"
                       required>
            </div>
            <button type="submit">Registreren</button>
        </form>
        <p>Al een account? <a href="login.php">Log in</a></p>
    </div>
</div>
</body>
</html>