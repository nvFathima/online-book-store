<?php
include "../db_connect.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    
    // Handle both JSON and form data
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
    } else {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
    }

    // Validate input
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'error' => 'Title and content are required']);
        exit();
    }

    $image_path = null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/';
        $filename = uniqid() . '_' . $_FILES['image']['name'];
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $image_path = '../uploads/' . $filename;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload image']);
            exit();
        }
    }

    $sql = "INSERT INTO posts (user_id, title, content, image_path, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $title, $content, $image_path);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>