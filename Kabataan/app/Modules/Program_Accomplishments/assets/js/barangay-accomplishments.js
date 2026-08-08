document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('barangayAbyipSearch');
    const grid = document.getElementById('accomplishmentsBarangayGrid');
    const resultCount = document.getElementById('accomplishmentsResultCount');
    const filterEmpty = document.getElementById('accomplishmentsFilterEmpty');

    if (!searchInput || !grid) {
        return;
    }

    const cards = Array.from(grid.querySelectorAll('.accomplishments-barangay-card'));

    const filterCards = () => {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        cards.forEach((card) => {
            const name = card.dataset.barangayName || '';
            const isVisible = query === '' || name.includes(query);
            card.hidden = !isVisible;

            if (isVisible) {
                visibleCount += 1;
            }
        });

        if (resultCount) {
            resultCount.textContent = query === ''
                ? `Showing ${cards.length} barangays`
                : `Showing ${visibleCount} of ${cards.length} barangays`;
        }

        if (filterEmpty) {
            filterEmpty.hidden = visibleCount > 0;
            grid.hidden = visibleCount === 0;
        }
    };

    searchInput.addEventListener('input', filterCards);
});
