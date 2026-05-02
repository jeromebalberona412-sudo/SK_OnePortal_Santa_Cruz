'use strict';

let bmStatus = 'all';
let bmSearch = '';

function bmApplyFilters() {
    const cards = document.querySelectorAll('.bm-list-item');
    const empty = document.getElementById('bm-empty');
    let visible = 0;

    cards.forEach((card) => {
        const status = card.getAttribute('data-status');
        const name = card.getAttribute('data-name') || '';

        const statusMatch = bmStatus === 'all' || bmStatus === status;
        const searchMatch = !bmSearch || name.includes(bmSearch);
        const show = statusMatch && searchMatch;

        card.hidden = !show;
        if (show) {
            visible += 1;
        }
    });

    if (empty) {
        empty.hidden = visible > 0;
    }
}

function bmSetStatus(status, el) {
    bmStatus = status;
    document.querySelectorAll('.bm-chip[data-status]').forEach((chip) => chip.classList.remove('active'));
    el?.classList.add('active');
    bmApplyFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('bm-search');
    if (search) {
        search.addEventListener('input', (event) => {
            bmSearch = String(event.target.value || '').trim().toLowerCase();
            bmApplyFilters();
        });
    }

    bmApplyFilters();
});
