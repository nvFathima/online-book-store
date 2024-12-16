<?php
session_start();

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'];
    $verificationCode = $_POST['verification_code'];

    // Retrieve the verification code from the database
    $stmt = $conn->prepare("SELECT * FROM email_verifications WHERE email = ? AND verification_code = ?");
    $stmt->bind_param("ss", $email, $verificationCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Insert the new user into the users table
        $firstName = $_SESSION['first_name'];
        $lastName = $_SESSION['last_name'];
        $contactNumber = $_SESSION['contact_number'];
        $password = $_SESSION['password'];
        $userType = $_SESSION['user_type'];

        $stmt = $conn->prepare("INSERT INTO user (first_name, last_name, email, contact_no, password, user_type) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $firstName, $lastName, $email, $contactNumber, $password, $userType);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!');</script>";
            header("Location: user_login.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Invalid verification code.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify - BookHub</title>
    <link rel="stylesheet" href="css/user_login_style.css">
</head>
<body>
    <div class="login-container">
        <h1>Confirm Registration</h1>
        <form action="verify.php" method="post">
            <div class="input-group">
                <label for="verification_code">Enter Verification Code</label>
                <input type="text" id="verification_code" name="verification_code" required>
            </div>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>
