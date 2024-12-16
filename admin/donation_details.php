<?php
session_start();
include "../db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

// Check if donation ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_donations.php");
    exit;
}

$donation_id = $_GET['id'];

// Fetch donation details
$sql = "SELECT d.*, u.email AS user_email, u.first_name, u.last_name, u.contact_no, 
               c.category_name, cond.condition_name
        FROM donations d 
        JOIN user u ON d.Donor_id = u.user_id 
        JOIN categories c ON d.Category_id = c.category_id
        JOIN conditions cond ON d.condition_id = cond.condition_id
        WHERE d.Donation_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donation_id);
$stmt->execute();
$result = $stmt->get_result();
$donation = $result->fetch_assoc();

// Fetch donation images
$sql_images = "SELECT image_path FROM donation_images WHERE donation_id = ?";
$stmt_images = $conn->prepare($sql_images);
$stmt_images->bind_param("i", $donation_id);
$stmt_images->execute();
$result_images = $stmt_images->get_result();
$images = $result_images->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $update_sql = "UPDATE donations SET status = ? WHERE Donation_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $new_status = ($action == 'accept') ? 'Accepted' : 'Rejected';
    $update_stmt->bind_param("si", $new_status, $donation_id);
    
    if ($update_stmt->execute()) {
        header("Location: manage_donations.php");
        exit;
    } else {
        $error_message = "Error updating donation status.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        .nav-ribbon {
            background-color: #333;
            color: white;
            padding: 20px;
            position: fixed; /* Sticks the ribbon to the top */
            top: 0;
            left: 0;
            width: 100%; /* Make sure it covers the full width */
            z-index: 1000; /* Ensures it stays on top of content */
            display: flex; /* Allows for alignment */
            justify-content: flex-start; /* Aligns items to the left */
        }

        .nav-ribbon a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            margin-left: 20px; /* Add margin to separate the link from the left edge */
        }

        .nav-ribbon a:hover {
            background-color: #575757;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .navigation-bar {
            background-color: #333;
            padding: 10px 0;
            display: flex;
            justify-content: center;
        }
        .navigation-bar a {
            color: white;
            text-decoration: none;
            padding: 0 20px;
        }
        .main-content {
            padding: 60px;
        }
        h1 {
            color: #d9534f;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #333;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            border-radius: 3px;
            font-size: 14px;
            margin-right: 5px;
            text-decoration:none;
        }
        .btn-accept {
            background-color: #5cb85c;
            color: white;
        }
        .btn-reject, .btn-remove {
            background-color: #d9534f;
            color: white;
        }
        .btn-view {
            background-color: #5bc0de;
            color: white;
        }

        .donation-details {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .donation-images {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .donation-images img {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navigation Ribbon -->
    <nav class="nav-ribbon">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_donations.php">Back to Donations</a>
    </nav>

    <div class="main-content">
        <h1>Donation Details</h1>
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <div class="donation-details">
            <h2>Book Information</h2>
            <p><strong>Title:</strong> <?php echo $donation['Book_title']; ?></p>
            <p><strong>Author:</strong> <?php echo $donation['Author']; ?></p>
            <p><strong>Number of Copies:</strong> <?php echo $donation['No_of_copies']; ?></p>
            <p><strong>Category:</strong> <?php echo $donation['category_name']; ?></p>
            <p><strong>Condition:</strong> <?php echo $donation['condition_name']; ?></p>
            <p><strong>Status:</strong> <?php echo $donation['status']; ?></p>
            <p><strong>Request Date:</strong> <?php echo $donation['requested_time']; ?></p>

            <h2>Donor Information</h2>
            <p><strong>Name:</strong> <?php echo $donation['first_name'] . ' ' . $donation['last_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $donation['user_email']; ?></p>
            <p><strong>Contact Number:</strong> <?php echo $donation['contact_no']; ?></p>

            <h2>Book Images</h2>
            <div class="donation-images">
                <?php foreach ($images as $image): ?>
                    <img src="<?php echo $image['image_path']; ?>" alt="Donation Image">
                <?php endforeach; ?>
            </div>

            <?php if ($donation['status'] == 'Pending'): ?>
                <h2>Actions</h2>
                <form method="POST">
                    <button type="submit" name="action" value="accept" class="btn btn-accept">Accept Donation</button>
                    <button type="submit" name="action" value="reject" class="btn btn-reject">Reject Donation</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>