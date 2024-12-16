function showAddBookForm() {
    document.getElementById('addBookForm').style.display = 'block';
    document.getElementById('bookList').style.display = 'none';
    document.getElementById('togglecontrol').style.visibility = 'hidden';
}

function showBookList() {
    // Hide addBookForm and show bookList
    document.getElementById('addBookForm').style.display = 'none';
    document.getElementById('bookList').style.display = 'block';
    document.getElementById('togglecontrol').style.visibility = 'visible';

    // Get current status from URL or default to 'approved'
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status') || 'approved';  // Defaults to 'approved' if not provided

    // Set the active button (approved, pending, or rejected)
    setActiveToggle(status);
}

// Function to handle the active button state
function setActiveToggle(status) {
    const approvedBtn = document.querySelector(".toggle-btn.approved");
    const pendingBtn = document.querySelector(".toggle-btn.pending");
    const rejectedBtn = document.querySelector(".toggle-btn.rejected"); // Add a button for 'rejected'

    // Remove 'active' class from all buttons
    approvedBtn.classList.remove("active");
    pendingBtn.classList.remove("active");
    rejectedBtn.classList.remove("active"); // Reset rejected button

    // Add 'active' class based on the current status
    if (status === 'approved') {
        approvedBtn.classList.add("active");
    } else if (status === 'pending') {
        pendingBtn.classList.add("active");
    } else if (status === 'rejected') {
        rejectedBtn.classList.add("active");  // Handle rejected button state
    }
}

function toggleAgeSelect() {
    var conditionSelect = document.getElementById('condition');
    var ageSelect = document.getElementById('ageSelect');
    ageSelect.style.display = conditionSelect.value === 'Used' ? 'block' : 'none';
}

function removeBook(bookId) {
    if (confirm('Are you sure you want to remove this book?')) {
        // Send a request to the server to delete the book
        window.location.href = 'delete_book.php?id=' + bookId;
    }
}

