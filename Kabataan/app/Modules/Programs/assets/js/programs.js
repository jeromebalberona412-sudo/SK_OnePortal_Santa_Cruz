/**
 * Programs Module — category modals and application redirects (frontend only)
 */

window.programsModule = {
    async fetchCategories() {
        return [];
    },

    async fetchByCategory() {
        return null;
    },

    async fetchProgram() {
        return null;
    },

    async openCategoryModal(categoryId) {
        if (categoryId === 'education') {
            const modal = document.getElementById('educationModal');
            if (modal) modal.classList.add('active');
            return;
        }
        if (categoryId === 'sports') {
            if (typeof openSportsModal === 'function') openSportsModal();
            return;
        }
        this.showNoProgramModal(categoryId);
    },

    showNoProgramModal(categoryId) {
        const modal = document.getElementById('noProgramModal');
        const modalTitle = document.getElementById('noProgramModalTitle');
        if (!modal || !modalTitle) return;

        const categoryElement = document.querySelector(`[data-category="${categoryId}"]`);
        const categoryName = categoryElement?.querySelector('.category-content h3')?.textContent || 'Programs';
        modalTitle.textContent = categoryName;
        modal.classList.add('active');
    },

    openApplicationForm(programType) {
        if (programType === 'sports') {
            window.location.href = '/sports/apply';
            return;
        }
        window.location.href = '/scholarship/apply';
    },
};
