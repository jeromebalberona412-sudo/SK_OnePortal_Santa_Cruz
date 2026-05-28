document.addEventListener('DOMContentLoaded', function() {
    // Global handler for category clicks
    window.handleCategoryClick = function(categoryId) {
        console.log('Category clicked:', categoryId);
        if (window.programsModule && window.programsModule.openCategoryModal) {
            window.programsModule.openCategoryModal(categoryId);
        } else {
            console.error('programsModule not available');
        }
    };

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
            window.programsModule.openCategoryModal(categoryType);
        });
    });

    // Program modals are now handled by the programs module

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

    // Scholarship form submission is now handled by the programs module

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
            window.programsModule.openCategoryModal('education');
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

    // ── Barangay Profile — navigate to page ──────────────────────────────────
    document.querySelectorAll('.brgy-profile-item').forEach((item) => {
        item.addEventListener('click', () => {
            const name = item.dataset.brgyName;
            const slug = name.toLowerCase().replace(/\s+/g, '-');
            showLoading('Loading');
            window.location.href = `/barangay/${slug}`;
        });
    });
});


// ── Feed Posts Rendering with Program Details ──────────────────────────────
let feedPosts = [];
let currentPage = 1;
let currentFilter = 'all';
let isLoadingFeed = false;

async function loadFeedPosts(reset = true) {
    if (isLoadingFeed) return;
    isLoadingFeed = true;

    const container = document.getElementById('feed-posts');
    if (reset) {
        currentPage = 1;
        feedPosts = [];
        container.innerHTML = '<div class="post-card" style="text-align:center;color:#999;padding:32px;">Loading...</div>';
    }

    try {
        // Mock data - replace with actual API call
        const mockPosts = [
            // PROGRAMS
            {
                id: 1,
                type: 'program',
                author: 'SK Education Committee',
                avatar: 'https://ui-avatars.com/api/?name=SK+Education&background=2196F3&color=fff',
                time: '2 days ago',
                title: 'Scholarship Assistance Program (SY 2026)',
                description: 'Financial assistance for deserving youth to support tuition and essential academic needs for the school year 2026.',
                program_details: {
                    committee_handled_by: 'Education Committee',
                    status: 'active',
                    participant_quantity: 50,
                    starting_date: '2026-03-01',
                    end_date: '2026-03-31',
                    venue: 'Santa Cruz Barangay Hall',
                    full_description: 'The Scholarship Assistance Program provides financial support to qualified youth residents of Santa Cruz. The grant aims to cover tuition fees and other required academic expenses. Applicants must submit complete requirements within the schedule and meet academic and community service expectations for continued eligibility.',
                    terms_and_conditions: [
                        'Applicant must be a bonafide resident of Barangay Santa Cruz',
                        'Must submit complete requirements before the deadline',
                        'Must maintain a general weighted average of at least 85% or equivalent',
                        'Recipients may be renewed each semester subject to compliance',
                        'Recipients must render 40 hours of community service per semester',
                        'False information will result in disqualification',
                        'Scholarship grant is non-transferable'
                    ],
                    requirements: [
                        'Certificate of Enrollment (for the current school year)',
                        'Certified True Copy of school records or COR (if applicable)',
                        'Barangay Certificate of Indigency',
                        'Valid ID (front and back)',
                        'Recent 2x2 ID picture',
                        'Essay explaining financial need and academic goals (500 words minimum)'
                    ],
                    deadline: '2026-03-31',
                    slots: 50,
                    slots_remaining: 42,
                }
            },
            {
                id: 2,
                type: 'program',
                author: 'SK Skills Training Committee',
                avatar: 'https://ui-avatars.com/api/?name=SK+Skills&background=0450a8&color=fff',
                time: '5 days ago',
                title: 'Community Skills Training: Basic Computer Literacy',
                description: 'A short course to help youth learn computer fundamentals, typing, and safe online practices.',
                program_details: {
                    committee_handled_by: 'Skills Training Committee',
                    status: 'active',
                    participant_quantity: 30,
                    starting_date: '2026-04-10',
                    end_date: '2026-05-10',
                    venue: 'Barangay Multi-Purpose Hall',
                    full_description: 'This training program is designed to improve digital literacy among youth. Participants will learn basic computer operations, effective typing practice, and practical guidance on online safety and responsible technology use.',
                    terms_and_conditions: [
                        'Participants must attend scheduled sessions on time',
                        'Must comply with classroom guidelines and respectful behavior',
                        'Completion of all required activities is necessary for certification',
                        'Participants must submit any required forms before the first session'
                    ],
                    requirements: [
                        'Participant registration form',
                        'Valid ID or school ID',
                        'Barangay endorsement letter (if requested)',
                        'Any available notebook or stationery for practice'
                    ],
                    deadline: '2026-04-08',
                    slots: 30,
                    slots_remaining: 18,
                }
            },

            // ANNOUNCEMENTS
            {
                id: 3,
                type: 'announcement',
                author: 'SK Youth Advisory Board',
                avatar: 'https://ui-avatars.com/api/?name=SK+Advisory&background=15803d&color=fff',
                time: '1 day ago',
                title: 'Youth Advisory: Meeting Schedule Update',
                description: 'The next SK youth advisory meeting will be held next Friday. Please arrive 15 minutes early for registration and agenda review.',
            },

            // EVENTS
            {
                id: 4,
                type: 'event',
                author: 'SK Community Affairs Committee',
                avatar: 'https://ui-avatars.com/api/?name=SK+Community&background=ff7a00&color=fff',
                time: '3 days ago',
                title: 'Career Readiness Session',
                description: 'A youth-focused career orientation covering practical job readiness, interview basics, and CV preparation. Open to ages 15 to 30.',
            },

            // ACTIVITIES
            {
                id: 5,
                type: 'activity',
                author: 'SK Barangay Volunteers',
                avatar: 'https://ui-avatars.com/api/?name=SK+Volunteers&background=6a1b9a&color=fff',
                time: '6 days ago',
                title: 'Monthly Community Clean-Up Drive',
                description: 'Help keep Santa Cruz clean and safe. Participants are encouraged to bring gloves and water. Coordination will be provided at the barangay hall.'
            }
        ];

        feedPosts = reset ? mockPosts : [...feedPosts, ...mockPosts];
        renderFeedPosts();
    } catch (error) {
        console.error('Error loading feed:', error);
        container.innerHTML = '<div class="post-card" style="text-align:center;color:#ef4444;padding:32px;">Failed to load posts</div>';
    } finally {
        isLoadingFeed = false;
    }
}

