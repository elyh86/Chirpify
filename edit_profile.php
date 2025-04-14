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
<html lang="nl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Chirpify</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <button class="theme-toggle" id="themeToggle" title="Toggle dark mode">
        <i class="fas fa-moon"></i>
    </button>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <i class="fab fa-twitter"></i>
            </div>
            <ul class="menu">
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="profile.php?user_id=<?php echo $_SESSION['user_id']; ?>"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="edit_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
                <?php if (getUserRole($conn, $_SESSION['user_id']) === 'admin'): ?>
                <li><a href="admin_panel.php"><i class="fas fa-shield-alt"></i> Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <a href="index.php" class="btn">Tweet</a>
        </div>
        
        <div class="main-content">
            <div class="edit-profile-wrapper">
                <div class="edit-profile-header">
                    <h1>Edit Your Profile</h1>
                    <p>Customize your profile information</p>
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

                <form method="post" action="" enctype="multipart/form-data" class="edit-profile-form">
                    <div class="media-upload-section">
                        <div class="media-preview">
                            <img src="<?php echo htmlspecialchars(!empty($user['profile_picture']) ? $user['profile_picture'] : 'uploads/default_avatar.png'); ?>" 
                                 alt="Profile Preview">
                        </div>
                        <div class="upload-btn-wrapper">
                            <label for="profile_picture" class="upload-btn">
                                <i class="fas fa-camera"></i> Change Photo
                            </label>
                            <input type="file" 
                                   id="profile_picture" 
                                   name="profile_picture"
                                   accept="image/*">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               value="<?php echo htmlspecialchars($user['username']); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="biography">Biography</label>
                        <textarea id="biography" 
                                  name="biography" 
                                  rows="4"
                                  placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['biography']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="New password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Confirm new password">
                    </div>

                    <div class="form-footer">
                        <a href="profile.php?user_id=<?php echo $_SESSION['user_id']; ?>" class="cancel-btn">Cancel</a>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Preview image before upload
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.media-preview img').src = e.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

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
