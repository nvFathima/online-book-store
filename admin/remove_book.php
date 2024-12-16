<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $rejection_reason = $_POST['rejection_reason'];

    // Retrieve seller_id and book title
    $stmt = $conn->prepare("SELECT seller_id, title FROM books WHERE title_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $seller_id = $book['seller_id'];
    $book_title = $book['title'];
    $stmt->close();

    // Insert rejection reason into rejection_messages table
    $stmt = $conn->prepare("INSERT INTO rejection_messages (book_title, seller_id, rejection_reason) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $book_title, $seller_id, $rejection_reason);
    $stmt->execute();
    $stmt->close();

    // Remove the book and its associated images
    $stmt = $conn->prepare("DELETE FROM books WHERE title_id = ?");
    $stmt->bind_param("i", $book_id);
    
    if ($stmt->execute()) {
        header("Location: manage_booksales.php?msg=Book rejected and removed successfully");
    } else {
        header("Location: manage_booksales.php?error=Error removing book");
    }

    $stmt->close();
}

header("Location: manage_booksales.php");
exit;
?>
