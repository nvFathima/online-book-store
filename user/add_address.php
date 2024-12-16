<?php
session_start();
require_once('../db_connect.php');

$user_id = $_SESSION['user_id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address_line1 = trim($_POST['address_line1']);
    $address_line2 = trim($_POST['address_line2']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    
    $stmt = $conn->prepare("INSERT INTO shipping_addresses (user_id, address_line_1, address_line_2, city, state, postal_code, country) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $address_line1, $address_line2, $city, $state, $postal_code, $country);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Address added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add address. Please try again.";
    }
    $stmt->close();
}

// Redirect after processing
header('Location: addresses.php');
exit();
?>
