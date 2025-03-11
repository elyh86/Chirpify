<?php
require_once "db.php";
session_start();

$error_message = "";
$success_message = "";
$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $success_message = "Password has been reset successfully.";
            } else {
                $error_message = "No account found with that email.";
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
    <title>Chirpyfy - Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container" id="authContainer">
    <div class="login-box">
        <div class="twitter-icon">
            <i class="fab fa-twitter"></i>
        </div>
        <h2>Reset Password</h2>
        
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
                       value="<?php echo htmlspecialchars($email); ?>"
                       required>
            </div>
            
            <div class="form-field">
                <input type="password" 
                       id="new_password" 
                       name="new_password" 
                       placeholder="New Password"
                       required>
            </div>
            
            <div class="form-field">
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       placeholder="Confirm Password"
                       required>
            </div>
            
            <button type="submit">Reset Password</button>
        </form>

        <p><a href="login.php">Back to Login</a></p>
    </div>
</div>
</body>
</html>
