<?php
session_start();
include "../db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

// Determine which status to filter by (default: pending)
$status = isset($_GET['status']) && in_array($_GET['status'], ['Pending', 'Accepted']) ? $_GET['status'] : 'Pending';

// Fetch donation requests based on status
$sql = "SELECT d.Donation_id, d.Book_title, d.status, d.requested_time, u.email AS user_email 
        FROM donations d 
        JOIN user u ON d.Donor_id = u.user_id 
        WHERE d.status = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $status);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donations</title>
    <style>
        /* Styling (similar to the existing style you provided) */
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
            text-decoration: none;
        }
        .btn-view {
            background-color: #5bc0de;
            color: white;
        }
        /* New button styles for toggling between pending and accepted */
        .btn-toggle {
            padding: 10px 15px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
            transition: background-color 0.3s ease;
        }
        .btn-toggle.active {
            background-color: #d33b33;
        }
        .btn-toggle:hover {
            background-color: #575757;
        }
    </style>
</head>
<body>
    <!-- Navigation Ribbon -->
    <nav class="nav-ribbon">
        <a href="admin_dashboard.php">Dashboard</a>
    </nav>

    <div class="main-content">
        <h1>Manage Book Donations</h1>

        <!-- Toggle buttons for switching between Pending and Accepted Donations -->
        <div style="margin-bottom: 20px;">
            <a href="?status=Pending" class="btn-toggle <?php echo ($status === 'Pending') ? 'active' : ''; ?>">Pending Donations</a>
            <a href="?status=Accepted" class="btn-toggle <?php echo ($status === 'Accepted') ? 'active' : ''; ?>">Accepted Donations</a>
        </div>

        <table>
            <tr>
                <th>Donation ID</th>
                <th>User Email</th>
                <th>Book Title</th>
                <th>Request Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['Donation_id']; ?></td>
                        <td><?php echo $row['user_email']; ?></td>
                        <td><?php echo $row['Book_title']; ?></td>
                        <td><?php echo $row['requested_time']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td>
                            <a href="donation_details.php?id=<?php echo $row['Donation_id']; ?>" class="btn btn-view">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">
                        <?php echo $status === 'Pending' ? 'No pending donations.' : 'No accepted donations.'; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
