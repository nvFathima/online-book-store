<?php
session_start();
include "../db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../user_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get the book ID from the URL
if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
} else {
    // Redirect if no book ID is provided
    header("Location: customer_dashboard.php");
    exit;
}

// Fetch book details from the database based on the book ID
$sql = "SELECT b.title, b.author, b.description, b.unit_price, bi.image_path 
        FROM books b
        JOIN book_images bi ON b.title_id = bi.book_id
        WHERE b.title_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

// If no book is found, redirect to dashboard
if (!$book) {
    header("Location: customer_dashboard.php");
    exit;
}

/*if (isset($_POST['buy_now'])) {
    $_SESSION['book_id'] = $_POST['book_id']; // Pass the book ID to checkout
    header('Location: checkout.php');
}*/

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $book['title']; ?> - BookHub</title>

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/custom.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
        }
        .container-book {
            margin-top: 30px;
            padding: 20px;
            margin-bottom:30px;
            margin-left:20px;
            margin-right:20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .img-fluid {
            max-height: 500px;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        .img-fluid:hover {
            transform: scale(1.05);
        }
        h2 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }
        p {
            font-size: 16px;
            margin-bottom: 10px;
            color: #555;
        }
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            margin-right: 10px;
            margin-top: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success:hover {
            background-color: #28a745;
        }
        .btn-warning:hover {
            background-color: #ffc107;
        }
        .wishlist-btn i {
            margin-right: 5px;
        }
        .btn-warning {
            background-color: #f1c40f;
            color: #fff;
        }
        @media (max-width: 768px) {
            .img-fluid {
                max-height: 300px;
            }
            h2 {
                font-size: 24px;
            }
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include 'header_user.php'; ?>

    <div class="container-book">
        <div class="row">
            <div class="col-lg-6">
                <!-- Book Image -->
                <img src="<?php echo $book['image_path']; ?>" class="img-fluid" alt="<?php echo $book['title']; ?>">
            </div>
            <div class="col-lg-6">
                <!-- Book Details -->
                <h2><?php echo $book['title']; ?></h2>
                <p><strong>Author:</strong> <?php echo $book['author']; ?></p>
                <p><strong>Price:</strong> ₹<?php echo $book['unit_price']; ?></p>
                <p><strong>Description:</strong> <?php echo $book['description']; ?></p>

                <!-- Add to Cart, Buy Now, and Wishlist Buttons -->
                <form id="add-to-cart-form" method="POST" action="add_to_cart.php">
                    <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                    <button type="button" id="add-to-cart-btn" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <button type="submit" name="buy_now" class="btn btn-success">
                        <i class="fas fa-bolt"></i> Buy Now
                    </button>
                </form>

                <script src="../js/book-details.js"></script>
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

                <form id="wishlistForm" action="wishlist_process.php" method="POST" class="mt-3">
                    <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                    <button type="submit" class="btn btn-warning wishlist-btn">
                        <i class="far fa-heart"></i> Add to Wishlist
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
