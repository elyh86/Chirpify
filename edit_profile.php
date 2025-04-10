<?php
require_once "db.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";
$success_message = "";

// Fetch user data
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User not found, redirect to home page
        header("Location: index.php");
        exit();
    }

    // Initialize biography if not set
    if (!isset($user['biography'])) {
        $user['biography'] = '';
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $biography = trim($_POST['biography']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $profile_picture = $_FILES['profile_picture'];

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email, biography = :biography WHERE user_id = :user_id");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':biography', $biography);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
            }

            if ($profile_picture['size'] > 0) {
                if ($profile_picture['size'] <= 2 * 1024 * 1024) { // Limit image size to 2MB
                    $target_dir = "uploads/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $filename = time() . '_' . basename($profile_picture["name"]);
                    $target_file = $target_dir . $filename;
                    if (move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
                        $stmt = $conn->prepare("UPDATE users SET profile_picture = :profile_picture WHERE user_id = :user_id");
                        $stmt->bindParam(':profile_picture', $filename);
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();
                    } else {
                        $error_message = "Failed to upload profile picture.";
                    }
                } else {
                    $error_message = "Profile picture size must be less than 2MB.";
                }
            }

            $success_message = "Profile updated successfully";
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
    <title>Edit Profile - Chirpyfy</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
<div class="container" id="authContainer">
    <div class="register-box">
        <div class="twitter-icon">
            <i class="fab fa-twitter"></i>
        </div>
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

        <h2>Edit Profile</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="form-field">
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="Username"
                       value="<?php echo htmlspecialchars($user['username']); ?>"
                       required>
            </div>
            
            <div class="form-field">
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="Email"
                       value="<?php echo htmlspecialchars($user['email']); ?>"
                       required>
            </div>

            <div class="form-field">
                <textarea id="biography" 
                          name="biography" 
                          placeholder="Biography"
                          required><?php echo htmlspecialchars($user['biography']); ?></textarea>
            </div>
            
            <div class="form-field">
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="New Password (leave blank to keep current)">
            </div>
            
            <div class="form-field">
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       placeholder="Confirm Password">
            </div>

            <div class="form-field">
                <label for="profile_picture" class="custom-file-upload">Choose Profile Picture</label>
                <input type="file" 
                       id="profile_picture" 
                       name="profile_picture">
            </div>
            
            <button type="submit">Update Profile</button>
        </form>

        <p><a href="profile.php?user_id=<?php echo $user_id; ?>">Back to Profile</a></p>
    </div>
</div>
</body>
</html>
