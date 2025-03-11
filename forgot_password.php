<?php
require_once "db.php";
session_start();

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Redirect to reset_password.php with the email as a query parameter
            header("Location: reset_password.php?email=" . urlencode($email));
            exit();
        } else {
            $error_message = "No account found with that email.";
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
    <title>Chirpyfy - Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" id="authContainer">
    <div class="login-box">
        <div class="twitter-icon">
            <i class="fab fa-twitter"></i>
        </div>
        <h2>Forgot Password</h2>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-field">
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="Enter your email"
                       required>
            </div>
            
            <button type="submit">Send Reset Link</button>
        </form>

        <p><a href="login.php">Back to Login</a></p>
    </div>
</div>
</body>
</html>
