<?php
    session_start();
    include "../db_connect.php";

    // Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../user_login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <!-- Mobile Metas -->
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Site Metas -->
        <title>Cart</title>
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

        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body>
        <?php include 'header_user.php'; ?>

        <!-- Start All Title Box -->
        <div class="all-title-box">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Cart</h2>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Shop</a></li>
                            <li class="breadcrumb-item active">Cart</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- End All Title Box -->

        <!-- Start Cart  -->
        <?php
            // Fetch cart items from the database
            $sql = "SELECT c.*, b.title, b.unit_price, bi.image_path
            FROM cart c
            JOIN books b ON c.book_id = b.title_id
            JOIN book_images bi ON c.book_id = bi.book_id
            WHERE c.user_id = ?
            GROUP BY c.book_id"; // Group by book_id to ensure one image per book

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();


            $cart_items = [];
            while ($row = $result->fetch_assoc()) {
                $cart_items[] = $row;
            }

            // If cart is empty, show a message
            if (count($cart_items) == 0) {?>
                <div class="alert alert-info" role="alert">
                Your cart is empty. Start adding some books!
                </div><?php
            } else {
                // Render the cart items dynamically
                ?>
                <div class="cart-box-main">
                    <div class="container">
                        <div class="table-main table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Images</th>
                                        <th>Product Name</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody id="cart-body">
                                    <?php foreach ($cart_items as $item) : ?>
                                    <tr data-book-id="<?php echo $item['book_id']; ?>">
                                        <td class="thumbnail-img">
                                            <a href="#"><img class="img-fluid" src="<?php echo $item['image_path']; ?>" alt="<?php echo $item['title']; ?>"></a>
                                        </td>
                                        <td class="name-pr"><a href="#"><?php echo $item['title']; ?></a></td>
                                        <td class="price-pr"><p>₹<?php echo number_format($item['unit_price'], 2); ?></p></td>
                                        <td class="quantity-box">
                                            <input type="number" size="4" value="<?php echo $item['quantity']; ?>" min="1" step="1" class="c-input-text qty text" data-book-id="<?php echo $item['book_id']; ?>">
                                        </td>
                                        <td class="total-pr"><p>₹<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></p></td>
                                        <td class="remove-pr">
                                            <a href="#" class="remove-item" data-book-id="<?php echo $item['book_id']; ?>"><i class="fas fa-times"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="col-12 d-flex shopping-box">
                                        <a href="checkout.php" class="ml-auto btn hvr-hover">Checkout</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        ?>
        <!-- End Cart -->

        <!-- jQuery and AJAX Script for Dynamic Cart -->
        <script>
            document.querySelectorAll('.qty').forEach(item => {
                item.addEventListener('change', function() {
                    let bookId = this.dataset.bookId;
                    let quantity = this.value;
                    updateCart(bookId, quantity);
                });
            });

            function updateCart(bookId, quantity) {
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "update_cart.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Update the cart totals and quantities in the DOM
                            if (document.getElementById('sub-total')) {
                                document.getElementById('sub-total').textContent = '₹' + response.subtotal;
                            }
                            if (document.getElementById('tax')) {
                                document.getElementById('tax').textContent = '₹' + response.tax;
                            }
                            if (document.getElementById('grand-total')) {
                                document.getElementById('grand-total').textContent = '₹' + response.grand_total;
                            }
                            
                            // Update the total for the individual item row
                            let itemTotalElement = document.querySelector(`tr[data-book-id="${bookId}"] .total-pr p`);
                            if (itemTotalElement) {
                                itemTotalElement.textContent = '₹' + response.item_total;
                            } else {
                                console.error(`Could not find total element for book ID ${bookId}`);
                            }
                        } else {
                            // Display error message
                            alert(response.message);
                            // Reset the quantity input to its previous value
                            let quantityInput = document.querySelector(`input[data-book-id="${bookId}"]`);
                            if (quantityInput) {
                                quantityInput.value = response.available_quantity || 1;
                            }
                        }
                    }
                };
                xhr.send("book_id=" + encodeURIComponent(bookId) + "&quantity=" + encodeURIComponent(quantity));
            }

            document.querySelectorAll('.remove-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    let bookId = this.dataset.bookId;
                    removeFromCart(bookId);
                });
            });

            function removeFromCart(bookId) {
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "remove_from_cart.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Remove the item row from the DOM
                            document.querySelector(`tr[data-book-id='${bookId}']`).remove();

                            // Update the cart totals in the DOM
                            document.getElementById('sub-total').textContent = '₹' + response.subtotal;
                            document.getElementById('tax').textContent = '₹' + response.tax;
                            document.getElementById('grand-total').textContent = '₹' + response.grand_total;
                        }
                    }
                };
                xhr.send("book_id=" + bookId);
            }
        </script>

        <?php include '../footer.php'; ?>
    </body>

</html>