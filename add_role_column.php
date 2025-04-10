<?php
require_once 'db.php';

try {
    // Add role column to users table if it doesn't exist
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($result->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
    }

    // First check if user_id 5 exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = 5");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        // Make user_id 5 an admin
        $conn->exec("UPDATE users SET role = 'admin' WHERE user_id = 5");
        echo "User ID 5 has been made an admin successfully";
    } else {
        echo "Error: User ID 5 does not exist";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
