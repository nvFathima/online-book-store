document.addEventListener('DOMContentLoaded', function() {
    const createPostForm = document.getElementById('create-post-form');
    const postsContainer = document.getElementById('posts-container');
    const allPostsBtn = document.getElementById('all-posts-btn');
    const myPostsBtn = document.getElementById('my-posts-btn');
    const sortSelect = document.getElementById('sort-select');
    const paginationContainer = document.getElementById('pagination-container');

    let currentPostType = 'all';
    let currentFilter = 'newest'; // Changed from currentSort to currentFilter to match PHP
    let currentPage = 1;

    // Handle form submission for creating a new post
    createPostForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const title = document.getElementById('post-title').value;
        const content = document.getElementById('post-content').value;
        createPost(title, content);
    });

    // Event listeners for filter buttons
    allPostsBtn.addEventListener('click', () => {
        currentPostType = 'all';
        currentPage = 1;
        fetchPosts();
    });

    myPostsBtn.addEventListener('click', () => {
        currentPostType = 'user';
        currentPage = 1;
        fetchPosts();
    });

    // Handle sorting changes
    sortSelect.addEventListener('change', () => {
        currentFilter = sortSelect.value; // Changed from currentSort to currentFilter
        currentPage = 1;
        fetchPosts();
    });

    // Fetch posts from server
    function fetchPosts() {
        const loadingMessage = '<div class="text-center py-4">Loading posts...</div>';
        postsContainer.innerHTML = loadingMessage;

        // Changed sort to filter in the URL parameters
        const url = `get_posts.php?type=${currentPostType}&filter=${currentFilter}&page=${currentPage}`;
        console.log('Fetching posts with URL:', url); // Debug log

        axios.get(url)
            .then(response => {
                console.log('Response data:', response.data); // Debug log
                renderPosts(response.data.posts);
                renderPagination(response.data.total_pages, response.data.current_page);
            })
            .catch(error => {
                console.error('Error fetching posts:', error);
                postsContainer.innerHTML = '<p class="text-red-500">Error loading posts. Please try again later.</p>';
            });
    }

    function createPost(title, content) {
        const formData = new FormData();
        formData.append('title', title);
        formData.append('content', content);
    
        // Check if there's an image input and append it if present
        const imageInput = document.querySelector('input[type="file"]');
        if (imageInput && imageInput.files.length > 0) {
            formData.append('image', imageInput.files[0]);
        }
    
        axios.post('create_post.php', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(response => {
            if (response.data.success) {
                fetchPosts('all');
                document.getElementById('post-title').value = '';
                document.getElementById('post-content').value = '';
                if (imageInput) {
                    imageInput.value = ''; // Clear the file input
                }
            } else {
                alert('Error creating post: ' + (response.data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating post: ' + (error.response?.data?.error || error.message || 'Unknown error'));
        });
    }

    // Render posts to the container
    function renderPosts(posts) {
        console.log(posts);
        if (!posts || !posts.length) {
            postsContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No posts found.</p>';
            return;
        }

        postsContainer.innerHTML = posts.map(post => `
            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-xl font-bold">${escapeHtml(post.title)}</h3>
                        <p class="text-gray-600 text-sm">
                            Posted by ${escapeHtml(post.first_name)} ${escapeHtml(post.last_name)} on 
                            ${new Date(post.created_at).toLocaleDateString()}
                        </p>
                    </div>
                    ${post.image_path ? `
                        <img src="${escapeHtml(post.image_path)}" alt="Post image" 
                             class="w-24 h-24 object-cover rounded">
                    ` : ''}
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-800">${escapeHtml(truncateText(post.content, 200))}</p>
                    <a href="post_details.php?id=${post.post_id}" 
                       class="text-blue-600 hover:text-blue-800" target="_blank">
                        Read more...
                    </a>
                </div>

                <div class="flex items-center justify-between border-t pt-4">
                    <div class="flex items-center space-x-4">
                        <button class="like-btn ${post.user_liked ? 'text-red-500' : 'text-gray-500'}"
                                data-post-id="${post.post_id}">
                            <i class="fas fa-heart"></i>
                            <span class="like-count">${post.like_count}</span>
                        </button>
                        <span class="text-gray-500">
                            <i class="fas fa-comment"></i> ${post.comment_count}
                        </span>
                    </div>
                </div>
            </div>
        `).join('');

        // Attach like button event listeners
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', handleLikeClick);
        });
    }

    // Handle like button clicks
    function handleLikeClick(e) {
        const postId = this.dataset.postId;
        
        axios.post('like_post.php', { post_id: postId })
            .then(response => {
                if (response.data.success) {
                    const likeCount = this.querySelector('.like-count');
                    likeCount.textContent = response.data.new_like_count;
    
                    // Toggle button class based on action
                    if (response.data.action === 'like') {
                        this.classList.add('text-red-500');
                        this.classList.remove('text-gray-500');
                    } else {
                        this.classList.remove('text-red-500');
                        this.classList.add('text-gray-500');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating like. Please try again.');
            });
    }    

    // Render pagination controls
    function renderPagination(totalPages, currentPage) {
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let paginationHTML = '<div class="flex justify-center items-center gap-2">';
        
        // Previous button
        if (currentPage > 1) {
            paginationHTML += `
                <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
                        onclick="changePage(${currentPage - 1})">
                    Previous
                </button>`;
        }

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || 
                (i >= currentPage - 2 && i <= currentPage + 2)) {
                paginationHTML += `
                    <button class="px-3 py-1 rounded ${i === currentPage ? 
                        'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300'}"
                        onclick="changePage(${i})">
                        ${i}
                    </button>`;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                paginationHTML += '<span class="px-2">...</span>';
            }
        }

        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `
                <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
                        onclick="changePage(${currentPage + 1})">
                    Next
                </button>`;
        }

        paginationHTML += '</div>';
        paginationContainer.innerHTML = paginationHTML;
    }

    // Helper function to truncate text
    function truncateText(text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substr(0, maxLength) + '...';
    }

    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Global function for changing pages
    window.changePage = function(page) {
        currentPage = page;
        fetchPosts();
    };

    // Initial load
    fetchPosts();
});