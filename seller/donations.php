<?php
session_start();
include '../db_connect.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: ../user_login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Handle form submission for requesting a donation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_donation'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $condition_id = $_POST['condition_id']; 
    $no_of_copies = $_POST['no_of_copies'];

    // Fetch category_id
    $stmt1 = $conn->prepare("SELECT category_id FROM categories WHERE category_name = ?");
    $stmt1->bind_param("s", $category);
    $stmt1->execute();
    $result2 = $stmt1->get_result();
    $cat = $result2->fetch_assoc();
    $category_id = $cat['category_id'];
    $stmt1->close();

    // Validate form data
    if (empty($title) || empty($author) || empty($category)) {
        $error = "Please fill in all required fields";
    } elseif (!isset($error)) {
        // Insert donation request into the database
        $stmt = $conn->prepare("INSERT INTO donations (Donor_id, Book_title, Author, Category_id, condition_id, No_of_copies, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("issiii", $seller_id, $title, $author, $category_id, $condition_id, $no_of_copies);
        if ($stmt->execute()) {
            // Store success message in session
            $_SESSION['success'] = "Donation request submitted successfully.";

            // Fetch the donation_id for the newly added donation
            $donation_id = $stmt->insert_id;

            // Insert images if provided
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $uploadDir = '../uploads/donations/';

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
                }

                foreach ($_FILES['images']['name'] as $key => $fileName) {
                    $fileTmpPath = $_FILES['images']['tmp_name'][$key];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (in_array($fileExtension, $allowed)) {
                        $newFileName = uniqid() . '.' . $fileExtension;
                        $uploadFilePath = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $uploadFilePath)) {
                            // Insert image path into the donation_images table
                            $stmt2 = $conn->prepare("INSERT INTO donation_images (donation_id, image_path) VALUES (?, ?)");
                            $stmt2->bind_param("is", $donation_id, $uploadFilePath);
                            $stmt2->execute();
                            $stmt2->close();
                        } else {
                            $error = "Error uploading image $fileName";
                            break; // Exit loop if there is an error
                        }
                    } else {
                        $error = "Invalid file type for image $fileName. Only JPG, JPEG, PNG, and GIF are allowed.";
                        break; // Exit loop if there is an invalid file type
                    }
                }
            }

            // Redirect to avoid form resubmission on page reload
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;

        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch existing donation requests for the seller
$result = $conn->query("SELECT * FROM donations WHERE Donor_id = $seller_id");
$donations = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations - BookHub</title>
    <link rel="stylesheet" href="../css/don_styles.css">
    <script>
        function showSection(section) {
            document.getElementById('donations-list').style.display = 'none';
            document.getElementById('request-form').style.display = 'none';

            // Toggle active class on navigation links
            const navLinks = document.querySelectorAll('.navigation-bar a');
            navLinks.forEach(link => link.classList.remove('active'));

            // Show the requested section
            document.getElementById(section).style.display = 'block';

            // Highlight active section in the navigation
            const activeLink = document.querySelector(`.navigation-bar a[onclick="showSection('${section}')"]`);
            activeLink.classList.add('active');
        }

        // Show the donations list by default on page load
        window.onload = function() {
            showSection('donations-list');
        };

        function cancelDonation(donationId) {
            if (confirm('Are you sure you want to remove this book?')) {
                // Send a request to the server to delete the book
                window.location.href = 'cancel_donation.php?id=' + donationId;
            }
        }
    </script>
</head>
<body>
    <div class="top-header">
        <button onclick="location.href='seller_dashboard.php'" class="logout-btn">Dashboard</button>
        <h1>Donations</h1>
    </div>

    <nav class="navigation-bar">
        <a href="#" onclick="showSection('request-form')">Request Form</a>
        <a href="#" onclick="showSection('donations-list')">My Donations</a>
    </nav>

    <!-- Request Form Section -->
    <div id="request-form" class="form-container" style="display:none;">
        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <p class="success-message"><?php echo $_SESSION['success']; ?></p>
            <?php unset($_SESSION['success']); // Clear the message after displaying it ?>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Book Title</label>
                <input type="text" name="title" id="title" placeholder="Book Title" required>
            </div>

            <div class="form-group">
                <label for="author">Author</label>
                <input type="text" name="author" id="author" placeholder="Author" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" placeholder="Description"></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" id="category" required>
                    <option value="">Select Category</option>
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
                <label for="images">Book Images</label>
                <input type="file" name="images[]" id="images" multiple>
            </div>

            <div class="form-group">
                <label for="condition">Condition of the Book:</label>
                <select name="condition_id" id="condition" required>
                    <option value="" disabled selected>Select condition</option>
                    <option value="301">New</option>
                    <option value="302">Almost-new</option>
                    <option value="303">Very-good</option>
                    <option value="304">Good</option>
                </select>
            </div>

            <div class="form-group">
                <label for="no_of_copies">Number of Copies:</label>
                <input type="number" id="no_of_copies" name="no_of_copies" min="1" required>
            </div>    

            <div class="form-group">
                <input type="submit" name="request_donation" value="Request Donation">
            </div>
        </form>
    </div>

    <!-- Donations List Section -->
    <div id="donations-list" class="donations-list">
        <h2>Your Donation Requests</h2>
        
        <?php if (empty($donations)): ?>
            <p class="no-donations-message">No donations made yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donations as $donation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donation['Book_title']); ?></td>
                            <td><?php echo htmlspecialchars($donation['Author']); ?></td>
                            <td><?php echo htmlspecialchars($donation['status']); ?></td>
                            <td>
                                <?php if ($donation['status'] === 'Pending'): ?>
                                    <button class="cancel-button" onclick="cancelDonation(<?php echo $donation['Donation_id']; ?>)">Cancel</button>
                                <?php else: ?>
                                    <span>N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>
