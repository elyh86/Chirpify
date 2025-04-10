<?php
require_once "db.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user has admin role for certain operations
function isAdmin($conn, $user_id) {
    static $isAdminCache = [];
    if (!isset($isAdminCache[$user_id])) {
        $stmt = $conn->prepare('SELECT role FROM users WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        $isAdminCache[$user_id] = ($user && $user['role'] === 'admin');
    }
    return $isAdminCache[$user_id];
}

// Validate request
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Location: index.php");
    exit();
}

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

if ($id <= 0 || !in_array($type, ['post', 'comment', 'user'])) {
    header("Location: index.php");
    exit();
}

try {
    $conn->beginTransaction();
    $isUserAdmin = isAdmin($conn, $_SESSION['user_id']);

    switch($type) {
        case 'post':
            $stmt = $conn->prepare("SELECT user_id FROM posts WHERE post_id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            
            if ($post && ($post['user_id'] == $_SESSION['user_id'] || $isUserAdmin)) {
                // Using cascading deletes for associated records
                $conn->prepare("DELETE FROM posts WHERE post_id = ?")->execute([$id]);
            }
            break;

        case 'comment':
            $stmt = $conn->prepare("SELECT user_id FROM comments WHERE comment_id = ?");
            $stmt->execute([$id]);
            $comment = $stmt->fetch();
            
            if ($comment && ($comment['user_id'] == $_SESSION['user_id'] || $isUserAdmin)) {
                $conn->prepare("DELETE FROM comments WHERE comment_id = ?")->execute([$id]);
            }
            break;

        case 'user':
            if ($isUserAdmin && $id != $_SESSION['user_id']) {
                // Using cascading deletes for associated records
                $conn->prepare("DELETE FROM users WHERE user_id = ?")->execute([$id]);
                $redirect = 'admin_panel.php';
            }
            break;
    }

    $conn->commit();
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Delete error: " . $e->getMessage());
}

header("Location: $redirect");
exit();

