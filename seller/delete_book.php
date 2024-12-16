<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: ../user_login.php");
    exit;
}

$bookId = intval($_GET['id']);
$sellerId = $_SESSION['user_id'];

// Ensure the book belongs to the logged-in seller
$stmt = $conn->prepare("DELETE FROM books WHERE title_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $bookId, $sellerId);

if ($stmt->execute()) {
    header("Location: manage_books.php?success=" . urlencode("Book removed successfully."));
} else {
    header("Location: manage_books.php?error=" . urlencode("Error removing book."));
}

$stmt->close();
$conn->close();
?>
