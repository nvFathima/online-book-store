<?php
require '../mpdf/vendor/autoload.php';
require_once('../db_connect.php');

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for the user
function getOrders($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT o.*, sa.address_line_1, sa.city, sa.state, sa.postal_code 
                            FROM orders o
                            JOIN shipping_addresses sa ON o.shipping_address_id = sa.address_id
                            WHERE o.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch ordered items for each order
function getOrderItems($order_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT oi.*, b.title, b.unit_price 
                            FROM order_items oi
                            JOIN books b ON oi.book_id = b.title_id
                            WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get orders and order items
$orders = getOrders($user_id);
$order_items = [];
foreach ($orders as $order) {
    $order_items[$order['order_id']] = getOrderItems($order['order_id']);
}

// Create an instance of mPDF
$mpdf = new \Mpdf\Mpdf();

// Start buffering HTML content
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .order { margin-bottom: 20px; }
        .order-header { font-weight: bold; }
        .order-items { margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
    </style>
</head>
<body>

<h2>Order History</h2>

<?php foreach ($orders as $order): ?>
    <div class="order">
        <div class="order-header">
            <p>Order ID: <?= htmlspecialchars($order['order_id']) ?></p>
            <p>Total Amount: ₹<?= htmlspecialchars($order['total_amount']) ?></p>
            <p>Order Status: <?= htmlspecialchars($order['order_status']) ?></p>
            <p>Shipping Address: <?= htmlspecialchars($order['address_line_1'] . ', ' . $order['city'] . ', ' . $order['state'] . ', ' . $order['postal_code']) ?></p>
        </div>

        <h4>Ordered Items:</h4>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                    <th>Expected Delivery</th>
                    <th>Delivered On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items[$order['order_id']] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td>₹<?= htmlspecialchars($item['unit_price']) ?></td>
                        <td>₹<?= htmlspecialchars($item['quantity'] * $item['unit_price']) ?></td>
                        <td><?= htmlspecialchars($item['expected_delivery']) ?></td>
                        <td><?= htmlspecialchars($item['received_time']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

</body>
</html>

<?php
// Capture the buffer and store it in a variable
$html = ob_get_clean();

// Write the HTML to the PDF using mPDF
$mpdf->WriteHTML($html);

// Output the PDF to the browser
$mpdf->Output('Order_History.pdf', \Mpdf\Output\Destination::INLINE); // INLINE will show the PDF in the browser
// Use $mpdf->Output('Order_History.pdf', \Mpdf\Output\Destination::DOWNLOAD); to download directly
?>
