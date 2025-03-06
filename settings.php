<?php
require_once "db.php";

// Fetch user settings data
// Assuming user is logged in and user ID is stored in session
session_start();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instellingen - Chirpyfy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sidebar">
    <h2>Chirpyfy</h2>
    <a href="index.php">Home</a>
    <a href="profile.php">Profiel</a>
    <a href="settings.php">Instellingen</a>
    <a href="logout.php">Uitloggen</a>
</div>
<div class="container" id="settingsContainer">
    <h1>Instellingen</h1>
    <form action="update_settings.php" method="post">
        <label for="username">Gebruikersnaam:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <button type="submit">Bijwerken</button>
    </form>
</div>
</body>
</html>
