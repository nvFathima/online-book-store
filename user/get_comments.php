<?php
include "../db_connect.php";
session_start();

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    $sql = "SELECT c.comment_id, c.content, c.created_at, u.first_name, u.last_name
            FROM comments c
            JOIN user u ON c.user_id = u.user_id
            WHERE c.post_id = ?
            ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($comments);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'No post ID provided']);
}
?>