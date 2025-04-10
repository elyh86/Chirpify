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

            // Handle profile picture upload
            if ($profile_picture['size'] > 0) {
                if ($profile_picture['size'] <= 2 * 1024 * 1024) { // Limit image size to 2MB
                    $target_dir = "uploads/";
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $filename = 'avatar_' . time() . '_' . basename($profile_picture["name"]);
                    $target_file = $target_dir . $filename;
                    if (move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
                        $stmt = $conn->prepare("UPDATE users SET profile_picture = :profile_picture WHERE user_id = :user_id");
                        $stmt->bindParam(':profile_picture', $target_file);
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

    <style>
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #1d9bf0;
        }

        .register-box {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            text-align: center;
            border: 2px solid #e1e8ed;
        }

        .twitter-icon {
            font-size: 50px;
            color: #1d9bf0;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #0f1419;
        }

        .form-field {
            width: 100%;
            margin-bottom: 20px;
            text-align: left;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            background: #ffffff;
            font-size: 16px;
            color: #0f1419;
        }

        .custom-file-upload {
            display: inline-block;
            padding: 10px 20px;
            background: #1d9bf0;
            color: white;
            border-radius: 9999px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            transition: background 0.3s;
            margin-bottom: 10px;
        }

        .custom-file-upload:hover {
            background: #117dc0;
        }

        .current-image {
            margin-top: 10px;
            text-align: center;
        }

        .current-image img {
            max-width: 100px;
            border-radius: 50%;
            border: 2px solid #e1e8ed;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #1d9bf0;
            color: white;
            border: none;
            border-radius: 9999px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        button[type="submit"]:hover {
            background: #117dc0;
        }

        .error-message {
            color: #dc2626;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
        }

        .success-message {
            color: #059669;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
        }

        .register-box button[type="submit"] {
            background-color: white !important;
            color: #1d9bf0 !important;
            font-weight: bold;
            border: none;
            padding: 12px 24px;
            border-radius: 9999px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .register-box button[type="submit"]:hover {
            background-color: #f5f5f5 !important;
        }

        .register-box a {
            color: white !important;
            text-decoration: none;
            font-weight: 500;
        }

        .register-box a:hover {
            text-decoration: underline;
        }

        .custom-file-upload {
            background-color: white !important;
            color: #1d9bf0 !important;
            padding: 8px 16px;
            border-radius: 9999px;
            cursor: pointer;
            display: inline-block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .custom-file-upload:hover {
            background-color: #f5f5f5 !important;
        }
    </style>
</head>
<body style="
    background-color: #1d9bf0;
">
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
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       placeholder="Username"
                       value="<?php echo htmlspecialchars($user['username']); ?>"
                       required>
            </div>
            
            <div class="form-field">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="Email"
                       value="<?php echo htmlspecialchars($user['email']); ?>"
                       required>
            </div>

            <div class="form-field">
                <label for="biography">Biography</label>
                <textarea id="biography" 
                          name="biography" 
                          placeholder="Biography"
                          required><?php echo htmlspecialchars($user['biography']); ?></textarea>
            </div>
            
            <div class="form-field">
                <label for="password">New Password (leave blank to keep current)</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="New Password">
            </div>
            
            <div class="form-field">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       placeholder="Confirm Password">
            </div>

            <div class="form-field">
                <label for="profile_picture">Choose Profile Picture</label>
                <label for="profile_picture" class="custom-file-upload">Choose Profile Picture</label>
                <input type="file" 
                       id="profile_picture" 
                       name="profile_picture"
                       accept="image/*">
                <?php if (!empty($user['profile_picture'])): ?>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Current Profile Picture" style="max-width: 100px; border-radius: 50%;">
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit">Update Profile</button>
        </form>

        <p class="back-link">
            <a href="profile.php?user_id=<?php echo $user_id; ?>">Back to Profile</a>
        </p>
    </div>
</div>
</body>
</html>
