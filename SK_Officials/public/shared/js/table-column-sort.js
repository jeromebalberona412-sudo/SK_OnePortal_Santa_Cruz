(function (global) {
    'use strict';

    function parseDateSortValue(value) {
        const text = String(value ?? '').trim();
        if (!text || text === '—' || text === '-') {
            return 0;
        }

        const slashMatch = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/.exec(text);
        if (slashMatch) {
            const [, month, day, year] = slashMatch;
            return new Date(Number(year), Number(month) - 1, Number(day)).getTime() || 0;
        }

        const parsed = Date.parse(text);
        return Number.isNaN(parsed) ? 0 : parsed;
    }

    function compareValues(left, right, column, numericColumns, dateColumns) {
        if (dateColumns.has(column)) {
            return parseDateSortValue(left) - parseDateSortValue(right);
        }

        if (numericColumns.has(column)) {
            const a = Number(left) || 0;
            const b = Number(right) || 0;
            return a - b;
        }

        return String(left ?? '').localeCompare(String(right ?? ''), undefined, {
            sensitivity: 'base',
            numeric: true,
        });
    }

    function mount(options) {
        const columnKeys = options.columnKeys || [];
        const skipThClasses = new Set(options.skipThClasses || ['th-checkbox', 'col-actions']);
        const numericColumns = new Set(options.numericColumns || []);
        const dateColumns = new Set(options.dateColumns || []);
        const getSortValue = options.getSortValue || ((row, column) => row[column]);
        const tieBreaker = options.tieBreaker || null;

        let sortColumn = options.defaultColumn || columnKeys[0] || null;
        let sortDirection = options.defaultDirection || 'asc';
        let headerRow = null;

        function updateHeaderState() {
            if (!headerRow) {
                return;
            }

            headerRow.querySelectorAll('.sk-sortable').forEach((header) => {
                header.classList.remove('sort-asc', 'sort-desc');
                if (header.dataset.sort === sortColumn) {
                    header.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
                }
            });
        }

        function sortRows(rows) {
            if (!sortColumn || !Array.isArray(rows)) {
                return rows;
            }

            const direction = sortDirection === 'desc' ? -1 : 1;
            const sorted = rows.slice();

            sorted.sort((left, right) => {
                const result = compareValues(
                    getSortValue(left, sortColumn),
                    getSortValue(right, sortColumn),
                    sortColumn,
                    numericColumns,
                    dateColumns
                );

                if (result !== 0) {
                    return result * direction;
                }

                if (typeof tieBreaker === 'function') {
                    return tieBreaker(left, right, direction);
                }

                return 0;
            });

            return sorted;
        }

        function initHeaders(target) {
            headerRow = typeof target === 'string'
                ? document.querySelector(target)
                : target;

            if (!headerRow) {
                return;
            }

            const headers = Array.from(headerRow.querySelectorAll('th'));
            let keyIndex = 0;

            headers.forEach((header) => {
                if ([...skipThClasses].some((cls) => header.classList.contains(cls))) {
                    return;
                }

                const sortKey = columnKeys[keyIndex];
                keyIndex += 1;

                if (!sortKey) {
                    return;
                }

                const labelHtml = header.innerHTML.trim();
                header.classList.add('sk-sortable');
                header.dataset.sort = sortKey;
                header.innerHTML = `
                    <button type="button" class="sk-sort-btn" data-sort="${sortKey}" aria-label="Sort column">
                        <span class="sk-sort-label">${labelHtml}</span>
                        <span class="sk-sort-icon" aria-hidden="true"></span>
                    </button>
                `;
            });

            headerRow.addEventListener('click', (event) => {
                const button = event.target.closest('.sk-sort-btn');
                if (!button) {
                    return;
                }

                const column = button.dataset.sort;
                if (!column) {
                    return;
                }

                if (sortColumn === column) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColumn = column;
                    sortDirection = 'asc';
                }

                updateHeaderState();

                if (typeof options.onSort === 'function') {
                    options.onSort();
                }
            });

            updateHeaderState();
        }

        return {
            initHeaders,
            sortRows,
            getState: () => ({ sortColumn, sortDirection }),
            setColumn: (column, direction) => {
                sortColumn = column;
                sortDirection = direction || 'asc';
                updateHeaderState();
            },
        };
    }

    global.SkTableSort = { mount };
})(window);
