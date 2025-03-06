<?php
require_once "db.php";

// Fetch user profile data
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
    <title>Profiel - Chirpyfy</title>
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
<div class="container" id="profileContainer">
    <h1>Profiel</h1>
    <p>Gebruikersnaam: <?php echo htmlspecialchars($user['username']); ?></p>
    <p>E-mail: <?php echo htmlspecialchars($user['email']); ?></p>
</div>
</body>
</html>
