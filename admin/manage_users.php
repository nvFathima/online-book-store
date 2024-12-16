<?php
session_start();
include '../db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch all users
$stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, contact_no, user_type FROM user");
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
    </style>
</head>
<body>
    <!-- Navigation Ribbon -->
    <nav class="nav-ribbon">
        <a href="admin_dashboard.php">Dashboard</a>
    </nav>

    <div class="main-content">
        <h1>Manage Users</h1>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Contact No</th>
                <th>Actions</th>
            </tr>
            <?php while($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['contact_no']); ?></td>
                <td>
                    <form method="POST" action="remove_user.php" onsubmit="return confirmDelete()">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <button type="submit">Remove User</button>
                    </form><br>
                    <a href="user_details.php?user_id=<?php echo $user['user_id']; ?>">
                        <button type="button">View Details</button>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
    function confirmDelete() {
        return confirm("Are you sure you want to remove this user?");
    }
    </script>
</body>
</html>