function renderFeedPosts() {
    const container = document.getElementById('feed-posts');
    container.innerHTML = '';

    const filtered = currentFilter === 'all' 
        ? feedPosts 
        : feedPosts.filter(p => p.type === currentFilter);

    if (filtered.length === 0) {
        container.innerHTML = '<div class="post-card" style="text-align:center;color:#999;padding:32px;">No posts found</div>';
        return;
    }

    filtered.forEach(post => {
        const postEl = createPostElement(post);
        container.appendChild(postEl);
    });
}

function createPostElement(post) {
    const article = document.createElement('article');
    article.className = 'post-card';
    article.dataset.postType = post.type;

    if (post.type === 'program' && post.program_details) {
        article.innerHTML = createProgramPostHTML(post);
    } else {
        article.innerHTML = createRegularPostHTML(post);
    }

    return article;
}

function createProgramPostHTML(post) {
    const details = post.program_details;
    const startDate = new Date(details.starting_date).toLocaleDateString();
    const endDate = new Date(details.end_date).toLocaleDateString();
    const deadline = new Date(details.deadline).toLocaleDateString();

    return `
        <div class="post-header">
            <img src="${post.avatar}" alt="${post.author}" class="post-avatar">
            <div class="post-info">
                <p class="post-author">${post.author}</p>
                <div class="post-meta">
                    <span class="post-type program">Program</span>
                    <span class="post-time">${post.time}</span>
                </div>
            </div>
        </div>

        <div class="post-content">
            <h3 class="post-title">${post.title}</h3>
            <p class="post-text">${post.description}</p>

            <!-- Program Meta Grid -->
            <div class="program-meta-grid">
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span><strong>Deadline:</strong> ${deadline}</span>
                </div>
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span><strong>Slots:</strong> ${details.slots_remaining} / ${details.slots} available</span>
                </div>
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span><strong>Venue:</strong> ${details.venue}</span>
                </div>
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                    <span><strong>Committee:</strong> ${details.committee_handled_by}</span>
                </div>
                <div class="meta-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span><strong>Duration:</strong> ${startDate} - ${endDate}</span>
                </div>
            </div>

            <!-- More Details Expandable Section -->
            <div class="program-details-section">
                <button class="more-details-btn" onclick="toggleProgramDetails(this)">
                    <span>More Details</span>
                    <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                
                <div class="details-content" style="display: none;">
                    <!-- Full Description -->
                    <div class="detail-block">
                        <h4>Full Description</h4>
                        <p>${details.full_description}</p>
                    </div>

                    <!-- Requirements -->
                    <div class="detail-block">
                        <h4>Requirements</h4>
                        <ul class="requirements-list">
                            ${details.requirements.map(req => `<li>${req}</li>`).join('')}
                        </ul>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="detail-block">
                        <h4>Terms & Conditions</h4>
                        <ul class="terms-list">
                            ${details.terms_and_conditions.map(term => `<li>${term}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            </div>

            <a href="/scholarship/apply" class="view-details-btn">
                Apply Now
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
    `;
}

function createRegularPostHTML(post) {
    const typeLabel = post.type
        ? post.type.charAt(0).toUpperCase() + post.type.slice(1)
        : '';
    return `
        <div class="post-header">
            <img src="${post.avatar}" alt="${post.author}" class="post-avatar">
            <div class="post-info">
                <p class="post-author">${post.author}</p>
                <div class="post-meta">
                    <span class="post-type ${post.type}">${typeLabel}</span>
                    <span class="post-time">${post.time}</span>
                </div>
            </div>
        </div>
        <div class="post-content">
            <h3 class="post-title">${post.title}</h3>
            <p class="post-text">${post.description}</p>
        </div>
    `;
}

function toggleProgramDetails(button) {
    const detailsContent = button.nextElementSibling;
    const chevron = button.querySelector('.chevron-icon');
    const span = button.querySelector('span');
    
    if (detailsContent.style.display === 'none') {
        detailsContent.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
        span.textContent = 'Less Details';
    } else {
        detailsContent.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
        span.textContent = 'More Details';
    }
}

function setFeedFilter(button, filter) {
    document.querySelectorAll('.feed-tab').forEach(tab => tab.classList.remove('active'));
    button.classList.add('active');
    currentFilter = filter;
    renderFeedPosts();
}

function loadMorePosts() {
    currentPage++;
    loadFeedPosts(false);
}

// Initialize feed on page load
if (document.getElementById('feed-posts')) {
    loadFeedPosts();
}

// Make functions globally available
window.toggleProgramDetails = toggleProgramDetails;
window.setFeedFilter = setFeedFilter;
window.loadMorePosts = loadMorePosts;
