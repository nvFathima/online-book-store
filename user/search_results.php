<?php
session_start();
include '../db_connect.php'; // Make sure this file exists and contains your database connection logic

// Get the search query from the URL
$search_query = isset($_GET['query']) ? $_GET['query'] : '';

// Prepare the SQL query
$sql = "SELECT b.*, c.category_name, cond.condition_name, u.first_name, u.last_name, 
               (SELECT image_path FROM book_images WHERE book_id = b.title_id LIMIT 1) as image_path
        FROM books b
        LEFT JOIN categories c ON b.category_id = c.category_id
        LEFT JOIN conditions cond ON b.condition_id = cond.condition_id
        LEFT JOIN user u ON b.seller_id = u.user_id
        WHERE b.title LIKE ? OR b.author LIKE ? OR c.category_name LIKE ?
        AND b.status = 'accepted'";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$search_term = "%$search_query%";
$stmt->bind_param("sss", $search_term, $search_term, $search_term);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all results
$books = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Site Metas -->
    <title>Search</title>
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
    <style>
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
    </style>

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
                    <a class="navbar-brand" href="customer_dashboard.php"><img src="../images/main-icon.png" class="logo logo-image" alt=""></a>
                </div>
                <!-- End Header Navigation -->

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="navbar-menu">
                    <ul class="nav navbar-nav ml-auto" data-in="fadeInDown" data-out="fadeOutUp">
                        <li class="nav-item active"><a class="nav-link" href="customer_dashboard.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="about_user.php">About Us</a></li>
                        <li class="dropdown">
                            <a href="community.php" class="nav-link dropdown-toggle" data-toggle="dropdown">Reader's Community</a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="nav-link dropdown-toggle arrow" data-toggle="dropdown">SHOP</a>
                            <ul class="dropdown-menu">
                                <li><a href="cart.php">Cart</a></li>
                                <li><a href="checkout.php">Checkout</a></li>
                                <li><a href="my-account.php">My Account</a></li>
                                <li><a href="wishlist.php">Wishlist</a></li>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="donate_books.php">Donate</a></li>
                        <li class="nav-item"><a class="nav-link" href="service_user.php">Our Services</a></li>
                        
                    </ul>
                </div>
                <!-- /.navbar-collapse -->

                <!-- Start Atribute Navigation -->
                <div class="attr-nav">
                    <ul>
                        <li class="search"><a href="#"><i class="fa fa-search"></i></a></li>
                    </ul>
                </div>
                <!-- End Atribute Navigation -->
            </div>
        </nav>
        <!-- End Navigation -->
    </header>
    <!-- End Main Top -->

    <div class="container-book">
        <h1>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>
            <?php if (count($books) > 0): ?>
                <div class="row">
                    <?php foreach ($books as $book): ?>
                        <div class="col-md-4">
                            
                                <div class="col-lg-6">
                                    <img src="<?php echo htmlspecialchars($book['image_path']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-image">
                                </div>
                                <div class="col-lg-6">
                                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                    <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                                    <p>Category: <?php echo htmlspecialchars($book['category_name']); ?></p>
                                    <p>Condition: <?php echo htmlspecialchars($book['condition_name']); ?></p>
                                    <p>Price: ₹<?php echo number_format($book['unit_price'], 2); ?></p>
                                    <a href="book_details.php?id=<?php echo $book['title_id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                           
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No results found for your search query.</p>
            <?php endif; ?>
    </div>

    <?php include '../footer.php'; ?>
</body>
</html>