<?php
    session_start();

    include "db_connect.php";

    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $sql = "SELECT user_id FROM user WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            echo 'exists';
        } 
        else {
            echo 'available';
        }

        $stmt->close();
    }
    
    $conn->close();
?>