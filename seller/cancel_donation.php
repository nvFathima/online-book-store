<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: ../user_login.php");
    exit;
}

$donationId = intval($_GET['id']);
$sellerId = $_SESSION['user_id'];

// Delete the record from the donations table
$stmt3 = $conn->prepare("DELETE FROM donations WHERE Donation_id = ? AND Donor_id = ?");
$stmt3->bind_param("ii", $donationId, $sellerId);

if ($stmt3->execute()) {
    header("Location: donations.php?success=" . urlencode("Donation request cancelled successfully."));
} else {
    header("Location: donations.php?error=" . urlencode("Error cancelling the request."));
}

$stmt3->close();
$conn->close();
?>
