document.addEventListener('DOMContentLoaded', () => {
    const categoryButtons = document.querySelectorAll('[data-filter-category]');
    const barangayButtons = document.querySelectorAll('[data-filter-barangay]');
    const cards = document.querySelectorAll('.feed-card');
    const emptyState = document.getElementById('emptyState');

    let activeCategory = 'all';
    let activeBarangay = 'all';

    const normalize = (value) => String(value || '').trim().toLowerCase();

    const applyFilters = () => {
        let visibleCount = 0;

        cards.forEach((card) => {
            const cardCategory = normalize(card.getAttribute('data-item-category'));
            const cardBarangay = normalize(card.getAttribute('data-item-barangay'));

            const categoryMatch = activeCategory === 'all' || cardCategory === activeCategory;
            const barangayMatch = activeBarangay === 'all' || cardBarangay === activeBarangay;

            const show = categoryMatch && barangayMatch;
            card.hidden = !show;

            if (show) {
                visibleCount += 1;
            }
        });

        if (emptyState) {
            emptyState.hidden = visibleCount !== 0;
        }
    };

    categoryButtons.forEach((button) => {
        button.addEventListener('click', () => {
            categoryButtons.forEach((chip) => chip.classList.remove('active'));
            button.classList.add('active');
            activeCategory = normalize(button.getAttribute('data-filter-category'));
            applyFilters();
        });
    });

    barangayButtons.forEach((button) => {
        button.addEventListener('click', () => {
            barangayButtons.forEach((chip) => chip.classList.remove('active'));
            button.classList.add('active');
            activeBarangay = normalize(button.getAttribute('data-filter-barangay'));
            applyFilters();
        });
    });

    document.querySelectorAll('[data-details-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const cardBody = button.closest('.feed-card-body');
            const detailsText = cardBody ? cardBody.querySelector('[data-details-text]') : null;

            if (!detailsText) {
                return;
            }

            const isHidden = detailsText.hasAttribute('hidden');
            if (isHidden) {
                detailsText.removeAttribute('hidden');
                button.textContent = 'Hide Full Details';
            } else {
                detailsText.setAttribute('hidden', 'hidden');
                button.textContent = 'View Full Details';
            }
        });
    });

    applyFilters();
});
