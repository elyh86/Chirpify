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
<html lang="nl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chirpyfy - Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <button class="theme-toggle" id="themeToggle" title="Toggle dark mode">
        <i class="fas fa-moon"></i>
    </button>
<div id="authContainer">
    <div class="login-box">
        <div class="twitter-icon">
            <i class="fab fa-twitter"></i>
        </div>
        <h2>Log in op Chirpyfy</h2>
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
    <script>
        // Dark mode functionality
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const icon = themeToggle.querySelector('i');

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);
        icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });
    </script>
</body>
</html>
