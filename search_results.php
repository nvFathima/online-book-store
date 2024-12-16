<?php
    // Connect to your database
    include('db_connect.php'); // Make sure to add your DB connection here

    if (isset($_GET['query'])) {
        $query = $_GET['query'];

        // Use a prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ?");
        $search_query = "%$query%";
        $stmt->bind_param("ss", $search_query, $search_query);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if($row['status'] != "pending"){
                    echo "<p>Book: " . $row['title'] . " by " . $row['author'] . "</p>";
                }
            }
        } else {
            echo "No results found.";
        }
    }
?>
