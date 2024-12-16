<?php
session_start();
require_once('../db_connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Function to get all orders for a user
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

// Function to get ordered items for a specific order
function getOrderItems($order_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT oi.*, b.title, b.unit_price, oi.expected_delivery, oi.item_status 
                            FROM order_items oi
                            JOIN books b ON oi.book_id = b.title_id
                            WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch orders for the user
$orders = getOrders($user_id);

// Create an associative array to store items for each order
$order_items = [];
foreach ($orders as $order) {
    $order_items[$order['order_id']] = getOrderItems($order['order_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="icon" href="../images/main-ico.png" type="image/png">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/custom.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</head>
<body>
    <?php include 'header_user.php'; ?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>My Orders</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="my-account.php">Account</a></li>
                        <li class="breadcrumb-item active">My Orders</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <div class="container mt-5">
        <!-- Toggle for Active and Previous Orders -->
        <div class="btn-group mb-3" role="group">
            <button type="button" class="btn btn-outline-primary" id="active-orders-btn">All Orders</button>
            <a href="download_order_history.php" class="btn btn-outline-success ml-auto" target="_blank">Download Order History (PDF)</a>
        </div>

        <!-- Order Tables -->
        <div id="orders-container">
            <!-- Active Orders Table -->
            <div id="active-orders" class="orders-section">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Book</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Expected Delivery</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <!-- Group by Order ID -->
                            <tr>
                                <td rowspan="<?= count($order_items[$order['order_id']]) ?>"><?= htmlspecialchars($order['order_id']) ?></td>

                                <?php foreach ($order_items[$order['order_id']] as $index => $item): ?>
                                    <!-- Only the first item will show the order ID, so for other items, skip the first column -->
                                    <?php if ($index > 0): ?>
                                        <tr>
                                    <?php endif; ?>

                                    <td><?= htmlspecialchars($item['title']) ?></td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td>₹<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
                                    <td><?= htmlspecialchars($item['expected_delivery']) ?: 'Not set' ?></td>
                                    <td><?= htmlspecialchars($item['item_status']) ?></td>
                                    <td>
                                        <?php if ($item['item_status'] === 'Accepted'): ?>
                                            <form action="update_order_status.php" method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                                <input type="hidden" name="book_id" value="<?= $item['book_id'] ?>">
                                                <button type="submit" class="btn btn-success">Mark as Received</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>

                                    </tr>
                                <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Previous Orders Table -->
            
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        $('#active-orders-btn').click(function() {
            $('#active-orders').show();
            $('#previous-orders').hide();
        });
        $('#previous-orders-btn').click(function() {
            $('#active-orders').hide();
            $('#previous-orders').show();
        });
    </script>
</body>
</html>