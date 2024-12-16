<?php
    session_start();
    require '../db_connect.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../user_login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $cartItems = [];
    $directPurchaseItem = null;

    // Check if it's a direct purchase from "Buy Now"
    if (isset($_SESSION['book_id'])) {
        $book_id = $_SESSION['book_id'];
        // Fetch book details
        $bookSql = "SELECT title_id as book_id, title, unit_price, No_of_copies FROM books WHERE title_id = ?";
        $bookStmt = $conn->prepare($bookSql);
        $bookStmt->bind_param("i", $book_id);
        $bookStmt->execute();
        $bookResult = $bookStmt->get_result();
        $directPurchaseItem = $bookResult->fetch_assoc();
        $directPurchaseItem['quantity'] = 1; // Default to 1 for "Buy Now"
    } else {
        // Fetch cart items
        $cartSql = "SELECT c.*, b.title, b.unit_price, b.title_id as book_id, b.No_of_copies
                    FROM cart c
                    JOIN books b ON c.book_id = b.title_id
                    WHERE c.user_id = ?";
        $cartStmt = $conn->prepare($cartSql);
        $cartStmt->bind_param("i", $user_id);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();
        $cartItems = $cartResult->fetch_all(MYSQLI_ASSOC);
    }

    // Calculate total
    $total_amount = 0;
    if ($directPurchaseItem) {
        // For direct purchase, calculate based on the unit price and quantity
        $total_amount = $directPurchaseItem['unit_price'] * $directPurchaseItem['quantity'];
    } else {
        // For cart items, sum up all the items' prices and quantities
        foreach ($cartItems as $item) {
            $total_amount += $item['unit_price'] * $item['quantity'];
        }
    }

    // Handle the POST request when the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['paymentMethod'])) {
        $selected_address_id = $_POST['selected_address_id'];  
        $payment_method = $_POST['paymentMethod'];
        
        // Start a transaction
        $conn->begin_transaction();

        try {
            // Insert data into `orders` table
            $orderSql = "INSERT INTO orders (user_id, total_amount, shipping_address_id, order_status, payment_method) 
                        VALUES (?, ?, ?, 'Pending', ?)";
            $orderStmt = $conn->prepare($orderSql);
            $orderStmt->bind_param("idis", $user_id, $total_amount, $selected_address_id, $payment_method);
            $orderStmt->execute();

            $order_id = $conn->insert_id;

            // Insert data into `order_items` table
            $orderItemsSql = "INSERT INTO order_items (order_id, book_id, quantity, unit_price) 
                            VALUES (?, ?, ?, ?)";
            $orderItemsStmt = $conn->prepare($orderItemsSql);

            if ($directPurchaseItem) {
                // Get seller_id from the books table
                $seller_id_sql = "SELECT seller_id FROM books WHERE title_id = ?";
                $seller_stmt = $conn->prepare($seller_id_sql);
                $seller_stmt->bind_param("i", $directPurchaseItem['book_id']);
                $seller_stmt->execute();
                $seller_result = $seller_stmt->get_result();
                $seller = $seller_result->fetch_assoc();
                $seller_id = $seller['seller_id'];
            
                // Insert the direct purchase item into order_items
                $orderItemsSql = "INSERT INTO order_items (order_id, book_id, seller_id, quantity, unit_price) 
                                VALUES (?, ?, ?, ?, ?)";
                $orderItemsStmt = $conn->prepare($orderItemsSql);
                $orderItemsStmt->bind_param(
                    "iiidi",
                    $order_id,
                    $directPurchaseItem['book_id'],
                    $seller_id,
                    $directPurchaseItem['quantity'],
                    $directPurchaseItem['unit_price']
                );
                $orderItemsStmt->execute();
            
                // Update book inventory
                $updateBookSql = "UPDATE books SET No_of_copies = No_of_copies - ? WHERE title_id = ?";
                $updateBookStmt = $conn->prepare($updateBookSql);
                $updateBookStmt->bind_param("ii", $directPurchaseItem['quantity'], $directPurchaseItem['book_id']);
                $updateBookStmt->execute();
            } else {
                // For each cart item, insert them into order_items
                foreach ($cartItems as $item) {
                    // Get seller_id from the books table
                    $seller_id_sql = "SELECT seller_id FROM books WHERE title_id = ?";
                    $seller_stmt = $conn->prepare($seller_id_sql);
                    $seller_stmt->bind_param("i", $item['book_id']);
                    $seller_stmt->execute();
                    $seller_result = $seller_stmt->get_result();
                    $seller = $seller_result->fetch_assoc();
                    $seller_id = $seller['seller_id'];
            
                    // Insert cart item into order_items with seller_id
                    $orderItemsSql = "INSERT INTO order_items (order_id, book_id, seller_id, quantity, unit_price) 
                                    VALUES (?, ?, ?, ?, ?)";
                    $orderItemsStmt = $conn->prepare($orderItemsSql);
                    $orderItemsStmt->bind_param(
                        "iiidi",
                        $order_id,
                        $item['book_id'],
                        $seller_id,
                        $item['quantity'],
                        $item['unit_price']
                    );
                    $orderItemsStmt->execute();
            
                    // Update book inventory
                    $updateBookSql = "UPDATE books SET No_of_copies = No_of_copies - ? WHERE title_id = ?";
                    $updateBookStmt = $conn->prepare($updateBookSql);
                    $updateBookStmt->bind_param("ii", $item['quantity'], $item['book_id']);
                    $updateBookStmt->execute();
                }
            
                // Clear the user's cart after successful order placement
                $clearCartSql = "DELETE FROM cart WHERE user_id = ?";
                $clearCartStmt = $conn->prepare($clearCartSql);
                $clearCartStmt->bind_param("i", $user_id);
                $clearCartStmt->execute();
            }            

            $conn->commit();

            header("Location: process_order.php?order_id=" . $order_id . "&payAmount=" . $total_amount);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            echo "An error occurred: " . $e->getMessage();
        }
    }

    // Fetch user's shipping addresses
    $addressSql = "SELECT * FROM shipping_addresses WHERE user_id = ?";
    $addressStmt = $conn->prepare($addressSql);
    $addressStmt->bind_param("i", $user_id);
    $addressStmt->execute();
    $addressResult = $addressStmt->get_result();
    $addresses = $addressResult->fetch_all(MYSQLI_ASSOC);

    // Fetch user details
    $userSql = "SELECT first_name, last_name, email, contact_no FROM user WHERE user_id = ?";
    $userStmt = $conn->prepare($userSql);
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Site Metas -->
    <title>Checkout</title>
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .checkout-section {
            margin-bottom: 2rem;
        }
        .address-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .address-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .address-card.selected {
            border: 2px solid #007bff;
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }
        .payment-icon ul {
            padding: 0;
        }
        .payment-icon ul li {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <?php include 'header_user.php'; ?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Checkout</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Shop</a></li>
                        <li class="breadcrumb-item active">Checkout</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Checkout -->
    <div class="container mt-5">
        <form id="checkout-form" action="checkout.php" method="POST">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <!-- Order Summary Section -->
                    <div class="col-md-6">
                            <h3>Order Summary</h3>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Book</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Available</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($directPurchaseItem): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($directPurchaseItem['title']); ?></td>
                                                <td><?php echo $directPurchaseItem['quantity']; ?></td>
                                                <td>₹<?php echo number_format($directPurchaseItem['unit_price'] * $directPurchaseItem['quantity'], 2); ?></td>
                                                <td><?php echo $directPurchaseItem['No_of_copies']; ?></td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($cartItems as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td>₹<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                                                    <td><?php echo $item['No_of_copies']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">Total</th>
                                            <th>₹<?php echo number_format($total_amount, 2); ?></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    <!-- Shipping Address Section -->
                    <div class="card checkout-section">
                        <div class="card-header">
                            <h3 class="mb-0">Shipping Address</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($addresses)): ?>
                                <p>No shipping addresses found. <a href="add_address.php" class="btn btn-primary btn-sm">Add a new address</a></p>
                            <?php else: ?>
                                <div class="address-list">
                                    <?php foreach ($addresses as $address): ?>
                                        <div class="card mb-3 address-card" data-address-id="<?php echo $address['address_id']; ?>">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h5>
                                                <p class="card-text">
                                                    <?php echo $address['address_line_1']; ?><br>
                                                    <?php echo $address['address_line_2']; ?><br>
                                                    <?php echo $address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']; ?><br>
                                                    <?php echo $address['country']; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="selected_address_id" id="selected-address-id">
                            <?php endif; ?>
                            <a href="add_address.php" class="btn btn-outline-secondary mt-3">Add New Address</a>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <!-- Contact Information Section -->
                    <div class="card checkout-section">
                        <div class="card-header">
                            <h3 class="mb-0">Contact Information</h3>
                        </div>
                        <div class="card-body">
                            <p>
                                <strong>Name:</strong> <?php echo $user['first_name'] . ' ' . $user['last_name']; ?><br>
                                <strong>Email:</strong> <?php echo $user['email']; ?><br>
                                <strong>Phone:</strong> <?php echo $user['contact_no']; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Payment Method Section -->
                    <div class="card checkout-section">
                        <div class="card-header">
                            <h3 class="mb-0">Payment Method</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-block my-3">
                                <div class="custom-control custom-radio">
                                    <input id="credit" name="paymentMethod" type="radio" class="custom-control-input" checked required>
                                    <label class="custom-control-label" for="credit">Credit card</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input id="debit" name="paymentMethod" type="radio" class="custom-control-input" required>
                                    <label class="custom-control-label" for="debit">Debit card</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input id="paypal" name="paymentMethod" type="radio" class="custom-control-input" required>
                                    <label class="custom-control-label" for="paypal">PayPal</label>
                                </div>
                            </div>
                            <div class="payment-icon">
                                <ul class="list-inline">
                                    <li class="list-inline-item"><img class="img-fluid" src="../images/payment-icon/1.png" alt=""></li>
                                    <li class="list-inline-item"><img class="img-fluid" src="../images/payment-icon/2.png" alt=""></li>
                                    <li class="list-inline-item"><img class="img-fluid" src="../images/payment-icon/3.png" alt=""></li>
                                    <li class="list-inline-item"><img class="img-fluid" src="../images/payment-icon/5.png" alt=""></li>
                                    <li class="list-inline-item"><img class="img-fluid" src="../images/payment-icon/7.png" alt=""></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Proceed to Payment Button -->
                    <div class="checkout-section">
                        <button type="submit" id="payment-button" class="btn btn-primary btn-lg btn-block">Proceed to Payment</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- End Checkout -->

    <!-- External Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Select address on click
            $('.address-card').click(function() {
                $('.address-card').removeClass('selected border-primary');
                $(this).addClass('selected border-primary');
                $('#selected-address-id').val($(this).data('address-id'));
            });

            // Ensure address is selected before submitting
            $('#checkout-form').submit(function(e) {
                if (!$('#selected-address-id').val()) {
                    e.preventDefault();
                    alert('Please select a shipping address.');
                }
            });
        });
    </script>

    <?php include '../footer.php'; ?>
</body>

</html>