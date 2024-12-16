// Add to Cart button click event
document.getElementById('add-to-cart-btn').addEventListener('click', function() {
    let bookId = document.querySelector('input[name="book_id"]').value;
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "add_to_cart.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            let response = xhr.responseText;
            let messageType = response.includes("Sorry") || response.includes("Maximum") ? "error" : "info";
            showMessage(response, messageType);
            // Handle any other UI updates for the cart
        }
    };

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
    xhr.send("book_id=" + bookId);
});

// Buy Now button click event - it will submit the form to checkout directly
document.querySelector('button[name="buy_now"]').addEventListener('click', function() {
    document.getElementById('add-to-cart-form').submit();  // Submit the form for "buy_now"
});

//wishlist management 
document.addEventListener('DOMContentLoaded', function() {
    const wishlistForm = document.getElementById('wishlistForm');
    const wishlistBtn = wishlistForm.querySelector('.wishlist-btn');

    wishlistForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch("wishlist_process.php", {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            // Update the button text or icon based on the response
            if (data.includes("added")) {
                wishlistBtn.innerHTML = '<i class="fas fa-heart"></i> Added to Wishlist';
                wishlistBtn.classList.remove('btn-warning');
                wishlistBtn.classList.add('btn-success');
            } else if (data.includes("already")) {
                wishlistBtn.innerHTML = '<i class="fas fa-heart"></i> Already in Wishlist';
                wishlistBtn.classList.remove('btn-warning');
                wishlistBtn.classList.add('btn-secondary');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});