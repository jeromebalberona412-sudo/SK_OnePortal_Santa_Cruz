// Profile Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const filterTabs = document.querySelectorAll('.tab-btn');
    const programItems = document.querySelectorAll('.program-item');

    // Filter Programs
    if (filterTabs.length > 0) {
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Update active tab
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Filter programs
                filterPrograms(filter);
            });
        });
    }

    function filterPrograms(filter) {
        programItems.forEach(item => {
            const status = item.getAttribute('data-status');
            
            if (filter === 'all') {
                item.style.display = 'flex';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 10);
            } else if (status === filter) {
                item.style.display = 'flex';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, 10);
            } else {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    item.style.display = 'none';
                }, 300);
            }
        });

        // Check if any programs are visible
        setTimeout(() => {
            const visiblePrograms = Array.from(programItems).filter(item => 
                item.style.display !== 'none'
            );
            
            const programsList = document.querySelector('.programs-list');
            const existingEmpty = programsList.querySelector('.empty-state');
            
            if (visiblePrograms.length === 0 && !existingEmpty) {
                const emptyState = createEmptyState(filter);
                programsList.appendChild(emptyState);
            } else if (visiblePrograms.length > 0 && existingEmpty) {
                existingEmpty.remove();
            }
        }, 350);
    }

    function createEmptyState(filter) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'empty-state';
        
        let message = 'No programs found';
        if (filter === 'pending') message = 'No pending programs';
        if (filter === 'ongoing') message = 'No ongoing programs';
        if (filter === 'completed') message = 'No completed programs';
        
        emptyDiv.innerHTML = `
            <div class="empty-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M3 10 L12 6 L21 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="8" y1="13" x2="16" y2="13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <line x1="10" y1="16" x2="14" y2="16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h3>${message}</h3>
            <p>Check back later or explore new programs in the dashboard!</p>
        `;
        
        return emptyDiv;
    }

    // Smooth scroll for programs list
    const programsList = document.querySelector('.programs-list');
    if (programsList) {
        programsList.style.scrollBehavior = 'smooth';
    }

    // Add animation to program items on load
    programItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 50);
    });
});

// Modal Functions
function openEditModal() {
    const modal = document.getElementById('editProfileModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeEditModal() {
    const modal = document.getElementById('editProfileModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        // Reload page to show updated data
        window.location.reload();
    }
}

// Calculate Age from Birthdate
function calculateAge() {
    const birthdateInput = document.getElementById('edit_birthdate');
    const ageInput = document.getElementById('edit_age');
    
    if (birthdateInput && ageInput) {
        const birthdate = new Date(birthdateInput.value);
        const today = new Date();
        let age = today.getFullYear() - birthdate.getFullYear();
        const monthDiff = today.getMonth() - birthdate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
            age--;
        }
        
        ageInput.value = age;
    }
}

// Update Profile Form Submission
function updateProfile(event) {
    event.preventDefault();
    
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    // Simulate update (since this is prototype mode)
    // In production, this would send data to the server
    
    // Update session data
    const updatedData = {
        first_name: formData.get('first_name'),
        middle_initial: formData.get('middle_initial'),
        last_name: formData.get('last_name'),
        suffix: formData.get('suffix'),
        username: formData.get('username'),
        birthdate: formData.get('birthdate'),
        age: formData.get('age'),
        email: formData.get('email'),
        contact_number: formData.get('contact_number'),
        province: formData.get('province'),
        municipality: formData.get('municipality'),
        barangay: formData.get('barangay')
    };
    
    // Store in sessionStorage for prototype
    sessionStorage.setItem('profile_updated', JSON.stringify(updatedData));
    
    // Close edit modal
    closeEditModal();
    
    // Show success modal
    setTimeout(() => {
        const successModal = document.getElementById('successModal');
        if (successModal) {
            successModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }, 300);
    
    return false;
}

// View Program Details Function
function viewProgramDetails(programId) {
    console.log('Viewing program:', programId);
    alert('Program details will be available when the backend is implemented!');
}

// Close modals on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
        closeSuccessModal();
    }
});

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        closeEditModal();
        closeSuccessModal();
    }
});
