<?php
session_start();

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: ../user_login.php");
    session_unset();
    exit;
}

include '../db_connect.php';

// Fetch seller's name and statistics
$id = $_SESSION['user_id'];
$sql1 = "SELECT first_name FROM user WHERE user_id = $id";
$result1 = $conn->query($sql1);
$user = $result1->fetch_assoc();
$sellerName = $user['first_name'];

// Prepare and execute the stored procedure to fetch books to be sold
$stmt = $conn->prepare("CALL GetTotalCopiesBySeller(?, @total)");
$stmt->bind_param("i", $id);
$stmt->execute();

// Fetch the result for books to be sold
$result2 = $conn->query("SELECT @total AS TotalCopies");
$row = $result2->fetch_assoc();
$numberOfBooksToBeSold = isset($row['TotalCopies']) ? $row['TotalCopies'] : 0;

// Fetch the number of pending orders for the seller
$pendingOrdersSql = "
    SELECT COUNT(DISTINCT o.order_id) AS pending_orders
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN books b ON oi.book_id = b.title_id
    WHERE b.seller_id = ? AND o.order_status = 'Paid';
";

$pendingOrdersStmt = $conn->prepare($pendingOrdersSql);
$pendingOrdersStmt->bind_param("i", $id);
$pendingOrdersStmt->execute();
$pendingOrdersResult = $pendingOrdersStmt->get_result();
$pendingOrdersRow = $pendingOrdersResult->fetch_assoc();
$numberOfPendingOrders = $pendingOrdersRow['pending_orders'];

// Output HTML with fetched data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Site Metas -->
    <title>Home - Seller</title>
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
    <link rel="stylesheet" href="../css/seller_db_style.css">
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
                                <li><a href="#">Our location</a></li>
                                <li><a href="#">Contact Us</a></li>
                                <li><a href="../user_logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Main Top -->

    <!-- Start Main Top -->
    <header class="main-header">
        <!-- Start Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light navbar-default bootsnav">
            <div class="container">
                <!-- Start Header Navigation -->
                <div class="navbar-header">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-menu" aria-controls="navbars-rs-food" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa fa-bars"></i>
                </button>
                    <a class="navbar-brand" href="seller_dashboard.php"><img src="../images/main-icon.png" class="logo logo-image" alt=""></a>
                </div>
                <!-- End Header Navigation -->

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="navbar-menu">
                    <ul class="nav navbar-nav ml-auto" data-in="fadeInDown" data-out="fadeOutUp">
                        <li class="nav-item active"><a class="nav-link" href="seller_dashboard.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="manage_books.php">My Books</a></li>
                        <li class="nav-item"><a href="manage_orders.php" class="nav-link">Manage Orders</a></li>
                        <li class="nav-item"><a class="nav-link" href="donations.php">Donate</a></li>
                        <li class="nav-item"><a class="nav-link" href="myprofile.php">My Account</a></li>
                        
                    </ul>
                </div>
                <!-- /.navbar-collapse -->  
            </div>
        </nav>
        <!-- End Navigation -->
    </header>
    <!-- End Main Top -->
     
    <div class="main-content">
        <h1 style="font-size:30px"><b>Hello<br> Welcome Back, <?php echo htmlspecialchars($sellerName); ?></b></h1>
        <p>We hope your sales are going well. Remember to keep updating your inventory to attract more customers!</p>
        <p>If you need help with any listing or face any issues, feel free to <a href='contact.php'>contact us</a>.</p>
        <div class="overview">
            <div class="box">
                <h2>Pending Orders</h2>
                <p><?php echo $numberOfPendingOrders; ?></p>
            </div>
            <div class="box">
                <h2>Books to be Sold</h2>
                <p><?php echo $numberOfBooksToBeSold; ?></p>
            </div>
            <!-- Add more overview boxes as needed -->
        </div>
    </div>

    <!-- Start Footer  -->
    <footer>
        <div class="footer-main">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="footer-widget">
                            <h4>About The BookHub</h4>
                            <p><strong>BookHub</strong> is an online community-driven platform where readers can buy,
                             sell, and donate both new and used books. We make literature affordable and accessible 
                             to all while fostering a passionate community of book lovers.</p>
                            <ul>
                                <li><a href="https://www.facebook.com/profile.php?id=100072873832858" target="_blank"><i class="fab fa-facebook" aria-hidden="true"></i></a></li>
                                <li><a href="https://www.linkedin.com/in/fathima-nv-52a3ba265" target="_blank"><i class="fab fa-linkedin" aria-hidden="true"></i></a></li>
                                <li><a href="https://pin.it/1BIm3GR7O" target="_blank"><i class="fab fa-pinterest-p" aria-hidden="true"></i></a></li>
                                <li><a href="#"><i class="fab fa-whatsapp" aria-hidden="true"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="footer-link">
                            <h4>Information</h4>
                            <ul>
                                <li><a href="about_user.php">About Us</a></li>
                                <li><a href="service_user.php">Customer Service</a></li>
                                <li><a href="../terms_conditions.php" target="_blank">Terms &amp; Conditions</a></li>
                                <li><a href="../privacypolicy.php" target="_blank">Privacy Policy</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="footer-link-contact">
                            <h4>Contact Us</h4>
                            <ul>
                                <li>
                                    <p><i class="fas fa-map-marker-alt"></i>Address: Carmel Apartments <br>Prayag Road,<br> Parappanangadi p.o, 676303 </p>
                                </li>
                                <li>
                                    <p><i class="fas fa-phone-square"></i>Phone: <a href="tel:+91 82817 48813">+91 82817 48813</a></p>
                                </li>
                                <li>
                                    <p><i class="fas fa-envelope"></i>Email: <a href="mailto:optimist7825@gmail.com">bookhub.info@gmail.com</a></p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- End Footer  -->

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

