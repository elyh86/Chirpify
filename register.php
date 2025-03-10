<?php
require_once "db.php";

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error_message = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        try {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error_message = "Username already exists";
            } else {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $error_message = "Email already exists";
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $hashed_password);
                    
                    if ($stmt->execute()) {
                        $success_message = "Registration successful! You can now login.";
                    } else {
                        $error_message = "Registration failed";
                    }
                }
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
    <title>Register - Chirpify</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" id="authContainer">
    <div class="register-box">
        <h2>Registreren bij Chirpify</h2>
        
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <input type="text" name="username" placeholder="Gebruikersnaam" required>
            </div>
            
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Wachtwoord" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Bevestig wachtwoord" required>
            </div>
            
            <button type="submit">Registreren</button>
        </form>
        
        <p>Al een account? <a href="login.php">Log in</a></p>
    </div>
</div>
</body>
</html>