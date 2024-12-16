<?php

session_start();

require 'phpMailer/src/Exception.php';
require 'phpMailer/src/PHPMailer.php';
require 'phpMailer/src/SMTP.php';
//require 'vendor/autoload.php'; // Path to PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $contactNumber = $_POST['contact_number'];
    $pass_to_user = $_POST['password'];
    $password = password_hash($_POST['password'], PASSWORD_ARGON2ID);
    $userType = $_POST['user_type'];

    // Check if the email already exists
    $sql = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "Email already registered. Please use a different email.";
    } else {
        // Generate a random verification code
        $verificationCode = rand(100000, 999999);

        // Insert the verification code into the email_verifications table
        $sql = "INSERT INTO email_verifications (email, verification_code) VALUES ('$email', '$verificationCode')";
        $conn->query($sql);

        // Send the verification code via email
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'optimist7825@gmail.com'; // SMTP username
            $mail->Password = 'rucwiesbzmnarosu'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('optimist7825@gmail.com');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification Code';
            $mail->Body    = 'Dear Customer,<br>Thank You for registering on our website. 
            Please enter the below given code into the textbox provided on the site to confirm your registration. 
            <br><br>Your verification code is: ' . $verificationCode . "<br>Your Password: " . $pass_to_user;

            $mail->send();
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['contact_number'] = $contactNumber;
            $_SESSION['password'] = $password;
            $_SESSION['user_type'] = $userType;

            header("Location: verify.php");
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

$conn->close();
?>