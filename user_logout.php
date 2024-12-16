<?php
session_start();

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Unset all session variables
$_SESSION = array();

// Destroy the session
if (session_destroy()) {
    // Redirect to the login page
    header("Location: user_login.php");
    exit;
} else {
    echo "Failed to destroy session";
}
?>