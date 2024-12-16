<?php
// Database connection
require('../razorpay-php/Razorpay.php');

use Razorpay\Api\Api;

include "../db_connect.php";

$api_key = 'your_key'; //Your Test Key
$api_secret = 'your_secret'; //Your Test Secret Key

// Set transaction details
$myorder_id = $_GET['order_id'];


$payAmount = $_GET['payAmount'];


$api = new Api($api_key, $api_secret);
// Create an order
$order = $api->order->create([
    "amount" => (float)($payAmount *100),
    "currency" => "INR",
    "receipt" => $myorder_id,
]);
$order_id = $order->id;

// Set your callback URL
$callback_url = "http://localhost/MiniProject/user/confirm_pay.php?order_id=" . $myorder_id;

// Include Razorpay Checkout.js library
echo '<script src="https://checkout.razorpay.com/v1/checkout.js"></script>';


// Add a script to handle the payment
echo '<script>
    function startPayment() {
        var options = {
            key: "' . $api_key . '",
            amount: ' . $order->amount . ',
            currency: "' . $order->currency . '",
            name: "BookHub",
            description: "Payment for your order",
            image: "https://cdn.razorpay.com/logos/GhRQcyean79PqE_medium.png",
            order_id: "' . $order_id . '",
            theme:
            {
                "color": "#738276"
            },
            callback_url: "' . $callback_url . '"
        };
        var rzp = new Razorpay(options);
        rzp.open();
    }

    window.onload = startPayment;
</script>';
