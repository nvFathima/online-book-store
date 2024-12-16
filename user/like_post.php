<?php
include "../db_connect.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $post_id = $data['post_id'];
    $user_id = $_SESSION['user_id'];

    // Check if user has already liked the post
    $check_sql = "SELECT * FROM likes WHERE post_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        // User hasn't liked the post, so add the like
        $sql = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $post_id, $user_id);
    
        if ($stmt->execute()) {
            // Get the new like count
            $count_sql = "SELECT COUNT(*) as like_count FROM likes WHERE post_id = ?";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $post_id);
            $count_stmt->execute();
            $new_like_count = $count_stmt->get_result()->fetch_assoc()['like_count'];
            $count_stmt->close();
            
            echo json_encode(['success' => true, 'action' => 'like', 'new_like_count' => $new_like_count]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    
        $stmt->close();
    } else {
        // User has already liked the post, so remove the like
        $sql = "DELETE FROM likes WHERE post_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $post_id, $user_id);
    
        if ($stmt->execute()) {
            // Get the new like count
            $count_sql = "SELECT COUNT(*) as like_count FROM likes WHERE post_id = ?";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $post_id);
            $count_stmt->execute();
            $new_like_count = $count_stmt->get_result()->fetch_assoc()['like_count'];
            $count_stmt->close();
            
            echo json_encode(['success' => true, 'action' => 'unlike', 'new_like_count' => $new_like_count]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
    }    

    $check_stmt->close();
    $conn->close();
}
?>