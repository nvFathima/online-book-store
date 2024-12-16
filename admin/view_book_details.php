<?php
    session_start();
    include '../db_connect.php';

    if (!isset($_SESSION['admin_username'])) {
        header("Location: admin_login.php");
        exit;
    }

    // Get the book ID from the URL
    $book_id = $_GET['book_id'];

    // Fetch book details
    $stmt = $conn->prepare("SELECT books.*, categories.category_name, conditions.condition_name, user.first_name, user.last_name 
                            FROM books 
                            LEFT JOIN categories ON books.category_id = categories.category_id 
                            LEFT JOIN conditions ON books.condition_id = conditions.condition_id 
                            LEFT JOIN user ON books.seller_id = user.user_id 
                            WHERE title_id = ?");
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch book images
    $stmt_images = $conn->prepare("SELECT * FROM book_images WHERE book_id = ?");
    $stmt_images->bind_param('i', $book_id);
    $stmt_images->execute();
    $images = $stmt_images->get_result();
    $stmt_images->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Book Details</title>
    <link rel="stylesheet" href="booksales_style.css">
</head>
<body>
    <!-- Top container for navigation -->
    <div class="top-container">
        <a href="admin_dashboard.php" class="btn">Dashboard</a>
        <a href="manage_booksales.php" class="btn">Back to Book Sales</a>
    </div>

    <div class="main-content">
        <h1> <?php echo htmlspecialchars($book['title']); ?></h1>

        <div class="book-details">
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($book['description']); ?></p>
            <p><strong>Original Price:</strong> <?php echo htmlspecialchars($book['original_price']); ?></p>
            <p><strong>Selling Price:</strong> <?php echo htmlspecialchars($book['unit_price']); ?></p>
            <p><strong>Condition:</strong> <?php echo htmlspecialchars($book['condition_name']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category_name']); ?></p>
            <p><strong>Seller:</strong> <?php echo htmlspecialchars($book['first_name'] . " " . $book['last_name']); ?></p>
        </div>

        <!-- Book Images Section -->
        <div class="book-images">
            <h2>Book Images:</h2>
            <?php while($image = $images->fetch_assoc()): ?>
                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Book Image" width="200">
            <?php endwhile; ?>
        </div>

        <!-- Buttons for accepting and removing the book -->
        <div class="book-actions">
        <?php if($book['status'] === 'pending'): ?>
            <form method="POST" action="accept_sale.php" onsubmit="return confirmAction('accept', '<?php echo htmlspecialchars($book['title']); ?>')">
                <input type="hidden" name="sale_id" value="<?php echo $book['title_id']; ?>">
                <button type="submit" class="btn-accept">Accept Book</button>
            </form>
            <!-- Rejection Form Trigger -->
            <form method="POST" action="remove_book.php" onsubmit="return openRejectionModal();">
                <input type="hidden" name="book_id" value="<?php echo $book['title_id']; ?>">
                <button type="button" onclick="openRejectionModal()">Reject Book</button>
            </form>
        <?php endif; ?>
            <!-- Card-style Rejection Modal -->
            <div id="rejectionModal">
                <div class="modal-card">
                    <h2>Reject Book</h2>
                    <label for="rejection_reason">Enter the reason for rejection:</label>
                    <textarea id="rejection_reason" rows="5" required></textarea>
                    <button class="btn-submit" onclick="submitRejection()">Submit</button>
                    <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </div>

            <script>
                // Open card modal for rejection
                function openRejectionModal() {
                    document.getElementById('rejectionModal').style.display = 'flex';
                }

                // Close the modal
                function closeModal() {
                    document.getElementById('rejectionModal').style.display = 'none';
                }

                // Submit rejection form with the rejection reason
                function submitRejection() {
                    const rejectionReason = document.getElementById('rejection_reason').value.trim();

                    if (rejectionReason === '') {
                        alert('Please provide a reason for rejection.');
                        return;
                    }

                    const form = document.querySelector("form[action='remove_book.php']");
                    const hiddenInput = document.createElement("input");
                    hiddenInput.type = "hidden";
                    hiddenInput.name = "rejection_reason";
                    hiddenInput.value = rejectionReason;
                    form.appendChild(hiddenInput);

                    form.submit();  // Submit the form
                }
            </script>
        </div>
    </div>

</body>
</html>
