/**
 * Programs Module - Handles program category and application interactions
 */

// Attach to window for onclick compatibility
window.programsModule = {
    /**
     * Fetch all program categories
     */
    async fetchCategories() {
        try {
            const response = await fetch('/api/programs/categories');
            if (!response.ok) throw new Error('Failed to fetch categories');
            return await response.json();
        } catch (error) {
            console.error('Error fetching categories:', error);
            return [];
        }
    },

    /**
     * Fetch programs by category
     */
    async fetchByCategory(categoryId) {
        try {
            const response = await fetch(`/api/programs/category/${categoryId}`);
            if (!response.ok) throw new Error('Category not found');
            return await response.json();
        } catch (error) {
            console.error('Error fetching category:', error);
            return null;
        }
    },

    /**
     * Fetch single program
     */
    async fetchProgram(programId) {
        try {
            const response = await fetch(`/api/programs/${programId}`);
            if (!response.ok) throw new Error('Program not found');
            return await response.json();
        } catch (error) {
            console.error('Error fetching program:', error);
            return null;
        }
    },

    /**
     * Submit program application
     */
    async submitApplication(formData) {
        try {
            const response = await fetch('/api/programs/apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify(formData),
            });

            if (!response.ok) throw new Error('Failed to submit application');
            return await response.json();
        } catch (error) {
            console.error('Error submitting application:', error);
            return null;
        }
    },

    /**
     * Open category modal with programs
     */
    async openCategoryModal(categoryId) {
        const category = await this.fetchByCategory(categoryId);
        
        if (!category) {
            this.showNoProgramModal(categoryId);
            return;
        }

        if (category.programs.length === 0) {
            this.showNoProgramModal(categoryId);
            return;
        }

        // For education category, show the education modal
        if (categoryId === 'education') {
            this.showEducationModal(category);
        } else {
            // For other categories, show generic category modal
            this.showCategoryModal(category);
        }
    },

    /**
     * Show education category modal
     */
    showEducationModal(category) {
        const modal = document.getElementById('educationModal');
        if (!modal) return;

        const modalBody = modal.querySelector('.modal-body');
        if (!modalBody) return;

        // Clear existing content
        modalBody.innerHTML = '';

        // Add programs
        category.programs.forEach(program => {
            const programHtml = `
                <div class="program-item">
                    <div class="program-header">
                        <h3>🎓 ${program.name}</h3>
                        <span class="program-status ${program.status}">${program.status}</span>
                    </div>
                    <p class="program-description">${program.description}</p>
                    <div class="program-meta">
                        <span>📅 Deadline: ${new Date(program.deadline).toLocaleDateString()}</span>
                        <span>👥 ${program.slots} slots available</span>
                    </div>
                    <button class="apply-btn" onclick="window.programsModule.openApplicationForm(${program.id})">Apply Now</button>
                </div>
            `;
            modalBody.innerHTML += programHtml;
        });

        modal.classList.add('active');
    },

    /**
     * Show generic category modal
     */
    showCategoryModal(category) {
        // TODO: Create a generic category modal for other program types
        this.showNoProgramModal(category.id);
    },

    /**
     * Show no programs available modal
     */
    showNoProgramModal(categoryId) {
        const modal = document.getElementById('noProgramModal');
        const modalTitle = document.getElementById('noProgramModalTitle');
        
        if (!modal || !modalTitle) return;

        // Find category name
        const categoryElement = document.querySelector(`[data-category="${categoryId}"]`);
        const categoryName = categoryElement?.querySelector('.category-content h3')?.textContent || 'Programs';
        
        modalTitle.textContent = categoryName;
        modal.classList.add('active');
    },

    /**
     * Open application form
     */
    openApplicationForm(programId) {
        // Redirect to scholarship application form
        window.location.href = '/scholarship/apply';
    },

    /**
     * Submit scholarship application
     */
    async submitScholarshipApplication(formElement) {
        const programId = document.getElementById('scholarshipFormModal')?.dataset.programId || 1;
        
        const formData = {
            program_id: parseInt(programId),
            full_name: formElement.querySelector('input[placeholder="Juan Dela Cruz"]')?.value || '',
            email: formElement.querySelector('input[type="email"]')?.value || '',
            contact_number: formElement.querySelector('input[placeholder="09XX XXX XXXX"]')?.value || '',
            address: formElement.querySelector('textarea[placeholder*="House No"]')?.value || '',
            school: formElement.querySelector('input[placeholder="Name of Institution"]')?.value || '',
            course: formElement.querySelector('input[placeholder*="BS Computer"]')?.value || '',
            year_level: formElement.querySelector('select')?.value || '',
            gpa: formElement.querySelectorAll('input[placeholder*="1.75"]')[0]?.value || '',
            parent_name: formElement.querySelectorAll('input[placeholder="Full Name"]')[1]?.value || '',
            occupation: formElement.querySelectorAll('input[placeholder="Occupation"]')[0]?.value || '',
            family_income: formElement.querySelectorAll('select')[1]?.value || '',
            siblings: parseInt(formElement.querySelector('input[type="number"]')?.value || 0),
            essay: formElement.querySelector('textarea[placeholder*="Share your story"]')?.value || '',
        };

        const result = await this.submitApplication(formData);
        
        if (result && result.success) {
            // Close form modal
            const formModal = document.getElementById('scholarshipFormModal');
            if (formModal) formModal.classList.remove('active');
            
            // Reset form
            formElement.reset();
            
            // Show success modal
            if (typeof showProgramSuccessModal === 'function') {
                showProgramSuccessModal();
            }
        } else {
            alert('Failed to submit application. Please try again.');
        }
    },
};

// Initialize programs module when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Program category click handlers
    const programCategories = document.querySelectorAll('.program-category');
    programCategories.forEach(category => {
        category.addEventListener('click', function() {
            const categoryType = this.dataset.category;
            window.programsModule.openCategoryModal(categoryType);
        });
    });

    // Apply button in education modal
    const applyButtons = document.querySelectorAll('.apply-btn');
    applyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Only handle if not already handled by onclick
            if (!this.hasAttribute('onclick')) {
                const educationModal = document.getElementById('educationModal');
                const scholarshipModal = document.getElementById('scholarshipFormModal');
                
                if (educationModal) {
                    educationModal.classList.remove('active');
                }
                
                if (scholarshipModal) {
                    scholarshipModal.classList.add('active');
                }
            }
        });
    });

    // Scholarship form submission
    const scholarshipForm = document.querySelector('.scholarship-form');
    if (scholarshipForm) {
        scholarshipForm.addEventListener('submit', function(e) {
            e.preventDefault();
            window.programsModule.submitScholarshipApplication(this);
        });
    }
});
