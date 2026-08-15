document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('barangayAccomplishmentsSearch');
    const grid = document.getElementById('accomplishmentsBarangayGrid');
    const resultCount = document.getElementById('accomplishmentsResultCount');
    const filterEmpty = document.getElementById('accomplishmentsFilterEmpty');

    if (searchInput && grid) {
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
    }

    const lightbox = document.getElementById('programPhotoLightbox');
    const lightboxImage = document.getElementById('programPhotoLightboxImage');
    const lightboxCaption = document.getElementById('programPhotoLightboxCaption');
    const lightboxClose = document.getElementById('programPhotoLightboxClose');

    const closeLightbox = () => {
        if (!lightbox) return;
        lightbox.hidden = true;
        document.body.style.overflow = '';
        if (lightboxImage) {
            lightboxImage.src = '';
            lightboxImage.alt = '';
        }
    };

    const openLightbox = (src, alt) => {
        if (!lightbox || !lightboxImage || !src) return;
        lightboxImage.src = src;
        lightboxImage.alt = alt || 'Program photo';
        if (lightboxCaption) {
            lightboxCaption.textContent = alt || '';
        }
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    document.querySelectorAll('.program-report-photo').forEach((button) => {
        button.addEventListener('click', () => {
            openLightbox(button.dataset.photoSrc, button.dataset.photoAlt);
        });
    });

    lightboxClose?.addEventListener('click', closeLightbox);
    lightbox?.addEventListener('click', (event) => {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && lightbox && !lightbox.hidden) {
            closeLightbox();
        }
    });
});
