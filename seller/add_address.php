<?php
session_start();
require_once('../db_connect.php');

$user_id = $_SESSION['user_id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address_line1 = trim($_POST['address_line1']);
    $address_line2 = trim($_POST['address_line2']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    
    $stmt = $conn->prepare("INSERT INTO addresses (user_id, address_line1, address_line2, postcode, state) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $address_line1, $address_line2, $postal_code, $state);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Address added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add address. Please try again.";
    }
    $stmt->close();
}

// Redirect after processing
header('Location: myprofile.php');
exit();
?>
