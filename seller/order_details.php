<?php
session_start();
// Connect to the database
include('../db_connect.php');

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $seller_id = $_SESSION['user_id']; // Current seller's ID

    // Fetch order and customer details from the database
    $sql_order = "
    SELECT 
        o.order_id, o.order_status, o.created_at, o.total_amount,
        u.first_name, u.last_name, u.contact_no, u.email,
        sa.address_line_1, sa.address_line_2, sa.city, sa.state, sa.postal_code, sa.country,
        oi.expected_delivery  -- Fetch from order_items, not orders
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN user u ON o.user_id = u.user_id
        JOIN shipping_addresses sa ON o.shipping_address_id = sa.address_id
        WHERE o.order_id = ?
    ";

    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param('i', $order_id);
    $stmt_order->execute();
    $result_order = $stmt_order->get_result();
    $order_details = $result_order->fetch_assoc();

    // Fetch order items (books) from the database specific to this seller
    $sql_items = "
        SELECT 
            b.title_id, b.title, oi.quantity, oi.unit_price, oi.item_status
        FROM order_items oi
        JOIN books b ON oi.book_id = b.title_id
        WHERE oi.order_id = ? AND oi.seller_id = ?
    ";

    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param('ii', $order_id, $seller_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['accept_order'])) {
            // Update the item_status for this seller's items to 'Accepted'
            $update_sql = "UPDATE order_items SET item_status = 'Accepted' WHERE order_id = ? AND seller_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('ii', $order_id, $seller_id);
            $update_stmt->execute();

            // Check if all items in the order have been accepted by all sellers
            $check_sql = "
                SELECT COUNT(*) as pending_items
                FROM order_items
                WHERE order_id = ? AND item_status != 'Accepted'
            ";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('i', $order_id);
            $check_stmt->execute();
            $result_check = $check_stmt->get_result();
            $pending_items = $result_check->fetch_assoc()['pending_items'];

            // If no items are pending, update the overall order status to 'Accepted'
            if ($pending_items == 0) {
                $update_order_sql = "UPDATE orders SET order_status = 'Accepted' WHERE order_id = ?";
                $update_order_stmt = $conn->prepare($update_order_sql);
                $update_order_stmt->bind_param('i', $order_id);
                $update_order_stmt->execute();
            }

            // Refresh order details
            $stmt_order->execute();
            $result_order = $stmt_order->get_result();
            $order_details = $result_order->fetch_assoc();

            echo "<p class='success-message'>Order accepted successfully!</p>";
        } elseif (isset($_POST['update_delivery'])) {
            // Update expected delivery date in the database
            $expected_delivery = $_POST['expected_delivery'];
            $update_sql = "UPDATE order_items SET expected_delivery = ? WHERE order_id = ? AND seller_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('sii', $expected_delivery, $order_id, $seller_id);
            $update_stmt->execute();

            // Refresh order details
            $stmt_order->execute();
            $result_order = $stmt_order->get_result();
            $order_details = $result_order->fetch_assoc();

            echo "<p class='success-message'>Expected delivery date updated!</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - BookHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem 0;
            text-align: center;
        }
        .nav {
            background-color: #34495e;
            padding: 0.5rem 0;
        }
        .nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }
        .nav ul li {
            margin: 0 1rem;
        }
        .nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .nav ul li a:hover {
            color: #3498db;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            color: #3498db;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-top: 20px;
        }
        p {
            margin: 10px 0;
        }
        .books-list {
            padding-left: 20px;
        }
        .books-list li {
            margin-bottom: 10px;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="date"], button {
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #2980b9;
        }
        .success-message {
            background-color: #2ecc71;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>BookHub Seller Portal</h1>
    </header>
    <nav class="nav">
        <ul>
            <li><a href="seller_dashboard.php">Seller Dashboard</a></li>
            <li><a href="manage_orders.php">Manage Orders</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Order Details (ID: <?php echo $order_details['order_id']; ?>)</h1>

        <h2>Customer Information</h2>
        <p><strong>Name:</strong> <?php echo $order_details['first_name'] . ' ' . $order_details['last_name']; ?></p>
        <p><strong>Contact:</strong> <?php echo $order_details['contact_no']; ?></p>
        <p><strong>Email:</strong> <?php echo $order_details['email']; ?></p>

        <h2>Shipping Address</h2>
        <p><?php echo $order_details['address_line_1'] . ', ' . $order_details['city'] . ', ' . $order_details['state'] . ', ' . $order_details['postal_code'] . ', ' . $order_details['country']; ?></p>

        <h2>Books Ordered</h2>
        <ul class="books-list">
            <?php
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            while ($item = $result_items->fetch_assoc()) {
                echo "<li>" . $item['title'] . " (Quantity: " . $item['quantity'] . ", Price: ₹" . number_format($item['unit_price'], 2) . ")</li>";
            }
            ?>
        </ul>

        <h2>Expected Delivery</h2>
        <p id="current-delivery">Current Expected Delivery: <?php echo $order_details['expected_delivery'] ?: 'Not set'; ?></p>

        <form method="POST" id="update-delivery-form">
            <label for="expected_delivery">Set Expected Delivery Date:</label>
            <input type="date" id="expected_delivery" name="expected_delivery" required>
            <button type="submit" name="update_delivery">Update Delivery Date</button>
        </form>

        <h2>Order Status</h2>
        <p><strong>Current Status:</strong> <span id="current-status"><?php echo $order_details['order_status']; ?></span></p>
        <?php if ($order_details['order_status'] == 'Paid'): ?>
            <form method="POST" id="accept-order-form">
                <button type="submit" name="accept_order" id="accept-order-btn" disabled>Accept Order</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('update-delivery-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('update_delivery', '1');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('current-delivery').textContent = 'Current Expected Delivery: ' + formData.get('expected_delivery');
                const successMessage = document.createElement('p');
                successMessage.className = 'success-message';
                successMessage.textContent = 'Expected delivery date updated!';
                this.after(successMessage);
                setTimeout(() => successMessage.remove(), 3000);

                // Enable the "Accept Order" button
                const acceptOrderBtn = document.getElementById('accept-order-btn');
                if (acceptOrderBtn) {
                    acceptOrderBtn.disabled = false;
                }
            });
        });

        // Add event listener for the "Accept Order" form
        const acceptOrderForm = document.getElementById('accept-order-form');
        if (acceptOrderForm) {
            acceptOrderForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('accept_order', '1');

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('current-status').textContent = 'Accepted';
                    const successMessage = document.createElement('p');
                    successMessage.className = 'success-message';
                    successMessage.textContent = 'Order accepted successfully!';
                    this.after(successMessage);
                    setTimeout(() => successMessage.remove(), 3000);

                    // Remove the "Accept Order" button after acceptance
                    this.remove();
                });
            });
        }
    </script>
</body>
</html>

<?php
// Close the prepared statements and database connection
$stmt_order->close();
$stmt_items->close();
$conn->close();
?>