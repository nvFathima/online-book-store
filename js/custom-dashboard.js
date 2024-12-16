//wishlist button management
document.addEventListener('DOMContentLoaded', function() {
    // Function to handle wishlist actions
    function handleWishlist(event) {
        event.preventDefault();
        
        let bookId = this.getAttribute('data-book-id');
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "wishlist_process.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                showMessage(xhr.responseText);
                // Optionally, update the wishlist icon here
                // For example, change the icon to a filled heart
                event.target.classList.replace('far', 'fas');
            }
        };
        
        xhr.send("book_id=" + bookId);
    }

    // Attach the event listener to all wishlist icons
    let wishlistIcons = document.querySelectorAll('.wishlist-icon');
    wishlistIcons.forEach(function(icon) {
        icon.addEventListener('click', handleWishlist);
    });

    // Message display function (reuse the one from the previous example)
    function showMessage(message, type = 'info') {
        // Remove any existing message
        const existingMessage = document.getElementById('custom-message');
        if (existingMessage) {
            document.body.removeChild(existingMessage);
        }

        // Create and show a custom message element
        let messageElement = document.createElement('div');
        messageElement.id = 'custom-message';
        messageElement.textContent = message;
        
        // Set styles
        Object.assign(messageElement.style, {
            position: 'fixed',
            top: '20px',
            left: '50%',
            transform: 'translateX(-50%)',
            padding: '12px 20px',
            backgroundColor: type === 'error' ? '#ff4d4d' : '#4CAF50',
            color: 'white',
            borderRadius: '5px',
            boxShadow: '0 4px 8px rgba(0,0,0,0.1)',
            zIndex: '10000',
            fontSize: '16px',
            textAlign: 'center',
            opacity: '0',
            transition: 'opacity 0.3s ease-in-out'
        });

        document.body.appendChild(messageElement);

        // Trigger reflow to enable transition
        messageElement.offsetHeight;

        // Make the message visible
        messageElement.style.opacity = '1';

        // Remove the message after 3 seconds
        setTimeout(() => {
            messageElement.style.opacity = '0';
            setTimeout(() => {
                if (messageElement.parentNode) {
                    document.body.removeChild(messageElement);
                }
            }, 300);
        }, 3000);
    }
});