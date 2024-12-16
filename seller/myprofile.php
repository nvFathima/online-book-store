<?php
session_start();
include '../db_connect.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: ../user_login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Fetch seller's details
$stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch seller's addresses
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$addresses = $stmt->get_result();

// Check if there's no active address but addresses exist
$hasActiveAddress = false;
$addressesArray = [];
while ($address = $addresses->fetch_assoc()) {
    $addressesArray[] = $address;
    if ($address['is_active']) {
        $hasActiveAddress = true;
    }
}

if (!$hasActiveAddress && count($addressesArray) > 0) {
    // Set the first address as the active address
    $firstAddress = $addressesArray[0];
    $stmt = $conn->prepare("UPDATE addresses SET is_active = 1 WHERE address_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $firstAddress['address_id'], $seller_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['address_activated'] = true;
}

?>

<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>My Profile</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            padding: 20px;
        }
        .profile-button {
            background-color: #d33b33;
            border: none;
            color: white;
            margin-top: 10px;
            padding: 10px;
            width: 100%;
        }
        .profile-button:hover {
            background-color: #b02a2a;
        }
        .labels {
            font-size: 14px;
            color: #333;
        }
        .logout-btn {
            background-color: #262626;
            color: #e60000;
            border: 1px solid #e60000;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        .logout-btn:hover {
            background-color: #e60000;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container rounded bg-white mt-5 mb-5">
    <div id="message-container"></div>
        <?php
        if (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        ?>

        <div class="row">
            <div class="col-md-3 border-right">
                <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                    <button onclick="location.href='seller_dashboard.php'" class="logout-btn">Dashboard</button><br><br>
                    <span class="font-weight-bold"><?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']); ?></span>
                    <span class="text-black-50"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
            </div>
            <div class="col-md-9 border-right">
                <div class="p-3 py-5">
                    <h4 class="text-left">Profile Settings</h4>
                    <div class="row mt-2">
                        <div class="col-md-6"><label class="labels">First Name</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" readonly></div>
                        <div class="col-md-6"><label class="labels">Last Name</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" readonly></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12"><label class="labels">Mobile Number</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['contact_no']); ?>" readonly></div>
                    </div>

                    <!-- Add Address Section -->
                    <div class="mt-5">
                        <h5 class="text-left">Add Address</h5>
                        <form id="addAddressForm" method="POST" action="add_address.php">
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="labels">Address Line 1</label>
                                    <input type="text" class="form-control" name="address_line1" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="labels">Address Line 2</label>
                                    <input type="text" class="form-control" name="address_line2">
                                </div>
                                <div class="col-md-12">
                                    <label class="labels">Postcode</label>
                                    <input type="text" class="form-control" name="postcode" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="labels">State</label>
                                    <input type="text" class="form-control" name="state" required>
                                </div>
                            </div>
                            <button type="submit" class="btn profile-button">Add Address</button>
                        </form>
                    </div>

                    <!-- Address List Section -->
                    <div class="mt-5">
                        <h5 class="text-left">Your Addresses</h5>
                        <form id="addressSelectionForm" method="POST" action="set_active_address.php">
                            <?php
                            foreach ($addressesArray as $address) {
                                echo '<div class="d-flex justify-content-between align-items-center mb-3">';
                                echo '<div class="form-check">';
                                echo '<input class="form-check-input" type="radio" name="address_id" value="' . htmlspecialchars($address['address_id']) . '"' . ($address['is_active'] ? ' checked' : '') . '>';
                                echo '<label class="form-check-label">' . htmlspecialchars($address['address_line1']) . ', ' . htmlspecialchars($address['address_line2']) . ', ' . htmlspecialchars($address['postcode']) . ', ' . htmlspecialchars($address['state']) . '</label>';
                                echo '</div>';
                                echo '<button type="button" class="btn btn-danger delete-address" data-id="' . htmlspecialchars($address['address_id']) . '">Delete</button>';
                                echo '</div>';
                            }
                            
                            if (empty($addressesArray)) {
                                echo '<p>No addresses added yet.</p>';
                            }
                            ?>
                            <button type="submit" class="btn btn-primary profile-button mt-3">Update Active Address</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
        <script>
            $(document).ready(function() {
                // Delete address
                $('.delete-address').on('click', function() {
                    var addressId = $(this).data('id');
                    $.post('delete_address.php', { address_id: addressId }, function(response) {
                        location.reload();
                    });
                });

                // Check if address limit is reached
                $('#addAddressForm').on('submit', function(e) {
                    var addressCount = <?php echo $addresses->num_rows; ?>;
                    if (addressCount >= 3) {
                        alert('You can only add up to 3 addresses.');
                        e.preventDefault();
                    }
                });

                // Check if an address was just activated
                <?php if (isset($_SESSION['address_activated'])): ?>
                location.reload();
                <?php unset($_SESSION['address_activated']); endif; ?>

                // Check for messages in URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                const successMsg = urlParams.get('success');
                const errorMsg = urlParams.get('error');
                if (successMsg) {
                    
                    $('#message-container').html('<div class="alert alert-success">' + decodeURIComponent(successMsg) + '</div>');
                } else if (errorMsg) {
                    $('#message-container').html('<div class="alert alert-danger">' + decodeURIComponent(errorMsg) + '</div>');
                }

                // Remove messages after 5 seconds
                setTimeout(function() {
                    $('.alert').fadeOut('slow');
                }, 5000);
            });
        </script>

</body>
</html>
<?php
    // Close the database connection at the end of the file
    $conn->close();
?>