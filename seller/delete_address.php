<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: ../user_login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

if (isset($_POST['address_id'])) {
    $address_id = $_POST['address_id'];

    // Delete the address
    $stmt = $conn->prepare("DELETE FROM addresses WHERE address_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $seller_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Address deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete the address. Please try again.";
    }

    $stmt->close();

} else {
    $_SESSION['error'] = "No address selected.";

}

$conn->close();

// Use JavaScript to redirect and maintain the session
echo "<script>window.location.href = 'myprofile.php';</script>";
exit;

?>
