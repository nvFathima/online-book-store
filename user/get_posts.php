<?php
include "../db_connect.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$type = $_GET['type'] ?? 'all';
$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of posts per page
$offset = ($page - 1) * $limit;

// Base query
$sql = "SELECT 
            p.post_id, 
            p.title, 
            p.content, 
            p.created_at, 
            p.image_path, 
            u.first_name, 
            u.last_name,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) as like_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count,
            EXISTS (SELECT 1 FROM likes WHERE post_id = p.post_id AND user_id = ?) as user_liked
        FROM posts p
        JOIN user u ON p.user_id = u.user_id";

// Add WHERE clause based on type
$where_clause = "";
if ($type === 'user') {
    $where_clause = " WHERE p.user_id = ?";
}

// Add ORDER BY clause based on filter
switch ($filter) {
    case 'popular':
        $order_by = " ORDER BY like_count DESC";
        break;
    case 'oldest':
        $order_by = " ORDER BY p.created_at ASC";
        break;
    case 'alphabetical':
        $order_by = " ORDER BY p.title ASC";
        break;
    default: // newest
        $order_by = " ORDER BY p.created_at DESC";
}

// Complete the query
$sql .= $where_clause . $order_by . " LIMIT ? OFFSET ?";

// Prepare and execute the statement with appropriate parameters
$stmt = $conn->prepare($sql);
if ($type === 'user') {
    $stmt->bind_param("iiii", $user_id, $user_id, $limit, $offset);
} else {
    $stmt->bind_param("iii", $user_id, $limit, $offset);
}

// Execute and get results
if (!$stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to execute query: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);

// Get total posts count for pagination
$count_sql = "SELECT COUNT(*) as total FROM posts AS p" . $where_clause;
$count_stmt = $conn->prepare($count_sql);
if ($type === 'user') {
    $count_stmt->bind_param("i", $user_id);
}
$count_stmt->execute();
$total_posts = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $limit);

// Prepare the response
$response = [
    'posts' => $posts,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'total_posts' => $total_posts
];

// Send the JSON response
header('Content-Type: application/json');
echo json_encode($response);

// Clean up
$stmt->close();
$count_stmt->close();
$conn->close();
?>