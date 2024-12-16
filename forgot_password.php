<?php
    session_start();
    // Load PHPMailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    $message = '';
    $message_type = '';

    // Database connection
    include "db_connect.php";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];

        // Check if the email exists in the database
        $sql = "SELECT * FROM user WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Check if there's already a non-expired reset code for this email
            $sql_check_reset = "SELECT * FROM password_reset WHERE email = ? AND expire_at > NOW()";
            $stmt = $conn->prepare($sql_check_reset);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $reset_result = $stmt->get_result();

            if ($reset_result->num_rows > 0) {
                // Reset code exists and is still valid
                $_SESSION['email'] = $email;
                echo '<script>
                        alert("A password reset code has already been sent to your email. \nPlease use the code to reset your password or try again later.");
                        window.location.href = "reset_password.php";
                    </script>';
                exit;
            } else {
                // Before generating a new code, delete any expired password reset entries for this email
                $sql_delete_expired = "DELETE FROM password_reset WHERE email = ? AND expire_at < NOW()";
                $stmt = $conn->prepare($sql_delete_expired);
                $stmt->bind_param("s", $email);
                $stmt->execute();

                // No valid reset entry, or it expired, so proceed with generating a new code
                $verification_code = rand(100000, 999999); // Generate a 6-digit numeric code

                // Store the verification code in the database (overwrite any existing entry)
                $stmt = $conn->prepare("INSERT INTO password_reset (email, verification_code, expire_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
                $stmt->bind_param("ss", $email, $verification_code);
                $stmt->execute();

                // Setup PHPMailer
                $mail = new PHPMailer(true);
                try {
                    //Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
                    $mail->SMTPAuth = true;
                    $mail->Username = 'optimist7825@gmail.com'; // Your Gmail address
                    $mail->Password = 'rucwiesbzmnarosu'; // Your Gmail app-specific password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    //Recipients
                    $mail->setFrom('optimist7825@gmail.com', 'BookHub');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Verification Code';
                    $mail->Body = "Your password reset verification code is: $verification_code";

                    $mail->send();
                    echo '<script>
                            alert("A verification code has been sent to your email. Please check your email.");
                            window.location.href = "reset_password.php?email=' . $email . '";
                        </script>';
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
        } else {
            echo '<script>
                    alert("This email is not registered.");
                </script>';
        }
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 0.5rem;
            color: #555;
        }
        input {
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 0.75rem;
            background-color: #d33b33;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #c32b2b;
        }
        .error {
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .success {
            color: #28a745;
            margin-bottom: 1rem;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Your Password</h1>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <!-- For forgot_password.php -->
        <form action="forgot_password.php" method="POST">
            <label for="email">Enter your registered email address:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Send Reset Code</button>
        </form>
</body>
</html>
