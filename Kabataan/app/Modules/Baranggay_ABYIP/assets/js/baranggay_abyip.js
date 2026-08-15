document.addEventListener('DOMContentLoaded', () => {
    initBarangayAbyipSearch();
    initBarangayAbyipViewer();
});

function initBarangayAbyipSearch() {
    const searchInput = document.getElementById('barangayAbyipSearch');
    const grid = document.getElementById('barangayAbyipGrid');
    const resultCount = document.getElementById('barangayAbyipResultCount');
    const filterEmpty = document.getElementById('barangayAbyipFilterEmpty');

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
}

function initBarangayAbyipViewer() {
    const page = document.querySelector('[data-abyip-documents-url]');
    if (!page) {
        return;
    }

    const documentsUrl = page.getAttribute('data-abyip-documents-url');
    const statusEl = document.getElementById('barangayAbyipStatus');
    const pagesEl = document.getElementById('barangayAbyipPages');
    const yearSelect = document.getElementById('barangayAbyipYear');

    if (!documentsUrl || !statusEl || !pagesEl || !yearSelect) {
        return;
    }

    const documentsByYear = new Map();
    let renderToken = 0;
    let loadPromise = null;

    const uniqueByYear = (items) => {
        items.forEach((item) => {
            const year = String(item.year || '');
            if (!year || documentsByYear.has(year)) {
                return;
            }
            documentsByYear.set(year, item);
        });
    };

    const fillYearOptions = () => {
        const years = Array.from(documentsByYear.keys()).sort((a, b) => Number(b) - Number(a));
        yearSelect.innerHTML = '';

        if (years.length === 0) {
            yearSelect.innerHTML = '<option value="">No years available</option>';
            yearSelect.disabled = true;
            return;
        }

        years.forEach((year) => {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = 'CY ' + year;
            yearSelect.appendChild(option);
        });

        yearSelect.disabled = false;
        yearSelect.value = years[0];
    };

    const renderPdf = async (fileUrl) => {
        const token = ++renderToken;
        statusEl.hidden = false;
        statusEl.textContent = 'Opening ABYIP...';
        pagesEl.innerHTML = '';

        if (!window.pdfjsLib) {
            throw new Error('PDF viewer failed to load.');
        }

        const pdf = await window.pdfjsLib.getDocument({
            url: fileUrl,
            withCredentials: true,
        }).promise;

        if (token !== renderToken) {
            return;
        }

        const width = Math.max(pagesEl.clientWidth || page.clientWidth || 800, 280);

        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
            const pdfPage = await pdf.getPage(pageNumber);
            if (token !== renderToken) {
                return;
            }

            const unscaled = pdfPage.getViewport({ scale: 1 });
            const outputScale = window.devicePixelRatio || 1;
            const scale = width / unscaled.width;
            const viewport = pdfPage.getViewport({ scale: scale * outputScale });
            const canvas = document.createElement('canvas');
            canvas.className = 'baranggay-abyip-page-sheet';
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            canvas.style.width = '100%';
            canvas.style.height = 'auto';

            await pdfPage.render({
                canvasContext: canvas.getContext('2d'),
                viewport,
            }).promise;

            if (token !== renderToken) {
                return;
            }

            pagesEl.appendChild(canvas);
        }

        statusEl.hidden = true;
    };

    const openYear = (year) => {
        const item = documentsByYear.get(String(year));
        if (!item || !item.file_url) {
            statusEl.hidden = false;
            statusEl.textContent = 'No ABYIP uploaded for that year.';
            pagesEl.innerHTML = '';
            return;
        }

        renderPdf(item.file_url).catch(() => {
            statusEl.hidden = false;
            statusEl.textContent = 'Unable to open the ABYIP document right now. Please try again.';
        });
    };

    yearSelect.addEventListener('change', () => {
        openYear(yearSelect.value);
    });

    if (loadPromise) {
        return;
    }

    loadPromise = fetch(documentsUrl, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Failed to load ABYIP documents.');
            }
            return response.json();
        })
        .then((payload) => {
            const items = Array.isArray(payload.data) ? payload.data : [];
            uniqueByYear(items);
            fillYearOptions();

            if (documentsByYear.size === 0) {
                statusEl.textContent = 'No ABYIP uploaded yet for this barangay.';
                return;
            }

            openYear(yearSelect.value);
        })
        .catch(() => {
            yearSelect.innerHTML = '<option value="">Unavailable</option>';
            yearSelect.disabled = true;
            statusEl.textContent = 'Unable to open the ABYIP document right now. Please try again.';
        });
}
