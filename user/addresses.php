<?php
session_start();
require_once('../db_connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Function to get all addresses for a user
function getAddresses($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM shipping_addresses WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch all addresses for the user
$addresses = getAddresses($user_id);
$address_count = count($addresses);

// Check if 'is_active' column exists
$is_active_exists = !empty($addresses) && array_key_exists('is_active', $addresses[0]);

// Retrieve any error or success messages
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Addresses</title>
    <!-- Site Icons -->
    <link rel="icon" href="../images/main-ico.png" type="image/png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <!-- Site CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="../css/responsive.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/custom.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header_user.php'; ?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Addresses</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="my-account.php">Account</a></li>
                        <li class="breadcrumb-item active">Addresses</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <div class="container mt-5">
        <h2>Manage Your Addresses</h2>
        
        <!-- Messages Section -->
        <div id="message-container">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
        </div>

        <!-- Add Address Section -->
        <div class="mt-4">
            <h4>Add New Address</h4>
            <form id="addAddressForm" method="POST" action="add_address.php">
                <div class="form-group">
                    <label>Address Line 1</label>
                    <input type="text" class="form-control" name="address_line1" required>
                </div>
                <div class="form-group">
                    <label>Address Line 2</label>
                    <input type="text" class="form-control" name="address_line2">
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" class="form-control" name="city" required>
                </div>
                <div class="form-group">
                    <label>State</label>
                    <input type="text" class="form-control" name="state" required>
                </div>
                <div class="form-group">
                    <label>Postal Code</label>
                    <input type="text" class="form-control" name="postal_code" required>
                </div>
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" class="form-control" name="country" required>
                </div>
                <button type="submit" class="btn btn-primary" id="addAddressBtn">Add Address</button>
            </form>
        </div>

        <!-- Address List Section -->
        <div class="mt-5">
            <h4>Your Addresses</h4>
            
                <?php if (empty($addresses)): ?>
                    <p>No addresses added yet.</p>
                <?php else: ?>
                    <?php foreach ($addresses as $address): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <?= htmlspecialchars($address['address_line_1']) ?>, 
                                    <?= htmlspecialchars($address['address_line_2']) ?>, 
                                    <?= htmlspecialchars($address['postal_code']) ?>, 
                                    <?= htmlspecialchars($address['state']) ?>
                                </label>
                            </div>
                            <button type="button" class="btn btn-danger delete-address" data-id="<?= htmlspecialchars($address['address_id']) ?>">Delete</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
        </div>
    </div>
    
    <?php include '../footer.php'; ?>

    <!-- Scripts Section -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Delete address functionality with confirmation
            $('.delete-address').on('click', function() {
                var addressId = $(this).data('id');
                
                // Show confirmation dialog before deletion
                var confirmDelete = confirm('Are you sure you want to delete this address?');
                
                if (confirmDelete) {
                    // If confirmed, proceed with the AJAX request
                    $.post('delete_address.php', { address_id: addressId }, function(response) {
                        location.reload();
                    });
                } else {
                    // If canceled, do nothing
                    return false;
                }
            });

            // Check address limit before submitting
            $('#addAddressForm').on('submit', function(e) {
                var addressCount = <?= $address_count ?>;
                if (addressCount >= 3) {
                    alert('You can only add up to 3 addresses.');
                    e.preventDefault();
                }
            });
        });
    </script>

</body>
</html>
