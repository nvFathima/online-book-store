<?php
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpMailer/src/Exception.php';
require '../phpMailer/src/PHPMailer.php';
require '../phpMailer/src/SMTP.php';

$errorMSG = "";

// NAME
if (empty($_POST["name"])) {
    $errorMSG = "Name is required ";
} else {
    $name = $_POST["name"];
}

// EMAIL
if (empty($_POST["email"])) {
    $errorMSG .= "Email is required ";
} else {
    $email = $_POST["email"];
}

// Subject
if (empty($_POST["msg_subject"])) {
    $errorMSG .= "Subject is required ";
} else {
    $subject = $_POST["msg_subject"];
}

// MESSAGE
if (empty($_POST["message"])) {
    $errorMSG .= "Message is required ";
} else {
    $message = $_POST["message"];
}

if (empty($errorMSG)) {
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
        $mail->setFrom($email, $name);   // Sender's email
        $mail->addAddress('optimist7825@gmail.com', 'BookHub - Support'); // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Message Received: $subject";
        $mail->Body    = "
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Message:</strong> $message</p>
        ";

        $mail->send();
        echo "success";
    } catch (Exception $e) {
        echo "Something went wrong. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo $errorMSG;
}
?>