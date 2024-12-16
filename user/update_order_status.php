<?php
session_start();
require_once('../db_connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user_login.php');
    exit();
}

if (isset($_POST['order_id']) && isset($_POST['book_id'])) {
    $order_id = $_POST['order_id'];
    $book_id = $_POST['book_id']; // Fetch the specific book (item) ID
    $user_id = $_SESSION['user_id'];

    // Step 1: Update the item status for the specific book in the order
    $update_item_sql = "
        UPDATE order_items 
        SET item_status = 'Received', received_time = NOW() 
        WHERE order_id = ? AND book_id = ? AND item_status = 'Accepted'
    ";
    $stmt = $conn->prepare($update_item_sql);
    $stmt->bind_param("ii", $order_id, $book_id); // Bind both order_id and book_id
    $stmt->execute();

    // Step 2: Check if all items in the order have been marked as 'Received'
    $check_items_sql = "
        SELECT COUNT(*) AS pending_items 
        FROM order_items 
        WHERE order_id = ? AND item_status != 'Received'
    ";
    $stmt_check = $conn->prepare($check_items_sql);
    $stmt_check->bind_param("i", $order_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $pending_items = $result_check->fetch_assoc()['pending_items'];

    // If all items are received, update the overall order status to 'Delivered'
    if ($pending_items == 0) {
        $update_order_sql = "UPDATE orders SET order_status = 'Delivered' WHERE order_id = ?";
        $stmt_update_order = $conn->prepare($update_order_sql);
        $stmt_update_order->bind_param("i", $order_id);
        $stmt_update_order->execute();
    }

    // Redirect back to the orders page
    header('Location: orders.php');
    exit();
} else {
    // Redirect back if the required data is missing
    header('Location: orders.php');
    exit();
}
