<?php
session_start();
require '../db_connect.php'; 

$user_id = $_SESSION['user_id']; 
$book_id = $_POST['book_id'];

// If "Buy Now" button is clicked, skip adding to the cart
if (isset($_POST['buy_now'])) {
    $_SESSION['book_id'] = $book_id;  // Store the book ID in the session for checkout
    
    // Redirect to the checkout page
    header('Location: checkout.php');
    exit(); 
}

// Handle "Add to Cart" functionality separately
// This block runs if the user clicked the "Add to Cart" button
if (!isset($_POST['buy_now'])) {
    // Check the availability in the books table
    $sql = "SELECT No_of_copies FROM books WHERE title_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    if ($book && $book['No_of_copies'] > 0) {
        // Check if the item already exists in the cart
        $sql = "SELECT quantity FROM cart WHERE user_id = ? AND book_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_item = $result->fetch_assoc();

        if ($cart_item) {
            // If item exists, check if we can increment the quantity
            if ($cart_item['quantity'] < $book['No_of_copies']) {
                $sql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND book_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $book_id);
                $stmt->execute();
                echo "Item quantity increased in cart.";
            } else {
                echo "Maximum available quantity already in cart.";
            }
        } else {
            // If item doesn't exist, add it to the cart
            $sql = "INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, 1)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $book_id);
            $stmt->execute();
            echo "Item added to cart.";
        }
    } else {
        echo "Sorry, this item is out of stock.";
    }
}
?>