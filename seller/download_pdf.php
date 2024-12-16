<?php
require '../mpdf/vendor/autoload.php';  // Load the mPDF library

session_start();

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: ../user_login.php");
    session_unset();
    exit;
}

include '../db_connect.php';

// Fetch all orders for the seller
$seller_id = $_SESSION['user_id'];
$sql = "
SELECT 
    o.order_id, o.order_status, o.created_at, o.total_amount,
    u.first_name, u.last_name, u.contact_no, u.email,
    sa.address_line_1, sa.city, sa.state, sa.postal_code, sa.country,
    oi.quantity, b.title, b.No_of_copies, b.unit_price
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN books b ON oi.book_id = b.title_id
JOIN user u ON o.user_id = u.user_id
JOIN shipping_addresses sa ON o.shipping_address_id = sa.address_id
WHERE b.seller_id = ?
ORDER BY o.order_id ASC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to group books by order_id
$orders = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['order_id'];

    // If we haven't encountered this order_id, initialize it
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'order_id' => $row['order_id'],
            'order_status' => $row['order_status'],
            'created_at' => $row['created_at'],
            'total_amount' => $row['total_amount'],
            'customer' => "{$row['first_name']} {$row['last_name']}",
            'contact_no' => $row['contact_no'],
            'email' => $row['email'],
            'address' => "{$row['address_line_1']}, {$row['city']}, {$row['state']}, {$row['postal_code']}, {$row['country']}",
            'books' => [] // To store multiple books per order
        ];
    }

    // Append the book details to the 'books' array for this order
    $orders[$order_id]['books'][] = [
        'title' => $row['title'],
        'quantity' => $row['quantity'],
        'unit_price' => $row['unit_price'],
    ];
}

$stmt->close();
$conn->close();

// Initialize mPDF
$mpdf = new \Mpdf\Mpdf();

// Create the HTML for the PDF
$html = '<h1 style="text-align:center;">Seller Orders Report</h1>';

// Generate the report by splitting orders into Pending, Accepted, and Order History
$pending_orders = array_filter($orders, fn($order) => $order['order_status'] === 'Paid');
$accepted_orders = array_filter($orders, fn($order) => $order['order_status'] === 'Accepted');
$order_history = array_filter($orders, fn($order) => !in_array($order['order_status'], ['Paid', 'Accepted']));

// Pending Orders Section
$html .= '<h2>Pending Orders</h2>';
if (!empty($pending_orders)) {
    foreach ($pending_orders as $order) {
        $html .= "
            <h3>Order ID: {$order['order_id']}</h3>
            <p><strong>Status:</strong> {$order['order_status']}</p>
            <p><strong>Customer:</strong> {$order['customer']}</p>
            <p><strong>Total Amount:</strong> ₹" . number_format($order['total_amount'], 2) . "</p>
            <p><strong>Delivery Address:</strong> {$order['address']}</p>
            <p><strong>Books:</strong></p>
            <ul>";
        
        // Loop through the books for this order
        foreach ($order['books'] as $book) {
            $html .= "<li>{$book['title']} - {$book['quantity']} copies @ ₹" . number_format($book['unit_price'], 2) . "</li>";
        }

        $html .= "</ul><hr>";
    }
} else {
    $html .= '<p>No pending orders.</p>';
}

// Accepted Orders Section
$html .= '<h2>Accepted Orders</h2>';
if (!empty($accepted_orders)) {
    foreach ($accepted_orders as $order) {
        $html .= "
            <h3>Order ID: {$order['order_id']}</h3>
            <p><strong>Status:</strong> {$order['order_status']}</p>
            <p><strong>Customer:</strong> {$order['customer']}</p>
            <p><strong>Total Amount:</strong> ₹" . number_format($order['total_amount'], 2) . "</p>
            <p><strong>Delivery Address:</strong> {$order['address']}</p>
            <p><strong>Books:</strong></p>
            <ul>";
        
        foreach ($order['books'] as $book) {
            $html .= "<li>{$book['title']} - {$book['quantity']} copies @ ₹" . number_format($book['unit_price'], 2) . "</li>";
        }

        $html .= "</ul><hr>";
    }
} else {
    $html .= '<p>No accepted orders.</p>';
}

// Order History Section
$html .= '<h2>Order History</h2>';
if (!empty($order_history)) {
    foreach ($order_history as $order) {
        $html .= "
            <h3>Order ID: {$order['order_id']}</h3>
            <p><strong>Status:</strong> {$order['order_status']}</p>
            <p><strong>Customer:</strong> {$order['customer']}</p>
            <p><strong>Total Amount:</strong> ₹" . number_format($order['total_amount'], 2) . "</p>
            <p><strong>Delivery Address:</strong> {$order['address']}</p>
            <p><strong>Books:</strong></p>
            <ul>";

        foreach ($order['books'] as $book) {
            $html .= "<li>{$book['title']} - {$book['quantity']} copies @ ₹" . number_format($book['unit_price'], 2) . "</li>";
        }

        $html .= "</ul><hr>";
    }
} else {
    $html .= '<p>No previous orders found.</p>';
}

// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF
$mpdf->Output('Seller_Orders_Report.pdf', 'D');  // 'D' means force download
?>
