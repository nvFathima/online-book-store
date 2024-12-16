<?php
    require '../phpspreadsheet/vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    include "../db_connect.php";

    if (isset($_GET['report'])) {
        $reportType = $_GET['report'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        switch ($reportType) {
            // USERS REPORTS
            case 'all_users':
                $sql = "SELECT user_id, first_name, last_name, email, contact_no, created_at FROM user";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'User ID');
                $sheet->setCellValue('B1', 'First Name');
                $sheet->setCellValue('C1', 'Last Name');
                $sheet->setCellValue('D1', 'Email');
                $sheet->setCellValue('E1', 'Contact No');
                $sheet->setCellValue('F1', 'Created At');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['user_id']);
                    $sheet->setCellValue('B' . $row, $data['first_name']);
                    $sheet->setCellValue('C' . $row, $data['last_name']);
                    $sheet->setCellValue('D' . $row, $data['email']);
                    $sheet->setCellValue('E' . $row, $data['contact_no']);
                    $sheet->setCellValue('F' . $row, $data['created_at']);
                    $row++;
                }
                $filename = "all_users_report.xlsx";
                break;

            case 'sellers':
                $sql = "SELECT user_id, first_name, last_name, email, contact_no, created_at FROM user WHERE user_type = 'seller'";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'User ID');
                $sheet->setCellValue('B1', 'First Name');
                $sheet->setCellValue('C1', 'Last Name');
                $sheet->setCellValue('D1', 'Email');
                $sheet->setCellValue('E1', 'Contact No');
                $sheet->setCellValue('F1', 'Created At');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['user_id']);
                    $sheet->setCellValue('B' . $row, $data['first_name']);
                    $sheet->setCellValue('C' . $row, $data['last_name']);
                    $sheet->setCellValue('D' . $row, $data['email']);
                    $sheet->setCellValue('E' . $row, $data['contact_no']);
                    $sheet->setCellValue('F' . $row, $data['created_at']);
                    $row++;
                }
                $filename = "sellers_report.xlsx";
                break;

            case 'buyers':
                $sql = "SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.contact_no, u.created_at
                        FROM user u
                        INNER JOIN orders o ON u.user_id = o.user_id";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'User ID');
                $sheet->setCellValue('B1', 'First Name');
                $sheet->setCellValue('C1', 'Last Name');
                $sheet->setCellValue('D1', 'Email');
                $sheet->setCellValue('E1', 'Contact No');
                $sheet->setCellValue('F1', 'Created At');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['user_id']);
                    $sheet->setCellValue('B' . $row, $data['first_name']);
                    $sheet->setCellValue('C' . $row, $data['last_name']);
                    $sheet->setCellValue('D' . $row, $data['email']);
                    $sheet->setCellValue('E' . $row, $data['contact_no']);
                    $sheet->setCellValue('F' . $row, $data['created_at']);
                    $row++;
                }
                $filename = "buyers_report.xlsx";
                break;

            // BOOKS REPORTS
            case 'book_listings':
                $sql = "SELECT title_id, seller_id, title, author, unit_price, no_of_copies, status FROM books";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'Book ID');
                $sheet->setCellValue('B1', 'Seller ID');
                $sheet->setCellValue('C1', 'Title');
                $sheet->setCellValue('D1', 'Author');
                $sheet->setCellValue('E1', 'Unit Price');
                $sheet->setCellValue('F1', 'No of Copies');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['title_id']);
                    $sheet->setCellValue('B' . $row, $data['seller_id']);
                    $sheet->setCellValue('C' . $row, $data['title']);
                    $sheet->setCellValue('D' . $row, $data['author']);
                    $sheet->setCellValue('E' . $row, $data['unit_price']);
                    $sheet->setCellValue('F' . $row, $data['no_of_copies']);
                    $row++;
                }
                $filename = "book_listings_report.xlsx";
                break;

            case 'book_sales':
                $sql = "SELECT o.order_id, o.user_id, oi.book_id, oi.quantity, oi.unit_price, oi.total_price, o.created_at
                        FROM orders o
                        INNER JOIN order_items oi ON o.order_id = oi.order_id";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'Order ID');
                $sheet->setCellValue('B1', 'User ID');
                $sheet->setCellValue('C1', 'Book ID');
                $sheet->setCellValue('D1', 'Quantity');
                $sheet->setCellValue('E1', 'Unit Price');
                $sheet->setCellValue('F1', 'Total Price');
                $sheet->setCellValue('G1', 'Created At');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['order_id']);
                    $sheet->setCellValue('B' . $row, $data['user_id']);
                    $sheet->setCellValue('C' . $row, $data['book_id']);
                    $sheet->setCellValue('D' . $row, $data['quantity']);
                    $sheet->setCellValue('E' . $row, $data['unit_price']);
                    $sheet->setCellValue('F' . $row, $data['total_price']);
                    $sheet->setCellValue('G' . $row, $data['created_at']);
                    $row++;
                }
                $filename = "book_sales_report.xlsx";
                break;

            case 'rejected_books':
                $sql = "SELECT message_id, seller_id, book_title, rejection_reason, rejected_at FROM rejection_messages";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'Book ID');
                $sheet->setCellValue('B1', 'Seller ID');
                $sheet->setCellValue('C1', 'Title');
                $sheet->setCellValue('D1', 'Reason for Rejection');
                $sheet->setCellValue('E1', 'Rejected at');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['message_id']);
                    $sheet->setCellValue('B' . $row, $data['seller_id']);
                    $sheet->setCellValue('C' . $row, $data['book_title']);
                    $sheet->setCellValue('D' . $row, $data['rejection_reason']);
                    $sheet->setCellValue('E' . $row, $data['rejected_at']);
                    $row++;
                }
                $filename = "rejected_books_report.xlsx";
                break;

            // DONATIONS REPORTS
            case 'all_donations':
                $sql = "SELECT donation_id, donor_id, book_title, no_of_copies, status, requested_time FROM donations";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'Donation ID');
                $sheet->setCellValue('B1', 'Donor ID');
                $sheet->setCellValue('C1', 'Title of the Book');
                $sheet->setCellValue('D1', 'No of Copies');
                $sheet->setCellValue('E1', 'Status');
                $sheet->setCellValue('F1', 'Requested time');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['donation_id']);
                    $sheet->setCellValue('B' . $row, $data['donor_id']);
                    $sheet->setCellValue('C' . $row, $data['book_title']);
                    $sheet->setCellValue('D' . $row, $data['no_of_copies']);
                    $sheet->setCellValue('E' . $row, $data['status']);
                    $sheet->setCellValue('F' . $row, $data['requested_time']);
                    $row++;
                }
                $filename = "all_donations_report.xlsx";
                break;

            case 'pending_donations':
                $sql = "SELECT donation_id, donor_id, book_title, no_of_copies, status, requested_time FROM donations WHERE status = 'pending'";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'Donation ID');
                $sheet->setCellValue('B1', 'Donor ID');
                $sheet->setCellValue('C1', 'Title of the Book');
                $sheet->setCellValue('D1', 'No of Copies');
                $sheet->setCellValue('E1', 'Status');
                $sheet->setCellValue('F1', 'Requested time');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['donation_id']);
                    $sheet->setCellValue('B' . $row, $data['donor_id']);
                    $sheet->setCellValue('C' . $row, $data['book_title']);
                    $sheet->setCellValue('D' . $row, $data['no_of_copies']);
                    $sheet->setCellValue('E' . $row, $data['status']);
                    $sheet->setCellValue('F' . $row, $data['requested_time']);
                    $row++;
                }
                $filename = "pending_donations_report.xlsx";
                break;

            // COMMUNITY REPORTS
            case 'posts':
                $sql = "SELECT post_id, user_id, title, content, created_at FROM posts";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'Post ID');
                $sheet->setCellValue('B1', 'User ID');
                $sheet->setCellValue('C1', 'Title');
                $sheet->setCellValue('D1', 'Content');
                $sheet->setCellValue('E1', 'Created at');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['post_id']);
                    $sheet->setCellValue('B' . $row, $data['user_id']);
                    $sheet->setCellValue('C' . $row, $data['title']);
                    $sheet->setCellValue('D' . $row, $data['content']);
                    $sheet->setCellValue('E' . $row, $data['created_at']);
                    $row++;
                }
                $filename = "posts_report.xlsx";
                break;

            case 'comments':
                $sql = "SELECT comment_id, post_id, user_id, content, created_at FROM comments";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'Comment ID');
                $sheet->setCellValue('B1', 'Post ID');
                $sheet->setCellValue('C1', 'User ID');
                $sheet->setCellValue('D1', 'Content');
                $sheet->setCellValue('E1', 'Created at');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['comment_id']);
                    $sheet->setCellValue('B' . $row, $data['post_id']);
                    $sheet->setCellValue('C' . $row, $data['user_id']);
                    $sheet->setCellValue('D' . $row, $data['content']);
                    $sheet->setCellValue('E' . $row, $data['created_at']);
                    $row++;
                }
                $filename = "comments_report.xlsx";
                break;

            case 'likes':
                $sql = "SELECT like_id, post_id, user_id, created_at FROM likes";
                $result = $conn->query($sql);
                $sheet->setCellValue('A1', 'Like ID');
                $sheet->setCellValue('B1', 'Post ID');
                $sheet->setCellValue('C1', 'User ID');
                $sheet->setCellValue('D1', 'Created at');
                $row = 2;
                while ($data = $result->fetch_assoc()) {
                    $sheet->setCellValue('A' . $row, $data['like_id']);
                    $sheet->setCellValue('B' . $row, $data['post_id']);
                    $sheet->setCellValue('C' . $row, $data['user_id']);
                    $sheet->setCellValue('D' . $row, $data['created_at']);
                    $row++;
                }
                $filename = "likes_report.xlsx";
                break;

            default:
                echo "Invalid report type!";
                exit;
        }

        // Output to browser as an Excel file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="admin_dashboard_style.css">
