<?php
    session_start();
    include '../db_connect.php';

    if (!isset($_SESSION['admin_username'])) {
        header("Location: admin_login.php");
        exit;
    }

    // Determine which status to filter by (default: pending)
    $status = isset($_GET['status']) && in_array($_GET['status'], ['pending', 'accepted']) ? $_GET['status'] : 'pending';

    // Fetch books based on status (pending or approved)
    $stmt = $conn->prepare("SELECT * FROM books WHERE status = ?");
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $sales = $stmt->get_result();
    $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Book Sales</title>
    <link rel="stylesheet" href="admin_dashboard_style.css">
    <style>
        body {
            padding: 20px;
            color: #333;
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

        /* Button for toggling between pending and approved books */
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

        .main-content {
            margin-left: 20px;
            padding: 20px;
            margin-top: 40px; /* Ensures the content is not hidden by the fixed ribbon */
        }
        h1 {
            color: #d33b33;
            font-size: 28px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f4f4f4;
        }
        tr:hover {
            background-color: #eaeaea;
        }
        button {
            background-color: #d33b33;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #a32e2a;
        }
        .btn-view {
            background-color: #007bff;
        }

        .btn-view:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function confirmAction(action, title) {
            return confirm(`Are you sure you want to ${action} the book titled "${title}"?`);
        }
    </script>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="nav-ribbon">
        <a href="admin_dashboard.php">Dashboard</a>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Manage Book Sales</h1>

        <!-- Buttons to toggle between Pending and Approved books -->
        <div style="margin-bottom: 20px;">
            <a href="?status=pending" class="btn-toggle <?php echo ($status === 'pending') ? 'active' : ''; ?>">Pending Books</a>
            <a href="?status=accepted" class="btn-toggle <?php echo ($status === 'accepted') ? 'active' : ''; ?>">Approved Books</a>
        </div>

        <table>
            <tr>
                <th>Book Title</th>
                <th>Seller</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
            <?php if ($sales->num_rows > 0): // Check if there are any books to display ?>
                <?php while($sale = $sales->fetch_assoc()): ?>
                    <?php
                    $user = $sale['seller_id'];
                    $stmt2 = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
                    $stmt2->bind_param('i', $user);
                    $stmt2->execute();
                    $sellers = $stmt2->get_result();
                    $seller = $sellers->fetch_assoc();
                    $seller_name = $seller['first_name'];
                    $stmt2->close();
                    ?>
                <tr>
                    <td><?php echo htmlspecialchars($sale['title']); ?></td>
                    <td><?php echo htmlspecialchars($seller_name); ?></td>
                    <td><?php echo htmlspecialchars($sale['original_price']); ?></td>
                    <td>
                        <form method="GET" action="view_book_details.php">
                            <input type="hidden" name="book_id" value="<?php echo $sale['title_id']; ?>">
                            <button type="submit" class="btn-view">View Details</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Message if no books are found -->
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">
                        <?php echo $status === 'pending' ? 'No pending approvals.' : 'No approved books.'; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
