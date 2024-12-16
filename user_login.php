<?php
session_start();

if (isset($_SESSION['password_reset_success'])) {
    $error_message = "Password reset successfully. Please log in with your new password.";
    unset($_SESSION['password_reset_success']); // Unset the session variable
}

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prevent SQL injection
    $username = $conn->real_escape_string($username);

    // Check if the user exists
    $sql = "SELECT * FROM user WHERE email = '$username' ";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $db_password = $user["password"];
        $_SESSION['user_id'] = $user["user_id"];

        if(password_verify($password,$db_password)){
        
            $_SESSION['username'] = $username;

            // Update last active time
            $updateLastActive = "UPDATE user SET last_active = NOW() WHERE user_id = ?";
            $stmt = $conn->prepare($updateLastActive);
            $stmt->bind_param("i", $user["user_id"]);
            $stmt->execute();

            if ($user['user_type'] == 'user') {
                $_SESSION['user_type'] = 'user';
                header("Location: user/customer_dashboard.php");
                exit();
            } 
            elseif ($user['user_type'] == 'seller') {
                $_SESSION['user_type'] = 'seller';
                header("Location: seller/seller_dashboard.php");
                exit();
            } 
            else {
                $error_message =  "Invalid user type";
            }
        }
        else{
            $error_message = "Incorrect password";
        }
    } 
    else {
        $error_message = "User does not exist!";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookHub - User Login</title>
    <link rel="stylesheet" href="css/user_login_style.css">

    <link rel="icon" href="images/main-ico.png" type="image/png">
</head>
<body>
    <a class="back-home" href="index.php">Home</a>

    <div class="login-container">
        <h1>Sign In</h1>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="user_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <p>Don't have an account? <a href="registration.php">Register here</a></p>
            <p>Forgot your password? <a href="#" onclick="openForgotPassword(); return false;">Reset it</a></p>

            <script>
            function openForgotPassword() {
                const width = 600;
                const height = 400;
                const left = (screen.width - width) / 2;
                const top = (screen.height - height) / 2;
                const options = `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`;
                window.open('forgot_password.php', 'ForgotPasswordWindow', options);
            }
            </script>
        </form>
    </div>
</body>
</html>
