<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Site Metas -->
    <title>BookHub Home</title>
    <meta name="keywords" content="BookHub, online book store, buy books, sell books, donate books, second-hand books">
    <meta name="description" content="BookHub is your digital bookshelf, where you can buy, sell, and donate new and used books, making reading affordable for everyone.">
    <meta name="author" content="">

    <!-- Site Icons -->
    <link rel="icon" href="images/main-ico.png" type="image/png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Site CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="css/responsive.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/custom.css">

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
                    <div class="custom-select-box">
                        <a href="user_login.php" class="btn btn-red">Login/Register</a>
                    </div>
                    <div class="right-phone-box">
                        <div class="our-link">
                            <ul>
                                <li><a href="https://maps.app.goo.gl/xGKdxxuXUZ4cF8278" target= "_blank">Our location</a></li>
                                <li><a href="contact-us.php" target="_blank">Contact Us</a></li>
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
                    <a class="navbar-brand" href="index.php"><img src="images/main-icon.png" class="logo logo-image" alt=""></a>
                </div>
                <!-- End Header Navigation -->

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="navbar-menu">
                    <ul class="nav navbar-nav ml-auto" data-in="fadeInDown" data-out="fadeOutUp">
                        <li class="nav-item active"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="dropdown">
                            <a href="user_login.php" class="nav-link dropdown-toggle" data-toggle="dropdown">Reader's Community</a>
                        </li>
                        <!--<li class="dropdown megamenu-fw">

                        </li>-->
                        
                        <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                        <li class="nav-item"><a class="nav-link" href="service.php">Our Services</a></li>
                        
                    </ul>
                </div>
                <!-- /.navbar-collapse -->

                <!-- Start Atribute Navigation -->
                <div class="attr-nav">
                    <ul>
                        <li class="search"><a href="#"><i class="fa fa-search"></i></a></li>
                        <!--<li class="side-menu">-->
                        <li><a href="admin/admin_login.php">
                        <i class="fa fa-user-tie"></i></a></li>
                    </ul>
                </div>
                <!-- End Atribute Navigation -->
            </div>
        </nav>
        <!-- End Navigation -->
    </header>
    <!-- End Main Top -->

    <!-- Start Top Search -->
    <div class="top-search">
        <div class="container">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="text" class="form-control" placeholder="Search for books...">
                <span class="input-group-addon close-search"><i class="fa fa-times"></i></span>
            </div>
        </div>
    </div>
    <!-- End Top Search -->

    <!-- Start Slider -->
    <div id="slides-shop" class="cover-slides">
        <ul class="slides-container">
            <li class="text-left">
                <img src="images/banner-01.jpg" alt="">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="m-b-20"><strong>Welcome To <br> BookHub</strong></h1>
                            <p class="m-b-40">
                                Ever felt like books are just too expensive to buy?</br>Worry not! BookHub is here to make books affordable and accessible to everyone.
                            </p>
                            <p><a class="btn hvr-hover" href="about.php">Know More</a></p>
                        </div>
                    </div>
                </div>
            </li>
            <li class="text-center">
                <img src="images/banner-02.jpg" alt="">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="m-b-20"><strong>Bestsellers&<br>Must-Reads</strong></h1>
                            <p class="m-b-40">
                                Looking for an extensive collection of second-hand books?<br>Discover Your Next Favorite Book at Unbeatable Prices!
                            </p>
                            <p><a class="btn hvr-hover" href="user_login.php">Shop Now</a></p>
                        </div>
                    </div>
                </div>
            </li>
            <li class="text-right">
                <img src="images/banner-03.jpg" alt="">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="m-b-20"><strong>Join The <br> Community</strong></h1>
                            <p class="m-b-40">
                                Want to join a community of book lovers like you?<br>We connect readers through our vibrant Reader’s Community.
                            </p>
                            <p><a class="btn hvr-hover" href="user_login.php">Join Now</a></p>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="slides-navigation">
            <a href="#" class="next"><i class="fa fa-angle-right" aria-hidden="true"></i></a>
            <a href="#" class="prev"><i class="fa fa-angle-left" aria-hidden="true"></i></a>
        </div>
    </div>
    <!-- End Slider -->

    <?php
        include "db_connect.php";
        // Books per page limit
        $limit = 8;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Query to fetch books and one image per book
        $sql = "SELECT b.title, b.author, b.unit_price, MIN(bi.image_path) as image_path 
            FROM books b
            JOIN book_images bi ON b.title_id = bi.book_id
            WHERE b.status = 'accepted' 
            AND b.No_of_copies > 0
            GROUP BY b.title_id, b.title, b.author, b.unit_price
            LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        // Total number of books
        $total_books_sql = "SELECT COUNT(DISTINCT b.title_id) as total_books 
                    FROM books b 
                    WHERE b.status = 'accepted' 
                    AND b.No_of_copies > 0";
        $total_books_result = $conn->query($total_books_sql);
        $total_books = $total_books_result->fetch_assoc()['total_books'];
        $total_pages = ceil($total_books / $limit);
    ?>

    <!-- Start Books Section -->
    <div class="products-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title-all text-center">
                        <h1>Books for Sale</h1>
                        <p>Explore our collection of books and add them to your cart.</p>
                    </div>
                </div>
            </div>

            <!-- Book List Start -->
            <div class="row special-list">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Remove the "../" from the image path
                        $imagePath = str_replace('../', '', $row['image_path']);
                    
                        echo '
                        <div class="col-lg-3 col-md-6 special-grid">
                            <div class="products-single fix">
                                <div class="box-img-hover">
                                    <img src="' . $imagePath . '" class="img-fluid" alt="Book Image">
                                    <div class="mask-icon">
                                        <ul>
                                            <li><a href="#" data-toggle="tooltip" data-placement="right" title="Add to Wishlist"><i class="far fa-heart"></i></a></li>
                                        </ul>
                                        <a class="cart" href="user_login.php">View Details</a>
                                    </div>
                                </div>
                                <div class="why-text">
                                    <h4>' . $row['title'] . '</h4>
                                    <h5> ₹' . $row['unit_price'] . '</h5>
                                </div>
                            </div>
                        </div>
                        ';
                    }                    
                } else {
                    echo '<p>No books available at the moment.</p>';
                }
                ?>
            </div>
            <!-- Book List End -->

            <!-- Pagination Controls -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- End Books Section -->

    <!-- Start Blog  -->
    <?php
        function getTopStories($conn) {
            $query = "
                SELECT p.post_id, p.title, p.content, u.first_name, u.last_name,
                    COUNT(DISTINCT l.like_id) as likes,
                    COUNT(DISTINCT c.comment_id) as comments
                FROM posts p
                JOIN user u ON p.user_id = u.user_id
                LEFT JOIN likes l ON p.post_id = l.post_id
                LEFT JOIN comments c ON p.post_id = c.post_id
                GROUP BY p.post_id
                ORDER BY likes DESC
                LIMIT 6
            ";

            $result = $conn->query($query);
            $stories = [];
            while ($row = $result->fetch_assoc()) {
                $stories[] = [
                    'title' => $row['title'],
                    'content' => substr($row['content'], 0, 150) . '...',
                    'author' => $row['first_name'] . ' ' . $row['last_name'],
                    'likes' => $row['likes'],
                    'comments' => $row['comments']
                ];
            }
            return $stories;
        }
    ?>

    <div class="latest-blog">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title-all text-center">
                        <h1>Readers' Stories</h1>
                        <p>Share your love for reading!</p>
                    </div>
                </div>
            </div>
            <div class="row" id="stories-container">
                <?php
                    $stories = getTopStories($conn);
                    foreach (array_slice($stories, 0, 3) as $index => $story): ?>
                        <div class="col-md-6 col-lg-4 col-xl-4 story-item">
                            <div class="blog-box">
                                <!--<div class="blog-img">
                                    <img class="img-fluid" src="images/blog-img-0<?php echo $index + 1; ?>.jpg" alt="" />
                                </div>-->
                                <div class="blog-content">
                                    <div class="title-blog">
                                        <h3><?php echo htmlspecialchars($story['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($story['content']); ?></p>
                                    </div>
                                    <ul class="option-blog">
                                        <li><a href="user_login.php" data-toggle="tooltip" data-placement="right" title="Likes"><i class="far fa-heart"></i> <?php if ($story['likes'] > 0) echo $story['likes']; ?></a></li>
                                        <li><a href="user_login.php" data-toggle="tooltip" data-placement="right" title="Comments"><i class="far fa-comments"></i> <?php if ($story['comments'] > 0) echo $story['comments']; ?></a></li>
                                        <li><a href="user_login.php" data-toggle="tooltip" data-placement="right" title="Read more"><i class="fas fa-eye"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <button class="btn btn-primary mr-2" id="prev-stories" style='background-color:#d33b33;border:none'>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <button class="btn btn-primary" id="next-stories" style='background-color:#d33b33;border:none'>
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Blog  -->
    <?php $conn->close();?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let currentIndex = 0;
            const stories = <?php echo json_encode($stories); ?>;
            const totalStories = stories.length;

            // Function to update the stories
            function updateStories() {
                const $container = $('#stories-container');
                $container.empty();  // Clear out old stories

                // Display the next 3 stories
                for (let i = 0; i < 3; i++) {
                    const index = (currentIndex + i) % totalStories;
                    const story = stories[index];
                    $container.append(`
                        <div class="col-md-6 col-lg-4 col-xl-4 story-item">
                            <div class="blog-box">
                                <div class="blog-content">
                                    <div class="title-blog">
                                        <h3>${story.title}</h3>
                                        <p>${story.content}</p>
                                    </div>
                                    <ul class="option-blog">
                                        <li><a href="#" data-toggle="tooltip" data-placement="right" title="Likes">
                                            <i class="far fa-heart"></i> ${story.likes > 0 ? story.likes : ''}</a></li>
                                        <li><a href="#" data-toggle="tooltip" data-placement="right" title="Comments">
                                            <i class="far fa-comments"></i> ${story.comments > 0 ? story.comments : ''}</a></li>
                                        <li><a href="#" data-toggle="tooltip" data-placement="right" title="Read more">
                                            <i class="fas fa-eye"></i> </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    `);
                }
            }

            // Initialize by showing the first 3 stories
            updateStories();

            // Handle the "Next" button click
            $('#next-stories').click(function() {
                currentIndex = (currentIndex + 3) % totalStories;
                updateStories();  // Update the displayed stories
            });

            // Handle the "Previous" button click
            $('#prev-stories').click(function() {
                currentIndex = (currentIndex - 3 + totalStories) % totalStories;
                updateStories();  // Update the displayed stories
            });
        });
    </script>

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
                                <li><a href="about.php">About Us</a></li>
                                <li><a href="service.php">Customer Service</a></li>
                                <li><a href="terms_conditions.php" target="_blank">Terms &amp; Conditions</a></li>
                                <li><a href="privacypolicy.php" target="_blank">Privacy Policy</a></li>
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
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!-- ALL PLUGINS -->
    <script src="js/jquery.superslides.min.js"></script>
    <script src="js/bootstrap-select.js"></script>
    <script src="js/inewsticker.js"></script>
    <script src="js/bootsnav.js."></script>
    <script src="js/images-loded.min.js"></script>
    <script src="js/isotope.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/baguetteBox.min.js"></script>
    <script src="js/form-validator.min.js"></script>
    <script src="js/contact-form-script.js"></script>
    <script src="js/custom.js"></script>
</body>

</html>