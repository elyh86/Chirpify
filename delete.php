<?php
require_once "db.php"; // Include the database connection file
session_start(); // Start the session

if (!isset($_SESSION['user_id'])) { // Check if the user is logged in
    header("Location: login.php"); // Redirect to login page if not logged in
    exit(); // Exit the script
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { // Check if the request method is POST
    $post_id = $_POST['post_id']; // Get the post ID from the form
    $user_id = $_SESSION['user_id']; // Get the user ID from the session

    try {
        $stmt = $conn->prepare("SELECT * FROM posts WHERE post_id = :post_id AND user_id = :user_id"); // Prepare the SQL statement to select the post
        $stmt->bindParam(':post_id', $post_id); // Bind the post ID parameter
        $stmt->bindParam(':user_id', $user_id); // Bind the user ID parameter
        $stmt->execute(); // Execute the query
        $post = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the post data

        if ($post) { // If the post belongs to the user
            $stmt = $conn->prepare("DELETE FROM posts WHERE post_id = :post_id AND user_id = :user_id"); // Prepare the SQL statement to delete the post
            $stmt->bindParam(':post_id', $post_id); // Bind the post ID parameter
            $stmt->bindParam(':user_id', $user_id); // Bind the user ID parameter
            $stmt->execute(); // Execute the delete query
        }
    } catch (PDOException $e) { // Catch any PDO exceptions
        echo "Database error: " . $e->getMessage(); // Display the database error message
    }

    header("Location: index.php"); // Redirect to the index page
    exit(); // Exit the script
}
?>
