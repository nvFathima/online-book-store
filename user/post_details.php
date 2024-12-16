<?php
include "../db_connect.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: community.php");
    exit();
}

$post_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch post details with author info and counts
$sql = "SELECT p.*, u.first_name, u.last_name, 
        (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) as like_count,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) as comment_count,
        CASE WHEN EXISTS (SELECT 1 FROM likes WHERE post_id = p.post_id AND user_id = ?) THEN 1 ELSE 0 END as user_liked
        FROM posts p
        JOIN user u ON p.user_id = u.user_id
        WHERE p.post_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    header("Location: community.php");
    exit();
}

// Fetch comments for this post
$comment_sql = "SELECT c.*, u.first_name, u.last_name 
                FROM comments c
                JOIN user u ON c.user_id = u.user_id
                WHERE c.post_id = ?
                ORDER BY c.created_at DESC";

$comment_stmt = $conn->prepare($comment_sql);
$comment_stmt->bind_param("i", $post_id);
$comment_stmt->execute();
$comments = $comment_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($post['title']); ?> - BookHub Community</title>
    
    <!-- Include your existing CSS files -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="../css/custom.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header_user.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Post Details -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <p class="text-gray-600">
                        Posted by <?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?>
                        on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                    </p>
                </div>
                <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                    <button class="delete-post-btn text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i> Delete Post
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($post['image_path']): ?>
                <div class="mb-6">
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" 
                         alt="Post image" 
                         class="max-w-full rounded-lg shadow">
                </div>
            <?php endif; ?>

            <div class="prose max-w-none mb-6">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>

            <div class="flex items-center space-x-4 border-t pt-4">
                <button id="likeButton" 
                        class="flex items-center space-x-2 <?php echo $post['user_liked'] ? 'text-red-500' : 'text-gray-500'; ?>"
                        data-post-id="<?php echo $post['post_id']; ?>">
                    <i class="fas fa-heart"></i>
                    <span id="likeCount"><?php echo $post['like_count']; ?></span>
                </button>
                <span class="text-gray-500">
                    <i class="fas fa-comment"></i>
                    <span id="commentCount"><?php echo $post['comment_count']; ?></span>
                </span>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6">Comments</h2>
            
            <!-- Comment Form -->
            <form id="commentForm" class="mb-8">
                <textarea name="content" 
                          class="w-full p-3 border rounded-lg mb-2" 
                          rows="3" 
                          placeholder="Write a comment..."></textarea>
                <button type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    Post Comment
                </button>
            </form>

            <!-- Comments List -->
            <div id="commentsList">
                <?php foreach ($comments as $comment): ?>
                    <div class="border-b last:border-0 py-4">
                        <div class="flex justify-between mb-2">
                            <strong class="text-gray-800">
                                <?php echo htmlspecialchars($comment['first_name'] . ' ' . $comment['last_name']); ?>
                            </strong>
                            <span class="text-gray-500">
                                <?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?>
                            </span>
                        </div>
                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const likeButton = document.getElementById('likeButton');
            const commentForm = document.getElementById('commentForm');
            const commentsList = document.getElementById('commentsList');
            const deleteButton = document.querySelector('.delete-post-btn');
            const postId = <?php echo $post_id; ?>;

            // Handle likes
            likeButton?.addEventListener('click', function() {
                axios.post('like_post.php', { post_id: postId })
                    .then(response => {
                        if (response.data.success) {
                            const likeCount = document.getElementById('likeCount');
                            likeCount.textContent = response.data.new_like_count;
                            this.classList.toggle('text-red-500');
                            this.classList.toggle('text-gray-500');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error updating like. Please try again.');
                    });
            });

            // Handle comment submission
            commentForm?.addEventListener('submit', function(e) {
                e.preventDefault();
                const content = this.content.value.trim();
                
                if (!content) return;

                axios.post('add_comment.php', {
                    post_id: postId,
                    content: content
                })
                .then(response => {
                    if (response.data.success) {
                        location.reload(); // Refresh to show new comment
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error posting comment. Please try again.');
                });
            });

            // Handle post deletion
            deleteButton?.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this post?')) {
                    axios.post('delete_post.php', { post_id: postId })
                        .then(response => {
                            if (response.data.success) {
                                window.location.href = 'community.php';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error deleting post. Please try again.');
                        });
                }
            });
        });
    </script>
</body>
</html>