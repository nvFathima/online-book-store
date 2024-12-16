<?php
session_start();
require_once('../db_connect.php');

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['address_id'])) {
    $address_id = $_POST['address_id'];

    $stmt = $conn->prepare("DELETE FROM shipping_addresses WHERE address_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Address deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete address. Please try again.";
    }
    $stmt->close();
}
?>
