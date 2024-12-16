<?php
session_start();

include "../db_connect.php";

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch admin from the database
    $sql = "SELECT * FROM admin WHERE user_name = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Verify the password
        if ($password ==  $admin['password']) {
            $_SESSION['admin_username'] = $username;
            
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error_message = "Invalid password";
        }
    } else {
        $error_message = "Invalid username or password";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_style.css">
    <style>
        #loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        let errorMessage = "<?php echo addslashes($error_message); ?>";
    </script>
</head>
<body>
    <div id="loading-screen">
        <div class="spinner"></div>
    </div>

    <a class="back-home" href="../index.php">Home</a>
    <div class="login-container">
        <h2>Admin Login</h2>
        <div id="error-container" class="error-message" style="display: none;"></div>
        <form method="POST" action="admin_login.php">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
    <script>
        window.onload = function() {
            setTimeout(function() {
                document.getElementById('loading-screen').style.display = 'none';
                if (errorMessage) {
                    document.getElementById('error-container').textContent = errorMessage;
                    document.getElementById('error-container').style.display = 'block';
                }
            }, 2000); // 2000 milliseconds = 2 seconds
        };
    </script>
</body>
</html>