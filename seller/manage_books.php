<?php
    session_start();

    // Include database connection file
    include '../db_connect.php';

    // Check if the user is logged in and is a seller
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
        header("Location: ../user_login.php");
        exit;
    }

    // Fetch the seller's ID from the session
    $seller_id = $_SESSION['user_id'];

    // Handle form submission for adding a book
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_book'])) {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $description = trim($_POST['description']);
        $price = trim($_POST['price']);
        $category = trim($_POST['category']);
        $condition = trim($_POST['condition']);
        $copies = trim($_POST['copies']);

        // Fetch category ID
        $stmt1 = $conn->prepare("SELECT category_id FROM categories WHERE category_name = ?");
        $stmt1->bind_param("s", $category);
        $stmt1->execute();
        $result2 = $stmt1->get_result();
        $cat = $result2->fetch_assoc();
        $category_id = $cat['category_id'];
        $stmt1->close();

        // Fetch condition ID
        if ($condition === "Used") {
            $condition = trim($_POST['age']);
        }

        $stmt2 = $conn->prepare("SELECT condition_id FROM conditions WHERE condition_name = ?");
        $stmt2->bind_param("s", $condition);
        $stmt2->execute();
        $result3 = $stmt2->get_result();
        $con = $result3->fetch_assoc();
        $condition_id = $con['condition_id'];
        $stmt2->close();

        // Calculate selling cost
        $selling_cost = $price; // Default to original price
        $total = $price;
        if ($condition !== "New") {
            $price_multiplier = [
                "Almost-new" => 0.9,
                "Very-good" => 0.75,
                "Good" => 0.5
            ];
            $selling_cost = $price * $price_multiplier[$condition];
        }
        if ($copies > 1 && $condition === "New") {
            $total = $selling_cost * $copies;
        }

        // Validate and Insert book data into the database
        if (!empty($title) && !empty($author) && !empty($price) && !empty($category) && !empty($condition) && !empty($copies)) {
            $stmt3 = $conn->prepare("INSERT INTO books (seller_id, title, author, description, original_price, category_id, condition_id, total_price,unit_price, No_of_copies) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt3->bind_param("isssdiiddi", $seller_id, $title, $author, $description, $price, $category_id, $condition_id, $total, $selling_cost, $copies);
            
            if ($stmt3->execute()) {
                $book_id = $conn->insert_id;

                // Handle image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $uploadDir = '../uploads/images/';
                    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                    foreach ($_FILES['images']['name'] as $key => $fileName) {
                        $fileTmpPath = $_FILES['images']['tmp_name'][$key];
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        if (in_array($fileExtension, $allowed)) {
                            $newFileName = uniqid() . '.' . $fileExtension;
                            $uploadFilePath = $uploadDir . $newFileName;
                            if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                                $stmt4 = $conn->prepare("INSERT INTO book_images (book_id, image_path) VALUES (?, ?)");
                                $stmt4->bind_param("is", $book_id, $uploadFilePath);
                                $stmt4->execute();
                                $stmt4->close();
                            }
                        }
                    }
                }

                $_SESSION['book_added'] = true;
                header("Location: manage_books.php");
                exit;
            } else {
                $error = "Error: " . $stmt3->error;
            }
            $stmt3->close();
        } else {
            $error = "Please fill in all required fields";
        }
    }

    // Fetch new rejection count for the seller
    $stmt = $conn->prepare("SELECT COUNT(*) AS new_rejection_count FROM rejection_messages WHERE seller_id = ? AND is_read = 0");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $new_rejection_count = $row['new_rejection_count'];
    $stmt->close();

    // Fetch books based on status (approved = accepted, pending = pending, rejected = rejected)
    $status = isset($_GET['status']) ? $_GET['status'] : 'approved';

    // Fetch books based on the view (approved, pending, rejected)
    if ($status === 'approved') {
        $status_value = 'accepted'; // Show books with 'accepted' status
        $stmt = $conn->prepare("SELECT * FROM books WHERE seller_id = ? AND status = ?");
        $stmt->bind_param("is", $seller_id, $status_value);
        $stmt->execute();
        $result = $stmt->get_result();
        $books = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } elseif ($status === 'pending') {
        $status_value = 'pending'; // Show books with 'pending' status
        $stmt = $conn->prepare("SELECT * FROM books WHERE seller_id = ? AND status = ?");
        $stmt->bind_param("is", $seller_id, $status_value);
        $stmt->execute();
        $result = $stmt->get_result();
        $books = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } elseif ($status === 'rejected') {
        // Fetch rejected books and their rejection reasons
        $stmt = $conn->prepare("
        SELECT r.book_title, r.rejection_reason
        FROM rejection_messages r
        JOIN user u ON r.seller_id = u.user_id
        WHERE u.user_type = 'seller' AND r.seller_id = ?");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rejected_books = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        // Mark all rejection messages as read after fetching
        $stmt = $conn->prepare("UPDATE rejection_messages SET is_read = 1 WHERE seller_id = ?");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
?>

<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Manage Book Sales</title>
            <link rel="stylesheet" href="../css/books_style.css">
            <script>
                //Alert upon successful submission of new book details
                <?php if(isset($_SESSION['book_added']) && $_SESSION['book_added']): ?>
                alert("Book added successfully!");
                showBookList();
                <?php unset($_SESSION['book_added']); endif; ?>

                //Alert upon successful edit on existing book details
                <?php if(isset($_SESSION['book_updated']) && $_SESSION['book_updated']): ?>
                alert("Book details updated successfully!");
                <?php unset($_SESSION['book_updated']); endif; ?>
            </script>
            <script src="../js/books-manage.js"></script>
        </head>
    <body>
        <div class="sidebar">
            <button onclick="location.href='seller_dashboard.php'" class="dashboard-btn">Dashboard</button><br><br><br>
            <button onclick="showAddBookForm()" class="add-book-btn">Add New Book</button>
            <button onclick="showBookList()" class="book-list-btn">View Book Listings</button>
        </div>

        <div class="main-content">
            <!-- Form to Add a New Book -->
            <div id="addBookForm" style="display: none;">
                <h2>Add New Book</h2>
                <form id="addbook" action="manage_books.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author:</label>
                        <input type="text" id="author" name="author" maxlength="60" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Original Price:</label>
                        <input type="number" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="images">Upload Images:</label>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    </div>
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category" required>
                            <option value="">Select a category</option>
                            <option value="Autobiography">Autobiography</option>
                            <option value="Biography">Biography</option>
                            <option value="Business/economics">Business/economics</option>
                            <option value="Guide">Guide</option>
                            <option value="History">History</option>
                            <option value="Humor">Humor</option>
                            <option value="Journal">Journal</option>
                            <option value="Memoir">Memoir</option>
                            <option value="Philosophy">Philosophy</option>
                            <option value="Textbook">Textbook</option>
                            <option value="Science">Science</option>
                            <option value="Children's">Children's</option>
                            <option value="Classic">Classic</option>
                            <option value="Fantasy">Fantasy</option>
                            <option value="Historical fiction">Historical fiction</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="condition">Condition:</label>
                        <select id="condition" name="condition" required onchange="toggleAgeSelect()">
                            <option value="">Select condition</option>
                            <option value="New">New</option>
                            <option value="Used">Used</option>
                        </select>
                    </div>
                    <div class="form-group" id="ageSelect" style="display: none;">
                        <label for="age">Select how old your book is:</label>
                        <dl>
                            <dt>Choose 'Almost new' if:</dt>
                                <dd>- The book appears to be in perfect condition.</dd>
                                <dd>- There are no signs of wear or damage.</dd>
                                <dd>- The pages are clean and unmarked.</dd>
                            <dt>Choose 'Very good' if:</dt>
                                <dd>- The book shows minimal signs of wear.</dd>
                                <dd>- There might be slight creases or small marks on the cover.</dd>
                                <dd>- The pages are clean, but there may be very minor annotations or highlighting.</dd>
                            <dt>Choose 'Good' if:</dt>
                                <dd>- The book shows general signs of wear.</dd>
                                <dd>- The cover may have creases, scuffs, or small tears.</dd>
                                <dd>- Pages may have light annotations or highlighting.</dd>
                        </dl>
                        <select id="age" name="age">
                            <option value="">Select one</option>
                            <option value="Almost-new">Almost new</option>
                            <option value="Very-good">Very good</option>
                            <option value="Good">Good</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="copies">Number of Copies:</label>
                        <input type="number" id="copies" name="copies" min="1" required>
                    </div>
                    <!-- Agreement to Terms and Conditions -->
                    <div class="form-group">
                        <fieldset>
                            <legend class="label">Agreement</legend>
                            <div class="agreement-frame">
                                <iframe src="sales_agree.php" width="100%" height="150" frameborder="0"></iframe>
                                <div class="checkbox-container">
                                    <label for="agreement">I agree to the Terms of Service and Privacy Policy</label>
                                    <input type="checkbox" id="agreement" name="agreement" required>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <button type="submit" name="add_book">Add Book</button>
                </form>
            </div>

            <!-- Display Existing Book Listings -->
            <!-- Toggle buttons container -->
            <div id="togglecontrol" class="toggle-container">
                <button onclick="location.href='manage_books.php?status=approved'" 
                        class="toggle-btn <?php echo $status === 'approved' ? 'active' : ''; ?>">Approved Books</button>
                <button onclick="location.href='manage_books.php?status=pending'" 
                        class="toggle-btn <?php echo $status === 'pending' ? 'active' : ''; ?>">Pending Books</button>

                <!-- Rejected Books Button -->
                <button onclick="location.href='manage_books.php?status=rejected'" 
                        class="toggle-btn <?php echo $status === 'rejected' ? 'active' : ''; ?>">
                    Rejected Books
                    <?php if ($new_rejection_count > 0): ?>
                        <span class="badge"><?php echo $new_rejection_count; ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <div id="bookList">
                <!-- Show approved books -->
                <?php if ($status === 'approved'): ?>
                    <h2>Approved Book Listings</h2>
                    <?php if (!empty($books)): ?>
                        <?php foreach ($books as $book): ?>
                            <div class="book-container">
                                <div class="book-details">
                                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                    <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($book['description']); ?></p>
                                    <p><strong>Price:</strong> ₹<?php echo htmlspecialchars($book['unit_price']); ?></p>
                                </div>
                                <div class="button-container">
                                    <button class="edit-btn" onclick="location.href='edit_book.php?id=<?php echo $book['title_id']; ?>'">Edit Details</button>
                                    <button class="remove-btn" onclick="removeBook(<?php echo $book['title_id']; ?>)">Remove</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No approved books listed yet.</p>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Show pending books -->
                <?php if ($status === 'pending'): ?>
                    <h2>Pending Book Listings</h2>
                    <?php if (!empty($books)): ?>
                        <?php foreach ($books as $book): ?>
                            <div class="book-container">
                                <div class="book-details">
                                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                    <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($book['description']); ?></p>
                                    <p><strong>Price:</strong> ₹<?php echo htmlspecialchars($book['unit_price']); ?></p>
                                </div>
                                <div class="button-container">
                                    <button class="edit-btn" onclick="location.href='edit_book.php?id=<?php echo $book['title_id']; ?>'">Edit Details</button>
                                    <button class="remove-btn" onclick="removeBook(<?php echo $book['title_id']; ?>)">Remove</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No pending books listed yet.</p>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Show rejected books -->
                <?php if ($status === 'rejected'): ?>
                    <h2>Rejected Book Listings</h2>
                    <?php if (!empty($rejected_books)): ?>
                        <?php foreach ($rejected_books as $book): ?>
                            <div class="book-container">
                                <div class="book-details">
                                    <h3><?php echo htmlspecialchars($book['book_title']); ?></h3>
                                    <p><strong>Reason for Rejection:</strong> <?php echo htmlspecialchars($book['rejection_reason']); ?></p>
                                </div>
                                <div class="button-container">
                                    <a href="contact_us.php" class="contact-link">Have a concern? Contact us</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No rejected books listed yet.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>
