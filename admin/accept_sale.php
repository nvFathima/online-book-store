<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sale_id = $_POST['sale_id'];

    // Update the book status to 'accepted'
    $stmt = $conn->prepare("UPDATE books SET status = 'accepted' WHERE title_id = ?");
    $stmt->bind_param("i", $sale_id);

    if ($stmt->execute()) {
        header("Location: manage_book_sales.php?msg=Book sale accepted successfully");
    } else {
        header("Location: manage_book_sales.php?error=Error accepting book sale");
    }

    $stmt->close();
}

header("Location: manage_booksales.php");
exit;
?>
