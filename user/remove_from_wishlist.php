<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header('Location: ../user_login.php');
    exit();
}

if (!isset($_GET['id'])) {
    // Redirect to wishlist page if no book ID is provided
    header('Location: wishlist.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = $_GET['id'];

// Prepare SQL to delete the item from wishlist
$sql = "DELETE FROM wishlist WHERE user_id = ? AND book_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $book_id);

if ($stmt->execute()) {
    echo "Item removed from wishlist successfully.";
} else {
    echo "Failed to remove item from wishlist.";
}

// Redirect back to the wishlist page
header('Location: wishlist.php');
exit();
?>