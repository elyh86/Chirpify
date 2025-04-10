<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user has admin role
if (getUserRole($conn, $_SESSION['user_id']) !== 'admin') {
    header('Location: index.php');
    exit();
}

// Get all users
$users = $conn->query('SELECT *, COALESCE(created_at, NOW()) as created_at FROM users ORDER BY created_at DESC')->fetchAll();

// Get all posts with user info and stats
$posts = $conn->query('
    SELECT 
        p.post_id,
        p.content,
        p.created_at,
        p.image,
        p.like_count,
        p.repost_count,
        u.username,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    ORDER BY p.created_at DESC
')->fetchAll();

// Get all comments with user and post info
$comments = $conn->query('
    SELECT 
        c.comment_id,
        c.content as comment_content,
        c.created_at,
        u.username,
        SUBSTRING(p.content, 1, 100) as post_content
    FROM comments c 
    JOIN users u ON c.user_id = u.user_id 
    JOIN posts p ON c.post_id = p.post_id 
    ORDER BY c.created_at DESC
')->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Chirpify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Chirpify Admin</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Back to Site</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">User Management</h2>
        <div class="table-responsive mb-5">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role'] ?? 'user') ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td>
                            <a href="delete.php?type=user&id=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php if ($user['role'] !== 'admin'): ?>
                            <a href="promote_user.php?id=<?= $user['user_id'] ?>" class="btn btn-success btn-sm">
                                <i class="bi bi-arrow-up-circle"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 class="mb-4">Post Management</h2>
        <div class="table-responsive mb-5">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Content</th>
                        <th>Image</th>
                        <th>Stats</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['post_id']) ?></td>
                        <td><?= htmlspecialchars($post['username']) ?></td>
                        <td><?= htmlspecialchars(substr($post['content'], 0, 100)) ?><?= strlen($post['content']) > 100 ? '...' : '' ?></td>
                        <td>
                            <?php if ($post['image']): ?>
                                <img src="uploads/<?= htmlspecialchars($post['image']) ?>" alt="Post image" style="max-height: 50px;">
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= $post['like_count'] ?> likes<br>
                                <?= $post['repost_count'] ?> reposts<br>
                                <?= $post['comment_count'] ?> comments
                            </small>
                        </td>
                        <td><?= htmlspecialchars($post['created_at']) ?></td>
                        <td>
                            <a href="delete.php?type=post&id=<?= $post['post_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post? This will also delete all associated comments.')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 class="mb-4">Comment Management</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Post Content</th>
                        <th>Comment</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><?= htmlspecialchars($comment['comment_id']) ?></td>
                        <td><?= htmlspecialchars($comment['username']) ?></td>
                        <td><?= htmlspecialchars(substr($comment['post_content'], 0, 50)) ?>...</td>
                        <td><?= htmlspecialchars($comment['comment_content']) ?></td>
                        <td><?= htmlspecialchars($comment['created_at']) ?></td>
                        <td>
                            <a href="delete.php?type=comment&id=<?= $comment['comment_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this comment?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>