<?php
include "db_connect.php";

// Handle AJAX Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    // Create a new post
    if ($action === 'create_post') {
        $content = $conn->real_escape_string($_POST['content']);
        $user_id = 1;  // Placeholder user_id (Use session-based logged-in user)
        
        $sql = "INSERT INTO posts (user_id, content) VALUES ('$user_id', '$content')";
        if ($conn->query($sql) === TRUE) {
            echo 'Post created successfully!';
        } else {
            echo 'Error: ' . $conn->error;
        }

    // Like a post
    } elseif ($action === 'like_post') {
        $post_id = (int) $_POST['post_id'];
        $user_id = 1;  // Placeholder user_id
        
        // Check if user has already liked the post
        $checkLike = "SELECT * FROM likes WHERE user_id = '$user_id' AND post_id = '$post_id'";
        $result = $conn->query($checkLike);
        
        if ($result->num_rows === 0) {
            $sql = "INSERT INTO likes (user_id, post_id) VALUES ('$user_id', '$post_id')";
            if ($conn->query($sql) === TRUE) {
                echo 'Post liked!';
            } else {
                echo 'Error: ' . $conn->error;
            }
        } else {
            echo 'You have already liked this post.';
        }

    // Create a comment on a post
    } elseif ($action === 'create_comment') {
        $post_id = (int) $_POST['post_id'];
        $content = $conn->real_escape_string($_POST['content']);
        $user_id = 1;  // Placeholder user_id
        
        $sql = "INSERT INTO comments (user_id, post_id, content) VALUES ('$user_id', '$post_id', '$content')";
        if ($conn->query($sql) === TRUE) {
            echo 'Comment added!';
        } else {
            echo 'Error: ' . $conn->error;
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch all posts with likes and comments
    $sql = "
        SELECT p.*, u.first_name, u.last_name,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) AS like_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) AS comment_count
        FROM posts p
        JOIN user u ON p.user_id = u.user_id
        ORDER BY p.created_at DESC
    ";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($post = $result->fetch_assoc()) {
            echo "<div class='post'>
                    <h3>{$post['first_name']} {$post['last_name']}</h3>
                    <p>{$post['content']}</p>
                    <button class='like-btn' data-post-id='{$post['post_id']}'>Like ({$post['like_count']})</button>
                    <div class='comments'>
                      <h4>Comments ({$post['comment_count']})</h4>
                      <form class='comment-form' data-post-id='{$post['post_id']}'>
                        <input type='text' class='comment-content' placeholder='Write a comment'>
                        <button type='submit'>Comment</button>
                      </form>
                    </div>
                  </div>";
        }
    } else {
        echo 'No posts available.';
    }
}

// Close the connection
$conn->close();
?>
