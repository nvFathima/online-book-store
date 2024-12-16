<?php
include "../db_connect.php"; // Assuming this initializes a MySQLi connection in $conn
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Function to get paginated posts
function getPosts($conn, $user_id = null, $sort = "newest", $page = 1, $postsPerPage = 5) {
    // Calculate the offset for pagination
    $offset = ($page - 1) * $postsPerPage;

    // Start building the SQL query based on post type and sort order
    $sql = "SELECT p.post_id, p.title, p.content, p.created_at, p.image_path, u.first_name, u.last_name,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) AS like_count,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) AS comment_count
            FROM posts p
            JOIN user u ON p.user_id = u.user_id";

    // Filter for user-specific posts
    if ($user_id) {
        $sql .= " WHERE p.user_id = ?";
    }

    // Add sorting order
    switch ($sort) {
        case "oldest":
            $sql .= " ORDER BY p.created_at ASC";
            break;
        case "alphabetical":
            $sql .= " ORDER BY p.title ASC";
            break;
        case "popular":
            $sql .= " ORDER BY like_count DESC";
            break;
        default:
            $sql .= " ORDER BY p.created_at DESC";  // Default to newest
    }

    // Append pagination limits directly (no placeholders for LIMIT and OFFSET)
    $sql .= " LIMIT $postsPerPage OFFSET $offset";

    // Prepare the statement
    if ($user_id) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare($sql);
    }

    // Execute and fetch results
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    // Get the total number of posts for pagination purposes
    $countSql = "SELECT COUNT(*) as total FROM posts";
    if ($user_id) {
        $countSql .= " WHERE user_id = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $user_id);
    } else {
        $countStmt = $conn->prepare($countSql);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalPosts = $countResult->fetch_assoc()['total'];
    $countStmt->close();

    // Calculate total pages
    $totalPages = ceil($totalPosts / $postsPerPage);

    // Return the posts and total pages
    return [
        "posts" => $posts,
        "totalPages" => $totalPages,
    ];
}

// Handling AJAX request from JavaScript
$type = $_GET['type'] ?? 'all';
$sort = $_GET['sort'] ?? 'newest';
$page = intval($_GET['page'] ?? 1);

// Set user_id if the type is 'user', otherwise leave it null for 'all' posts
$user_id = ($type === 'user') ? $_SESSION['user_id'] : null;

$response = getPosts($conn, $user_id, $sort, $page);
//echo json_encode($response);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Site Metas -->
    <title>Community</title>
    <meta name="keywords" content="BookHub, online book store, buy books, sell books, donate books, second-hand books">
    <meta name="description" content="BookHub is your digital bookshelf, where you can buy, sell, and donate new and used books, making reading affordable for everyone.">
    <meta name="author" content="">

    <!-- Site Icons -->
    <link rel="icon" href="../images/main-ico.png" type="image/png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <!-- Site CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Responsive CSS -->
    <link rel="stylesheet" href="../css/responsive.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/custom.css">
    <link rel="stylesheet" href="community_style.css">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header_user.php'; ?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Readers' Community</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="customer_dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Community</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->
    <div class="container mx-auto p-4 bg-gray-100">
        <h1 class="text-3xl font-bold mb-6">Reader's Community</h1>
        
        <!-- Create Post Form -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-semibold mb-4">Create a New Post</h2>
            <form id="create-post-form" enctype="multipart/form-data">
                <input type="text" id="post-title" placeholder="Post Title" class="w-full p-2 mb-2 border rounded">
                <textarea id="post-content" placeholder="Share your reading experience..." class="w-full p-2 mb-2 border rounded"></textarea>
                <input type="file" name="image" accept="image/*" class="w-full p-2 mb-2 border rounded"/>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Post</button>
            </form>
        </div>

        <!-- Posts Section -->
        <div class="mb-4">
            <button id="all-posts-btn" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">All Posts</button>
            <button id="my-posts-btn" class="bg-green-500 text-white px-4 py-2 rounded">My Posts</button>
            <select id="sort-select" class="bg-gray-200 text-gray-700 px-4 py-2 rounded">
                <option value="newest">Newest</option>
                <option value="oldest">Oldest</option>
                <option value="alphabetical">Alphabetical</option>
                <option value="popular">Most Popular</option>
            </select>
        </div>

        <div id="posts-container">
            <!-- Posts will be dynamically inserted here -->
        </div>
        <div id="pagination-container" class="mt-4">
        <!-- Pagination buttons will be inserted here -->
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script src="../js/community.js"></script>

    <?php include '../footer.php'; ?>
</body>
</html>