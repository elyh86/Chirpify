<?php
require_once "db.php";
session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['loginUser']);
    $password = $_POST['loginPass'];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid username/email or password";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chirpyfy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" id="authContainer">
    <div class="login-box">
        <div class="twitter-icon">
            <i class="fab fa-twitter"></i>
        </div>
        <h2>Log in op Chirpify</h2>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-field">
                <input type="text" 
                       id="loginUser" 
                       name="loginUser" 
                       placeholder="E-mail of gebruikersnaam"
                       required>
            </div>
            
            <div class="form-field">
                <input type="password" 
                       id="loginPass" 
                       name="loginPass" 
                       placeholder="Wachtwoord"
                       required>
            </div>
            
            <button type="submit">Inloggen</button>
        </form>

        <p><a href="forgot_password.php">Wachtwoord vergeten?</a></p>
        <p>Nog geen account? <a href="register.php">Registreer je nu</a></p>
    </div>
</div>
</body>
</html>
