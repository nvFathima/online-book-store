<?php
session_start();
include '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

// Get the user ID from the URL
if (!isset($_GET['user_id'])) {
    header("Location: manage_users.php"); // Redirect if user_id is not provided
    exit;
}

$user_id = $_GET['user_id'];

// Fetch the user details from the database
$stmt = $conn->prepare("SELECT first_name, last_name, email, contact_no, user_type, created_at, last_active FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_details = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link rel="stylesheet" href="admin_dashboard_style.css">
    <link rel="stylesheet" href="user_det_style.css">
</head>

<body>
<!-- Navigation Bar -->
<nav class="nav-ribbon">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_users.php" class="manage-users-link">Manage Users</a>
    <a href="generate_user_pdf.php?user_id=<?php echo $user_id; ?>" target="_blank" class="download-btn">
        Download PDF
    </a>
</nav>

<!-- Main Content for User Details -->
<div class="user-details-container">
    <h1>User Details for <?php echo htmlspecialchars($user_details['first_name'] . ' ' . $user_details['last_name']); ?></h1>
    <table>
        <tr>
            <th>Email:</th>
            <td><?php echo htmlspecialchars($user_details['email']); ?></td>
        </tr>
        <tr>
            <th>Contact No:</th>
            <td><?php echo htmlspecialchars($user_details['contact_no']); ?></td>
        </tr>
        <tr>
            <th>User Type:</th>
            <td><?php echo htmlspecialchars($user_details['user_type']); ?></td>
        </tr>
        <tr>
            <th>Account Creation Time:</th>
            <td><?php echo htmlspecialchars($user_details['created_at']); ?></td>
        </tr>
        <tr>
            <th>Last Active Time:</th>
            <td><?php echo htmlspecialchars($user_details['last_active']); ?></td>
        </tr>
    </table>

    <?php if ($user_details['user_type'] == 'user'): ?>
        <h2>Shipping Addresses</h2>
        <table>
            <tr>
                <th>Address Line 1</th>
                <th>City</th>
                <th>State</th>
                <th>Postal Code</th>
                <th>Country</th>
            </tr>
            <?php
            $stmt = $conn->prepare("SELECT address_line_1, city, state, postal_code, country FROM shipping_addresses WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $shipping_addresses = $stmt->get_result();
            $stmt->close();

            while ($address = $shipping_addresses->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($address['address_line_1']); ?></td>
                    <td><?php echo htmlspecialchars($address['city']); ?></td>
                    <td><?php echo htmlspecialchars($address['state']); ?></td>
                    <td><?php echo htmlspecialchars($address['postal_code']); ?></td>
                    <td><?php echo htmlspecialchars($address['country']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

    <?php elseif ($user_details['user_type'] == 'seller'): ?>
        <h2>Business Addresses</h2>
        <table>
            <tr>
                <th>Address Line 1</th>
                <th>City</th>
                <th>State</th>
                <th>Postal Code</th>
                <th>Country</th>
            </tr>
            <?php
            $stmt = $conn->prepare("SELECT address_line1, address_line2, state, postcode FROM addresses WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $business_addresses = $stmt->get_result();
            $stmt->close();

            while ($address = $business_addresses->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($address['address_line1']); ?></td>
                    <td><?php echo htmlspecialchars($address['address_line2']); ?></td>
                    <td><?php echo htmlspecialchars($address['state']); ?></td>
                    <td><?php echo htmlspecialchars($address['postcode']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h2>Books Listed by Seller</h2>
        <table>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>No of Copies</th>
                <th>Price</th>
                <th>Status</th>
            </tr>
            <?php
            $stmt = $conn->prepare("SELECT title, author, No_of_copies, unit_price, status FROM books WHERE seller_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $books = $stmt->get_result();
            $stmt->close();

            while ($book = $books->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                    <td><?php echo htmlspecialchars($book['No_of_copies']); ?></td>
                    <td><?php echo htmlspecialchars($book['unit_price']); ?></td>
                    <td><?php echo htmlspecialchars($book['status']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

    <?php endif; ?>

    <!-- Display Donations for Both User and Seller -->
    <h2>Donations</h2>
    <table>
        <tr>
            <th>Book Title</th>
            <th>Author</th>
            <th>Number of Copies</th>
            <th>Status</th>
        </tr>
        <?php
        $stmt = $conn->prepare("SELECT Book_title, Author, No_of_copies, status FROM donations WHERE Donor_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $donations = $stmt->get_result();
        $stmt->close();

        while ($donation = $donations->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($donation['Book_title']); ?></td>
                <td><?php echo htmlspecialchars($donation['Author']); ?></td>
                <td><?php echo htmlspecialchars($donation['No_of_copies']); ?></td>
                <td><?php echo htmlspecialchars($donation['status']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    
    <!-- Remove User Button -->
    <form action="remove_user.php" method="POST" onsubmit="return confirm('Are you sure you want to remove this user?');">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <button type="submit" class="remove-user-btn">Remove User</button>
    </form>
</div>

</body>
</html>
