<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.php");
    exit;
}

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Remove user
    $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: manage_users.php");
exit;
?>
