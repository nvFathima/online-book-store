<?php
session_start();
require '../db_connect.php';

$user_id = $_SESSION['user_id'];
$book_id = isset($_POST['book_id']) ? $_POST['book_id'] : (isset($_GET['id']) ? $_GET['id'] : null);

if ($book_id === null) {
    echo "Error: No book ID provided.";
    exit;
}

// Check if the item already exists in the wishlist
$sql = "SELECT * FROM wishlist WHERE user_id = ? AND book_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // If item is not in the wishlist, add it
    $sql = "INSERT INTO wishlist (user_id, book_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    echo "Item added to wishlist.";
} else {
    echo "Item is already in wishlist.";
}

// Remove the header redirects as we're handling this with AJAX now
?>