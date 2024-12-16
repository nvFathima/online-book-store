<?php
session_start();
require '../db_connect.php';

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];

// Remove the item from the cart
$sql = "DELETE FROM cart WHERE user_id = ? AND book_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $book_id);
$stmt->execute();

// Calculate new totals
$sql = "SELECT c.quantity, b.unit_price FROM cart c
        JOIN books b ON c.book_id = b.title_id WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$subtotal = 0;
while ($row = $result->fetch_assoc()) {
    $subtotal += $row['unit_price'] * $row['quantity'];
}

$tax = $subtotal * 0.10;
$grand_total = $subtotal + $tax;

$response = [
    'success' => true,
    'subtotal' => number_format($subtotal, 2),
    'tax' => number_format($tax, 2),
    'grand_total' => number_format($grand_total, 2)
];

echo json_encode($response);
?>
