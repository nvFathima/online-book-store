<?php
session_start();
require_once('../db_connect.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the form was submitted and if an address ID was provided
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['address_id'])) {
    $address_id = $_POST['address_id'];

    // First, set all addresses to inactive for this user
    $stmt = $conn->prepare("UPDATE shipping_addresses SET is_active = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        // Now set the selected address as active
        $stmt = $conn->prepare("UPDATE shipping_addresses SET is_active = 1 WHERE address_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Active address updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update active address.";
        }
    } else {
        $_SESSION['error'] = "Failed to update address status.";
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "No address selected to set as active.";
}

// Redirect back to the address management page
header('Location: addresses.php');
exit();
?>
