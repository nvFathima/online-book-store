<?php
session_start();

include "../db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetching dynamic data from the database
// Get total number of customers
$sql = "SELECT COUNT(*) AS total_customers FROM user WHERE user_type = 'user'";
$result = $conn->query($sql);
$numberOfCustomers = $result->fetch_assoc()['total_customers'];

// Get total number of sellers
$sql = "SELECT COUNT(*) AS total_sellers FROM user WHERE user_type = 'seller'";
$result = $conn->query($sql);
$numberOfSellers = $result->fetch_assoc()['total_sellers'];

// Get total number of books listed
$sql = "SELECT COUNT(*) AS total_books FROM books";
$result = $conn->query($sql);
$numberOfBooksListed = $result->fetch_assoc()['total_books'];

// Get total number of book sales
$sql = "SELECT COUNT(*) AS total_sales FROM orders";
$result = $conn->query($sql);
$totalSales = $result->fetch_assoc()['total_sales'];

// Fetch book category distribution for the pie chart
$sql = "
    SELECT c.category_name, COUNT(b.category_id) AS total_books
    FROM books b
    JOIN categories c ON b.category_id = c.category_id
    WHERE b.status = 'accepted' -- Assuming only accepted books are counted
    GROUP BY b.category_id
";
$categories = [];
$categoryCount = [];
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $categories[] = $row['category_name'];
    $categoryCount[] = $row['total_books'];
}

// Fetch monthly sales data for bar chart
$monthlySales = [];
for ($month = 1; $month <= 12; $month++) {
    $sql = "SELECT COUNT(*) AS sales FROM orders WHERE MONTH(created_at) = $month";
    $result = $conn->query($sql);
    $monthlySales[] = $result->fetch_assoc()['sales'] ?? 0;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Admin</title>
    <link rel="stylesheet" href="admin_dashboard_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- For charts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>
<body>
    <div class="sidebar">
        <button onclick="location.href='admin_logout.php'" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>

        <ul class="sidebar-menu">
            <li><a href="manage_users.php" class="<?= ($current_page == 'manage_users.php') ? 'active' : '' ?>"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="manage_booksales.php" class="<?= ($current_page == 'manage_booksales.php') ? 'active' : '' ?>"><i class="fas fa-book"></i> Book Sales</a></li>
            <li><a href="manage_donations.php" class="<?= ($current_page == 'manage_donations.php') ? 'active' : '' ?>"><i class="fas fa-hand-holding-heart"></i> Book Donations</a></li>
            <!--<li><a href="#" class="<?= ($current_page == 'manage_services.php') ? 'active' : '' ?>"><i class="fas fa-cogs"></i> Manage Services</a></li>-->
            <li><a href="report_gen.php" class="<?= ($current_page == 'report_gen.php') ? 'active' : '' ?>"><i class="fas fa-file-alt"></i> Generate Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Admin Dashboard</h1>
        <div class="overview">
            <!-- Box 1: Total Users -->
            <a href="manage_users.php" class="box downloads" style="text-decoration:none;">
                <h2>Users</h2>
                <p><?php echo $numberOfCustomers; ?></p>
            </a>
            <!-- Box 2: Total Sellers -->
            <a href="manage_users.php" class="box purchases" style="text-decoration:none;">
                <h2>Sellers</h2>
                <p><?php echo $numberOfSellers; ?></p>
            </a>
            <!-- Box 3: Total Books Listed -->
            <a href="manage_booksales.php" class="box customers" style="text-decoration:none;">
                <h2>Books Listed</h2>
                <p><?php echo $numberOfBooksListed; ?></p>
            </a>
            <!-- Box 4: Total Sales -->
            <a href="manage_booksales.php" class="box customers" style="text-decoration:none;">
                <h2>Total Sales</h2>
                <p><?php echo $totalSales; ?></p>
            </a>
        </div>

        <!-- Section for charts -->
        <div class="chart-section">

            <!-- Pie Chart: Book Categories -->
            <div class="chart-container">
                <h3>Books by Category</h3>
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <script>

        // Pie Chart for Book Categories
        var ctx2 = document.getElementById('pieChart').getContext('2d');
        var pieChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    label: 'Books by Category',
                    data: <?php echo json_encode($categoryCount); ?>,
                    backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56'],
                    hoverOffset: 4
                }]
            }
        });
    </script>
</body>
</html>