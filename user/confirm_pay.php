<?php
// Include the Razorpay PHP library
require('../razorpay-php/Razorpay.php');

use Razorpay\Api\Api;

// Initialize Razorpay with your key and secret
$api_key = 'your_key';
$api_secret = 'your_secret';

$api = new Api($api_key, $api_secret);

// Check if payment is successful
$success = true;
$error = null;

// Get the payment ID and the signature from the callback
$payment_id = $_POST['razorpay_payment_id'];
$razorpay_signature = $_POST['razorpay_signature'];

try {
    // Verify the payment
    $attributes = array(
        'razorpay_order_id' => $_POST['razorpay_order_id'],
        'razorpay_payment_id' => $payment_id,
        'razorpay_signature' => $razorpay_signature
    );
    $api->utility->verifyPaymentSignature($attributes);
} catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
    $success = false;
    $error = 'Razorpay Signature Verification Failed';
}

if ($success) {
    // Payment is successful, update your database or perform other actions

    // Fetch the payment details
    $payment = $api->payment->fetch($payment_id);

    // Get amount paid
    $amount_paid = $payment->amount / 100; // Convert amount from paise to rupees

    // Update the orders table with the payment status
    include '../db_connect.php';  // Include your database connection
    $myorder_id = $_GET['order_id'];
    $updateOrder = $conn->prepare("UPDATE orders SET order_status = 'Paid' WHERE order_id = ?");
    $updateOrder->bind_param("i", $myorder_id);
    $updateOrder->execute();

    // Redirect to the payment success page
    header("Location: http://localhost/MiniProject/user/payment-success.php?order_id=" . $myorder_id);
    exit;
} else {
    // Payment failed, handle accordingly
    echo "Payment Failed! Error: $error";
}
?>
