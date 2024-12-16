<?php
    session_start();
    require '../db_connect.php';

    // Fetch wishlist items for the current user
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT w.*, b.title_id, b.title, b.author, b.unit_price, b.No_of_copies, b.status, bi.image_path
            FROM wishlist w
            JOIN books b ON w.book_id = b.title_id
            LEFT JOIN book_images bi ON b.title_id = bi.book_id
            WHERE w.user_id = ? AND b.status = 'accepted'
            GROUP BY b.title_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $wishlist_items = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Site Metas -->
    <title>Wishlist</title>
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
                    <h2>Wishlist</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Shop</a></li>
                        <li class="breadcrumb-item active">Wishlist</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Wishlist  -->
    <div class="wishlist-box-main">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div id="message-container"></div>
                    <?php if (empty($wishlist_items)): ?>
                        <div class="alert alert-info" role="alert">
                            Your wishlist is empty. Start adding some books!
                        </div>
                    <?php else: ?>
                        <div class="table-main table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th>Unit Price</th>
                                        <th>Availability</th>
                                        <th>Add to Cart</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($wishlist_items as $item): ?>
                                        <tr>
                                            <td class="thumbnail-img">
                                                <a href="book_details.php?id=<?php echo $item['title_id']; ?>">
                                                    <img class="img-fluid" src="<?php echo htmlspecialchars($item['image_path'] ?? 'path/to/default/image.jpg'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" />
                                                </a>
                                            </td>
                                            <td class="name-pr">
                                                <a href="book_details.php?id=<?php echo $item['title_id']; ?>">
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                </a>
                                            </td>
                                            <td class="author-pr">
                                                <?php echo htmlspecialchars($item['author']); ?>
                                            </td>
                                            <td class="price-pr">
                                                <p>$ <?php echo number_format($item['unit_price'], 2); ?></p>
                                            </td>
                                            <td class="quantity-box">
                                                <?php echo $item['No_of_copies'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                            </td>
                                            <td class="add-pr">
                                                <?php if ($item['No_of_copies'] > 0): ?>
                                                    <button class="btn hvr-hover add-to-cart" data-book-id="<?php echo $item['title_id']; ?>">Add to Cart</button>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary" disabled>Out of Stock</button>
                                                <?php endif; ?>
                                            </td>
                                            <td class="remove-pr">
                                                <a href="remove_from_wishlist.php?id=<?php echo $item['title_id']; ?>" class="remove-from-wishlist">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- End Wishlist -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.add-to-cart').click(function() {
            var bookId = $(this).data('book-id');
            var button = $(this);

            $.ajax({
                url: 'add_to_cart.php',
                type: 'POST',
                data: { book_id: bookId },
                success: function(response) {
                    $('#message-container').html('<div class="alert alert-success">' + response + '</div>');
                    button.prop('disabled', true).text('Added to Cart');
                },
                error: function() {
                    $('#message-container').html('<div class="alert alert-danger">Error adding item to cart. Please try again.</div>');
                }
            });
        });

        $('.remove-from-wishlist').click(function(e) {
            e.preventDefault();
            var link = $(this);
            var url = link.attr('href');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    link.closest('tr').remove();
                    $('#message-container').html('<div class="alert alert-success">Item removed from wishlist.</div>');
                    if ($('tbody tr').length === 0) {
                        $('.table-main').html('<div class="alert alert-info">Your wishlist is empty. Start adding some books!</div>');
                    }
                },
                error: function() {
                    $('#message-container').html('<div class="alert alert-danger">Error removing item from wishlist. Please try again.</div>');
                }
            });
        });
    });
    </script>

    <?php include '../footer.php'; ?>
</body>
</html>