<?php
session_start();
include '../db_connect.php';
require '../mpdf/vendor/autoload.php';  // Load the mPDF library

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

// Get the user ID from the URL
if (!isset($_GET['user_id'])) {
    header("Location: manage_users.php"); // Redirect if user_id is not provided
    exit;
}

$user_id = $_GET['user_id'];

// Fetch the user details from the database
$stmt = $conn->prepare("SELECT first_name, last_name, email, contact_no, user_type, created_at, last_active FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_details = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Initialize mPDF
$mpdf = new \Mpdf\Mpdf();

// Start building the HTML content
$html = '<h1>User Details for ' . htmlspecialchars($user_details['first_name'] . ' ' . $user_details['last_name']) . '</h1>';
$html .= '<table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">';
$html .= '<tr><th>Email</th><td>' . htmlspecialchars($user_details['email']) . '</td></tr>';
$html .= '<tr><th>Contact No</th><td>' . htmlspecialchars($user_details['contact_no']) . '</td></tr>';
$html .= '<tr><th>User Type</th><td>' . htmlspecialchars($user_details['user_type']) . '</td></tr>';
$html .= '<tr><th>Account Creation Time</th><td>' . htmlspecialchars($user_details['created_at']) . '</td></tr>';
$html .= '<tr><th>Last Active Time</th><td>' . htmlspecialchars($user_details['last_active']) . '</td></tr>';
$html .= '</table>';

// If the user is a 'user', fetch shipping addresses
if ($user_details['user_type'] == 'user') {
    $html .= '<h2>Shipping Addresses</h2>';
    $stmt = $conn->prepare("SELECT address_line_1, city, state, postal_code, country FROM shipping_addresses WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $shipping_addresses = $stmt->get_result();
    $stmt->close();
    
    if ($shipping_addresses->num_rows > 0) {
        $html .= '<table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">';
        $html .= '<tr><th>Address Line 1</th><th>City</th><th>State</th><th>Postal Code</th><th>Country</th></tr>';
        while ($address = $shipping_addresses->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($address['address_line_1']) . '</td>';
            $html .= '<td>' . htmlspecialchars($address['city']) . '</td>';
            $html .= '<td>' . htmlspecialchars($address['state']) . '</td>';
            $html .= '<td>' . htmlspecialchars($address['postal_code']) . '</td>';
            $html .= '<td>' . htmlspecialchars($address['country']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    } else {
        $html .= '<p>No shipping addresses found.</p>';
    }
}

// If the user is a 'seller', fetch business addresses and books
if ($user_details['user_type'] == 'seller') {
    // Business Addresses
    $html .= '<h2>Business Addresses</h2>';
    $stmt = $conn->prepare("SELECT address_line1, address_line2, state, postcode FROM addresses WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $business_addresses = $stmt->get_result();
    $stmt->close();

    if ($business_addresses->num_rows > 0) {
        $html .= '<table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">';
        $html .= '<tr><th>Address Line 1</th><th>Address Line 2</th><th>State</th><th>Postal Code</th></tr>';
        while ($address = $business_addresses->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($address['address_line1']) . '</td>';
            $html .= '<td>' . htmlspecialchars($address['address_line2']) . '</td>';
            $html .= '<td>' . htmlspecialchars($address['state']) . '</td>';
            $html .= '<td>' . htmlspecialchars($address['postcode']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    } else {
        $html .= '<p>No business addresses found.</p>';
    }

    // Books Listed by Seller
    $html .= '<h2>Books Listed by Seller</h2>';
    $stmt = $conn->prepare("SELECT title, author, No_of_copies, unit_price, status FROM books WHERE seller_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $books = $stmt->get_result();
    $stmt->close();

    if ($books->num_rows > 0) {
        $html .= '<table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">';
        $html .= '<tr><th>Title</th><th>Author</th><th>No of Copies</th><th>Price</th><th>Status</th></tr>';
        while ($book = $books->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($book['title']) . '</td>';
            $html .= '<td>' . htmlspecialchars($book['author']) . '</td>';
            $html .= '<td>' . htmlspecialchars($book['No_of_copies']) . '</td>';
            $html .= '<td>' . htmlspecialchars($book['unit_price']) . '</td>';
            $html .= '<td>' . htmlspecialchars($book['status']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
    } else {
        $html .= '<p>No books listed.</p>';
    }
}

// Fetch and display donations
$html .= '<h2>Donations</h2>';
$stmt = $conn->prepare("SELECT Book_title, Author, No_of_copies, status FROM donations WHERE Donor_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$donations = $stmt->get_result();
$stmt->close();

if ($donations->num_rows > 0) {
    $html .= '<table border="1" cellpadding="10" cellspacing="0" style="width: 100%;">';
    $html .= '<tr><th>Book Title</th><th>Author</th><th>Number of Copies</th><th>Status</th></tr>';
    while ($donation = $donations->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($donation['Book_title']) . '</td>';
        $html .= '<td>' . htmlspecialchars($donation['Author']) . '</td>';
        $html .= '<td>' . htmlspecialchars($donation['No_of_copies']) . '</td>';
        $html .= '<td>' . htmlspecialchars($donation['status']) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
} else {
    $html .= '<p>No donations found.</p>';
}

// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF as a download
$mpdf->Output('user_details.pdf', \Mpdf\Output\Destination::DOWNLOAD);
