<?php
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
    oi.quantity, oi.expected_delivery, oi.total_price, b.title, b.No_of_copies, b.unit_price
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN books b ON oi.book_id = b.title_id
JOIN user u ON o.user_id = u.user_id
JOIN shipping_addresses sa ON o.shipping_address_id = sa.address_id
WHERE oi.seller_id = ?
ORDER BY o.order_id ASC;";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

// Categorize orders by status
$pending_orders = [];
$accepted_orders = [];
$order_history = [];

while ($row = $result->fetch_assoc()) {
    // Reset sum for each new order
    $sum = 0;
    
    // Fetch the total price for the specific seller and order
    $sql2 = "SELECT total_price FROM order_items WHERE seller_id = ? AND order_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ii", $seller_id, $row['order_id']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    // Sum the total prices for this specific order
    while ($subRow = $result2->fetch_assoc()) {
        $sum += $subRow['total_price'];
    }
    $stmt2->close();

    // Add sum as an additional field in the order array
    $row['total_price_sum'] = $sum;

    // Categorize orders based on status
    if ($row['order_status'] === 'Paid') {
        $pending_orders[] = $row;
    } elseif ($row['order_status'] === 'Accepted') {
        $accepted_orders[] = $row;
    } else {
        $order_history[] = $row;
    }
}

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - BookHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="../css/order_style.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <button onclick="location.href='seller_dashboard.php'" class="dashboard-btn">Dashboard</button><br><br><br>
        <button onclick="location.href='manage_orders.php'" class="orders-btn">Manage Orders</button>
        <button  class="" onclick="window.open('download_pdf.php', '_blank')">Download Orders PDF</button>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <h2>Manage Orders</h2>
        <p class="subtitle">View and manage your pending, accepted, and past orders</p>

        <!-- Toggle buttons for order categories -->
        <div id="togglecontrol" class="toggle-container">
            <button onclick="showSection('pending')" class="toggle-btn active">Pending Orders</button>
            <button onclick="showSection('accepted')" class="toggle-btn">Accepted Orders</button>
            <button onclick="showSection('history')" class="toggle-btn">Order History</button>
        </div>

        <!-- Pending Orders Section -->
        <div id="pending-section" class="order-section">
            <h2>Pending Orders</h2>
            <?php foreach ($pending_orders as $order): ?>
                <div class="book-container">
                    <div class="book-details">
                        <h3>Order ID: <?php echo $order['order_id']; ?> (Status: <?php echo $order['order_status']; ?>)</h3>
                        <p><strong>Customer:</strong> <?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>
                        <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_price_sum'], 2); ?></p>
                    </div>
                    <div class="button-container">
                        <form action="order_details.php" method="GET">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <button class="edit-btn" type="submit">View Details</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Accepted Orders Section -->
        <div id="accepted-section" class="order-section" style="display: none;">
            <h2>Accepted Orders</h2>
            <?php foreach ($accepted_orders as $order): ?>
                <div class="book-container">
                    <div class="book-details">
                        <h3>Order ID: <?php echo $order['order_id']; ?> (Status: <?php echo $order['order_status']; ?>)</h3>
                        <p><strong>Customer:</strong> <?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>
                        <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_price_sum'], 2); ?></p>
                    </div>
                    <div class="button-container">
                        <form action="order_details.php" method="GET">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <button class="edit-btn" type="submit">View Details</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Order History Section -->
        <div id="history-section" class="order-section" style="display: none;">
            <h2>Order History</h2>
            <?php foreach ($order_history as $order): ?>
                <div class="book-container">
                    <div class="book-details">
                        <h3>Order ID: <?php echo $order['order_id']; ?> (Status: <?php echo $order['order_status']; ?>)</h3>
                        <p><strong>Customer:</strong> <?php echo $order['first_name'] . ' ' . $order['last_name']; ?></p>
                        <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_price_sum'], 2); ?></p>
                    </div>
                    <div class="button-container">
                        <form action="order_details.php" method="GET">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <button class="edit-btn" type="submit">View Details</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.order-section').forEach(sec => sec.style.display = 'none');
            
            // Show the selected section
            document.getElementById(section + '-section').style.display = 'block';

            // Remove active class from all buttons
            document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));

            // Add active class to the selected button
            document.querySelector(`button[onclick="showSection('${section}')"]`).classList.add('active');
        }
    </script>
</body>
</html>