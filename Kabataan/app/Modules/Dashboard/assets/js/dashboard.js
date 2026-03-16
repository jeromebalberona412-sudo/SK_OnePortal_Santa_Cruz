document.addEventListener('DOMContentLoaded', function() {
    // Comment toggle functionality
    const commentButtons = document.querySelectorAll('.comment-btn');
    commentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postCard = this.closest('.post-card');
            const commentsSection = postCard.querySelector('.comments-section');
            
            if (commentsSection.style.display === 'none' || !commentsSection.style.display) {
                commentsSection.style.display = 'block';
            } else {
                commentsSection.style.display = 'none';
            }
        });
    });

    // Program category click handlers
    const programCategories = document.querySelectorAll('.program-category');
    programCategories.forEach(category => {
        category.addEventListener('click', function() {
            const categoryType = this.dataset.category;
            
            if (categoryType === 'education') {
                openEducationModal();
            } else {
                // For other categories, show "No Available Program" modal
                showNoProgramModal();
            }
        });
    });

    // Education modal
    function openEducationModal() {
        const modal = document.getElementById('educationModal');
        if (modal) {
            modal.classList.add('active');
        }
    }

    // Show "No Available Program" modal
    function showNoProgramModal() {
        const modal = document.getElementById('noProgramModal');
        const modalTitle = document.getElementById('noProgramModalTitle');
        
        if (modal && modalTitle) {
            // Get the category name from the clicked element
            const clickedCategory = event.target.closest('.program-category');
            const categoryName = clickedCategory.querySelector('.category-content h3').textContent;
            
            // Set the modal title to the category name
            modalTitle.textContent = categoryName + ' Programs';
            
            modal.classList.add('active');
        }
    }

    // Apply button in education modal
    const applyButtons = document.querySelectorAll('.apply-btn');
    applyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const educationModal = document.getElementById('educationModal');
            const scholarshipModal = document.getElementById('scholarshipFormModal');
            
            if (educationModal) {
                educationModal.classList.remove('active');
            }
            
            if (scholarshipModal) {
                scholarshipModal.classList.add('active');
            }
        });
    });

    // Modal close buttons
    const modalCloseButtons = document.querySelectorAll('.modal-close');
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.program-modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Close modal when clicking overlay
    const modalOverlays = document.querySelectorAll('.modal-overlay');
    modalOverlays.forEach(overlay => {
        overlay.addEventListener('click', function() {
            const modal = this.closest('.program-modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Scholarship form submission
    const scholarshipForm = document.querySelector('.scholarship-form');
    if (scholarshipForm) {
        scholarshipForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Close form modal
            const formModal = document.getElementById('scholarshipFormModal');
            if (formModal) formModal.classList.remove('active');

            // Reset form
            this.reset();

            // Show success modal
            if (typeof showProgramSuccessModal === 'function') {
                showProgramSuccessModal();
            }
        });
    }

    // Send comment functionality
    const sendCommentButtons = document.querySelectorAll('.send-comment-btn');
    sendCommentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const wrapper = this.closest('.comment-input-wrapper');
            const input = wrapper.querySelector('.comment-input');
            const commentText = input.value.trim();
            
            if (commentText) {
                // Create new comment element
                const commentsSection = this.closest('.comments-section');
                const newComment = createCommentElement(commentText);
                
                // Insert before input wrapper
                commentsSection.insertBefore(newComment, wrapper);
                
                // Clear input
                input.value = '';
                
                // Update comment count
                const postCard = this.closest('.post-card');
                updateCommentCount(postCard);
            }
        });
    });

    // Enter key to send comment
    const commentInputs = document.querySelectorAll('.comment-input');
    commentInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const sendButton = this.nextElementSibling;
                if (sendButton) {
                    sendButton.click();
                }
            }
        });
    });

    // Helper function to create comment element
    function createCommentElement(text) {
        const div = document.createElement('div');
        div.className = 'comment-item';
        div.innerHTML = `
            <img src="https://ui-avatars.com/api/?name=You&background=667eea&color=fff" alt="You">
            <div class="comment-content">
                <p class="comment-author">You</p>
                <p class="comment-text">${escapeHtml(text)}</p>
                <span class="comment-time">Just now</span>
            </div>
        `;
        return div;
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Helper function to update comment count
    function updateCommentCount(postCard) {
        const commentBtn = postCard.querySelector('.comment-btn span');
        if (commentBtn) {
            const currentText = commentBtn.textContent;
            const match = currentText.match(/\d+/);
            if (match) {
                const count = parseInt(match[0]) + 1;
                commentBtn.textContent = `Comment (${count})`;
            }
        }
    }

    // Like button functionality
    const likeButtons = document.querySelectorAll('.action-btn:first-child');
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const span = this.querySelector('span');
            if (span) {
                const currentText = span.textContent;
                const match = currentText.match(/\d+/);
                if (match) {
                    const count = parseInt(match[0]);
                    const isLiked = this.classList.contains('liked');
                    
                    if (isLiked) {
                        span.textContent = `Like (${count - 1})`;
                        this.classList.remove('liked');
                        this.style.color = '#666';
                    } else {
                        span.textContent = `Like (${count + 1})`;
                        this.classList.add('liked');
                        this.style.color = '#667eea';
                    }
                }
            }
        });
    });

    // View details button functionality
    const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            openEducationModal();
        });
    });

    console.log('Dashboard initialized successfully!');

    // ── Programs Drawer (mobile/tablet) ───────────────────────────────────────
    const drawerBtn      = document.getElementById('programsDrawerBtn');
    const sidebar        = document.querySelector('.programs-sidebar');
    const backdrop       = document.getElementById('programsDrawerBackdrop');

    function openDrawer() {
        sidebar?.classList.add('drawer-open');
        backdrop?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        sidebar?.classList.remove('drawer-open');
        backdrop?.classList.remove('active');
        document.body.style.overflow = '';
    }

    drawerBtn?.addEventListener('click', () => {
        sidebar?.classList.contains('drawer-open') ? closeDrawer() : openDrawer();
    });

    backdrop?.addEventListener('click', closeDrawer);

    // Close drawer when a program category is clicked (opens a modal)
    document.querySelectorAll('.program-category').forEach(cat => {
        cat.addEventListener('click', () => {
            if (window.innerWidth <= 1200) closeDrawer();
        });
    });
});
