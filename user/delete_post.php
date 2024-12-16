<?php
include "../db_connect.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Check if post_id is provided
if (!isset($_POST['post_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Post ID not provided']);
    exit;
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

// Fetch the post to ensure the user is the owner
$sql = "SELECT * FROM posts WHERE post_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'You are not authorized to delete this post']);
    exit;
}

// Delete the post
$delete_sql = "DELETE FROM posts WHERE post_id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $post_id);

if ($delete_stmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error deleting post: ' . $delete_stmt->error]);
}

$stmt->close();
$delete_stmt->close();
$conn->close();
?>