</head>
<body>

    <nav class="nav-ribbon">
        <a href="admin_dashboard.php" class="back-to-dashboard">Back to Dashboard</a>   
    </nav>

    <main class="main-content">
        <h1>Generate Reports</h1>
        <p>Select the type of report you want to generate and download it as an Excel file.</p>

        <!-- Users Reports Section -->
        <h2>Users Reports</h2>
        <div class="report-buttons">
            <button onclick="location.href='report_gen.php?report=all_users'" class="report-btn">
                <i class="fas fa-users"></i> All Users
            </button>
            <button onclick="location.href='report_gen.php?report=sellers'" class="report-btn">
                <i class="fas fa-user-tie"></i> Sellers
            </button>
            <button onclick="location.href='report_gen.php?report=buyers'" class="report-btn">
                <i class="fas fa-shopping-cart"></i> Buyers
            </button>
        </div>

        <!-- Books Reports Section -->
        <h2>Books Reports</h2>
        <div class="report-buttons">
            <button onclick="location.href='report_gen.php?report=book_listings'" class="report-btn">
                <i class="fas fa-book"></i> Book Listings
            </button>
            <button onclick="location.href='report_gen.php?report=book_sales'" class="report-btn">
                <i class="fas fa-chart-line"></i> Book Sales
            </button>
            <button onclick="location.href='report_gen.php?report=rejected_books'" class="report-btn">
                <i class="fas fa-ban"></i> Rejected Books
            </button>
        </div>

        <!-- Donations Reports Section -->
        <h2>Donations Reports</h2>
        <div class="report-buttons">
            <button onclick="location.href='report_gen.php?report=all_donations'" class="report-btn">
                <i class="fas fa-donate"></i> All Donations
            </button>
            <button onclick="location.href='report_gen.php?report=pending_donations'" class="report-btn">
                <i class="fas fa-clock"></i> Pending Donations
            </button>
        </div>

        <!-- Community Reports Section -->
        <h2>Community Reports</h2>
        <div class="report-buttons">
            <button onclick="location.href='report_gen.php?report=posts'" class="report-btn">
                <i class="fas fa-comments"></i> Posts
            </button>
            <button onclick="location.href='report_gen.php?report=comments'" class="report-btn">
                <i class="fas fa-comment-dots"></i> Comments
            </button>
            <button onclick="location.href='report_gen.php?report=likes'" class="report-btn">
                <i class="fas fa-thumbs-up"></i> Likes
            </button>
        </div>

    </main>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f4f4f4;
            color: #333;
        }

        /* Top Navigation Bar */
        .nav-ribbon {
            background-color: #333;
            color: white;
            padding: 15px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-ribbon a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            margin-left: 20px;
        }

        .nav-ribbon a:hover {
            background-color: #575757;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        /* Main Content Layout */
        .main-content {
            text-align: left;
            margin-top: 80px;
            padding: 20px;
        }

        h1 {
            text-align:center;
            color: #333;
            margin-bottom: 20px;
            font-size: 35px;
        }

        p {
            text-align:center;
            font-size: 16px;
            margin-bottom: 30px;
            color: #555;
        }

        /* Report Buttons Layout */
        .report-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            max-width: 1000px;
            margin: 20px auto;
        }

        .report-btn {
            background-color: #d33b33;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
            flex: 1 1 250px;
            max-width: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px; /* Space between icon and text */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .report-btn i {
            font-size: 22px; /* Adjust icon size */
        }

        .report-btn:hover {
            background-color: #e60000;
            transform: translateY(-5px);
        }

        /* Responsive design */
        @media (max-width: 600px) {
            .nav-ribbon {
                padding: 10px;
                flex-direction: column;
                text-align: center;
            }
            
            .report-btn {
                flex: 1 1 100%;
                max-width: none;
            }
        }
    </style>
</body>
</html>
