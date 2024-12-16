<?php
require '../mpdf/vendor/autoload.php';
include '../db_connect.php'; // Include your database connection

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : (isset($_POST['order_id']) ? $_POST['order_id'] : null);

if (!$order_id) {
    echo "Invalid order.";
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Fetch order details from the database
    $orderQuery = "SELECT o.*, u.first_name, u.last_name
                   FROM orders o
                   JOIN user u ON o.user_id = u.user_id
                   WHERE o.order_id = ?";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        throw new Exception("Order not found.");
    }

    // Fetch order items
    $itemsQuery = "SELECT oi.*, b.title, b.No_of_copies
                   FROM order_items oi
                   JOIN books b ON oi.book_id = b.title_id
                   WHERE oi.order_id = ?";
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // If we've made it this far, commit the transaction
    $conn->commit();

} catch (Exception $e) {
    // An error occurred, rollback the transaction
    $conn->rollback();
    echo "An error occurred: " . $e->getMessage();
    exit;
}

// Check if the request is for PDF download
if (isset($_POST['download_pdf'])) {
    // Create new mPDF instance
    $mpdf = new \Mpdf\Mpdf();

    // Generate HTML content
    $html = '
    <style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
    }
    .container {
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    h2 {
        color: #2c3e50;
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        padding: 12px;
        border: 1px solid #dee2e6;
    }
    th {
        background-color: #f8f9fa;
        font-weight: bold;
        text-align: left;
    }
    </style>
    <div class="container">
        <h2>Thank you for your order!</h2>
        <p>Your order has been successfully processed. Below are the details of your purchase:</p>
        <table>
            <tr>
                <th>Order ID</th>
                <td>' . $order['order_id'] . '</td>
            </tr>
            <tr>
                <th>Customer Name</th>
                <td>' . $order['first_name'] . ' ' . $order['last_name'] . '</td>
            </tr>
        </table>
        <h3>Order Items</h3>
        <table>
            <tr>
                <th>Book Title</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>';

    foreach ($items as $item) {
        $html .= '
            <tr>
                <td>' . $item['title'] . '</td>
                <td>' . $item['quantity'] . '</td>
                <td>₹' . $item['unit_price'] . '</td>
                <td>₹' . $item['total_price'] . '</td>
            </tr>';
    }

    $html .= '
            <tr>
                <th colspan="3">Grand Total</th>
                <td>₹' . $order['total_amount'] . '</td>
            </tr>
        </table>
    </div>';

    // Write HTML content to PDF
    $mpdf->WriteHTML($html);

    // Output PDF
    $mpdf->Output('Invoice_Order_' . $order['order_id'] . '.pdf', 'D');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Site Metas -->
    <title>Payment Success - Order #<?php echo $order_id; ?></title>
    <meta name="keywords" content="BookHub, online book store, buy books, sell books, donate books, second-hand books">
    <meta name="description" content="BookHub is your digital bookshelf, where you can buy, sell, and donate new and used books, making reading affordable for everyone.">
    <meta name="author" content="">

    <!-- Site Icons -->
    <link rel="icon" href="../images/main-ico.png" type="image/png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <!-- Site CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="../css/responsive.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="stylesheet" href="pay-success.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Start Main Top -->
    <div class="main-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="text-slid-box">
                        <div id="offer-box" class="carouselTicker">
                            
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <div class="our-link">
                            <ul>
                                <li><a href="customer_dashboard.php">Home</a></li>
                                <li><a href="#">Our Location</a></li>
                                <li><a href="../user_logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Top -->

    <div class="container">
        <h2>Thank you for your order!</h2>
        <p>Your order has been successfully processed. Below are the details of your purchase:</p>

        <!-- Order Details -->
        <table class="table table-bordered">
            <tr>
                <th>Order ID</th>
                <td><?php echo $order['order_id']; ?></td>
            </tr>
            <tr>
                <th>Customer Name</th>
                <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
            </tr>
        </table>

        <!-- Order Items -->
        <h3>Order Items</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['title']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₹<?php echo $item['unit_price']; ?></td>
                    <td>₹<?php echo $item['total_price']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Grand Total</th>
                    <td>₹<?php echo $order['total_amount']; ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Download Invoice Button -->
        <form action="payment-success.php" method="POST">
            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
            <input type="hidden" name="download_pdf" value="1">
            <button type="submit" class="btn btn-success">Download Invoice</button>
        </form>
    </div>

    <!-- Start copyright  -->
    <div class="footer-copyright">
        <p class="footer-company">All Rights Reserved. &copy; 2024 <a href="index.php">BookHub</a></p>
    </div>
    <!-- End copyright  -->

    <a href="#" id="back-to-top" title="Back to top" style="display: none;">&uarr;</a>

    <!-- ALL JS FILES -->
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <!-- ALL PLUGINS -->
    <script src="../js/jquery.superslides.min.js"></script>
    <script src="../js/bootstrap-select.js"></script>
    <script src="../js/inewsticker.js"></script>
    <script src="../js/bootsnav.js."></script>
    <script src="../js/images-loded.min.js"></script>
    <script src="../js/isotope.min.js"></script>
    <script src="../js/owl.carousel.min.js"></script>
    <script src="../js/baguetteBox.min.js"></script>
    <script src="../js/form-validator.min.js"></script>
    <script src="../js/contact-form-script.js"></script>
    <script src="../js/custom.js"></script>
</body>
</html>