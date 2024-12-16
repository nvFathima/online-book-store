<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: user_login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Check if the address_id is provided in the request
if (isset($_POST['address_id'])) {
    $address_id = $_POST['address_id'];

    // First, set all the user's addresses to inactive
    $stmt = $conn->prepare("UPDATE addresses SET is_active = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $stmt->close();

    // Now, set the selected address as active
    $stmt = $conn->prepare("UPDATE addresses SET is_active = 1 WHERE address_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $seller_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Active address updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update the active address. Please try again.";
    }

    $stmt->close();
    $conn->close();
    header("Location: myprofile.php");
    exit;
} else {
    $_SESSION['error'] = "No address selected.";
    header("Location: myprofile.php");
    exit;
}

?>
