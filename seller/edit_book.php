<?php
session_start();

// Include database connection file
include '../db_connect.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: ../user_login.php");
    exit;
}

// Get book ID from the query string
if (!isset($_GET['id'])) {
    header("Location: manage_books.php");
    exit;
}

$book_id = $_GET['id'];

// Fetch book details
$stmt = $conn->prepare("SELECT * FROM books WHERE title_id = ? AND seller_id = ?");
$stmt->bind_param("ii", $book_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();

//fetch condition_name
$stmt2 = $conn->prepare("SELECT * FROM conditions WHERE condition_id = ?");
$stmt2->bind_param("i", $book['condition_id']);
$stmt2->execute();
$result2 = $stmt2->get_result();
$condition = $result2->fetch_assoc();
$stmt2->close();

if (!$book) {
    header("Location: manage_books.php");
    exit;
}

// Handle form submission for editing a book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_book'])) {
    $description = trim($_POST['description']);
    $copies = trim($_POST['copies']);

    // Validate input
    if (!empty($description) && !empty($copies)) {
        // Calculate selling cost
        $selling_cost = $book["original_price"];
        $total = $book["original_price"];
        if ($condition['condition_name'] !== "new") {
            $price_multiplier = [
                "Almost-new" => 0.9,
                "Very-good" => 0.75,
                "Good" => 0.5
            ];
            $selling_cost = ($book["original_price"] * $price_multiplier[$condition['condition_name']]);
        }
        if ($copies > 1) {
            $total = $selling_cost * $copies;
        }

        $stmt = $conn->prepare("UPDATE books SET description = ?, total_price = ?, unit_price = ?, No_of_copies = ? WHERE title_id = ? AND seller_id = ?");
        $stmt->bind_param("sddiii", $description, $total, $selling_cost, $copies, $book_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $_SESSION['book_updated'] = true;
            header("Location: manage_books.php");
            exit;
        } else {
            $error = "Error updating the book: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Please fill in all required fields";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - BookHub</title>
    <link rel="stylesheet" href="../css/books_style.css">
</head>
<body>
    <div class="main-content">
        <h2>Edit Book</h2>
        <?php if (isset($error)): ?>
            <div class="error">
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        <form action="edit_book.php?id=<?php echo $book_id; ?>" method="POST">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="author">Author:</label>
                <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="price">Original Price:</label>
                <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($book['original_price']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="condition">Condition:</label>
                <input type="text" id="condition" name="condition" value="<?php echo htmlspecialchars($condition['condition_name']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($book['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="copies">Number of Copies:</label>
                <input type="number" id="copies" name="copies" min="1" value="<?php echo htmlspecialchars($book['No_of_copies']); ?>" required>
            </div>
            <button type="submit" name="edit_book">Save Changes</button>
        </form>
    </div>
</body>
</html>
