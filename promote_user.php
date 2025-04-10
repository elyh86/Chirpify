<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    // Check if user has admin role
    $stmt = $conn->prepare('SELECT role FROM users WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch();
    
    if ($admin['role'] !== 'admin') {
        header('Location: index.php');
        exit();
    }

    // Get the user ID to promote
    $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($user_id > 0 && $user_id != $_SESSION['user_id']) {
        // Check if user exists and is not already an admin
        $stmt = $conn->prepare('SELECT role FROM users WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user && $user['role'] !== 'admin') {
            $stmt = $conn->prepare('UPDATE users SET role = ? WHERE user_id = ?');
            $stmt->execute(['admin', $user_id]);
        }
    }
} catch (PDOException $e) {
    error_log('Promotion error: ' . $e->getMessage());
}

header('Location: admin_panel.php');
exit();
