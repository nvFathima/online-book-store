<?php
// update_cart.php
session_start();
require '../db_connect.php';

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];
$quantity = $_POST['quantity'];

// Check book availability
$sql = "SELECT No_of_copies FROM books WHERE title_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if ($book['No_of_copies'] < $quantity) {
    echo json_encode([
        'success' => false,
        'message' => 'Not enough copies available. Only ' . $book['No_of_copies'] . ' left in stock.'
    ]);
    exit;
}

// Update the quantity in the cart
$sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND book_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $quantity, $user_id, $book_id);
$stmt->execute();

// Calculate new totals
$sql = "SELECT c.quantity, b.unit_price, b.title_id FROM cart c
        JOIN books b ON c.book_id = b.title_id WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$subtotal = 0;
$item_total = 0;
while ($row = $result->fetch_assoc()) {
    if ($row['title_id'] == $book_id) {
        $item_total = $row['unit_price'] * $quantity;
    }
    $subtotal += $row['unit_price'] * $row['quantity'];
}

$tax = $subtotal * 0.10;
$grand_total = $subtotal + $tax;

$response = [
    'success' => true,
    'item_total' => number_format($item_total, 2),
    'subtotal' => number_format($subtotal, 2),
    'tax' => number_format($tax, 2),
    'grand_total' => number_format($grand_total, 2),
    'available_quantity' => $book['No_of_copies']
];

echo json_encode($response);
?>