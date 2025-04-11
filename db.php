<?php
$dsn = 'mysql:host=localhost;dbname=chirpify';
$username = 'root';
$password = '';

// Function to get user role
function getUserRole($conn, $user_id) {
    try {
        $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['role'] ?? 'user';
    } catch (PDOException $e) {
        error_log("Error getting user role: " . $e->getMessage());
        return 'user';
    }
}

try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Function to check if a column exists in a table
    function columnExists($conn, $table, $column) {
        $result = $conn->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
        return $result->rowCount() > 0;
    }

    // Function to check if a table exists
    function tableExists($conn, $table) {
        $result = $conn->query("SHOW TABLES LIKE '{$table}'");
        return $result->rowCount() > 0;
    }

    // Add required columns to posts table
    if (!columnExists($conn, 'posts', 'like_count')) {
        $conn->exec("ALTER TABLE posts ADD COLUMN like_count INT DEFAULT 0");
    }
    if (!columnExists($conn, 'posts', 'repost_count')) {
        $conn->exec("ALTER TABLE posts ADD COLUMN repost_count INT DEFAULT 0");
    }
    if (!columnExists($conn, 'posts', 'image')) {
        $conn->exec("ALTER TABLE posts ADD COLUMN image VARCHAR(255) DEFAULT NULL");
    }

    // Create comments table if it doesn't exist
    if (!tableExists($conn, 'comments')) {
        $conn->exec("
            CREATE TABLE comments (
                comment_id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                user_id INT NOT NULL,
                content TEXT NOT NULL,
                image VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
            )
        ");
    }

    // Add required columns to users table
    if (!columnExists($conn, 'users', 'role')) {
        $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
    }
    if (!columnExists($conn, 'users', 'created_at')) {
        $conn->exec("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }
    if (!columnExists($conn, 'users', 'profile_picture')) {
        $conn->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT 'default_avatar.png'");
    }

    // Add biography column to users table
    if (!columnExists($conn, 'users', 'biography')) {
        $conn->exec("ALTER TABLE users ADD COLUMN biography TEXT");
    }

    
    // Set admin role for user_id 5 if it exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = 5");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $conn->exec("UPDATE users SET role = 'admin' WHERE user_id = 5");
    }
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}
?>
