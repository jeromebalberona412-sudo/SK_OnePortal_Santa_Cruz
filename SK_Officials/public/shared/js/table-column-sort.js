(function (global) {
    'use strict';

    let sharedMenu = null;
    let menuAnchor = null;
    let menuApi = null;

    function ensureMenu() {
        if (sharedMenu) {
            return sharedMenu;
        }

        sharedMenu = document.createElement('div');
        sharedMenu.id = 'skTableSortMenu';
        sharedMenu.className = 'sk-sort-menu';
        sharedMenu.hidden = true;
        sharedMenu.setAttribute('role', 'menu');
        sharedMenu.setAttribute('aria-label', 'Sort options');
        document.body.appendChild(sharedMenu);

        document.addEventListener('click', (event) => {
            if (!sharedMenu || sharedMenu.hidden) {
                return;
            }
            if (sharedMenu.contains(event.target) || menuAnchor?.contains(event.target)) {
                return;
            }
            closeMenu();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        window.addEventListener('scroll', () => closeMenu(), true);
        window.addEventListener('resize', () => closeMenu());

        return sharedMenu;
    }

    function closeMenu() {
        if (!sharedMenu) {
            return;
        }
        sharedMenu.hidden = true;
        if (menuAnchor) {
            menuAnchor.setAttribute('aria-expanded', 'false');
        }
        menuAnchor = null;
        menuApi = null;
    }

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

    function sortOptionsFor(column, numericColumns, dateColumns, plainColumns) {
        if (plainColumns.has(column)) {
            return [
                { dir: 'asc', label: 'Sort ascending', hint: '', icon: '↑' },
                { dir: 'desc', label: 'Sort descending', hint: '', icon: '↓' },
            ];
        }

        if (dateColumns.has(column)) {
            return [
                { dir: 'asc', label: 'Sort ascending', hint: 'Oldest → Newest', icon: '↑' },
                { dir: 'desc', label: 'Sort descending', hint: 'Newest → Oldest', icon: '↓' },
            ];
        }

        if (numericColumns.has(column)) {
            return [
                { dir: 'asc', label: 'Sort ascending', hint: '1 → 9', icon: '↑' },
                { dir: 'desc', label: 'Sort descending', hint: '9 → 1', icon: '↓' },
            ];
        }

        return [
            { dir: 'asc', label: 'Sort ascending', hint: 'A → Z', icon: '↑' },
            { dir: 'desc', label: 'Sort descending', hint: 'Z → A', icon: '↓' },
        ];
    }

    function mount(options) {
        const columnKeys = options.columnKeys || [];
        const skipThClasses = new Set(options.skipThClasses || ['th-checkbox', 'col-actions', 'sk-no-sort']);
        const numericColumns = new Set(options.numericColumns || []);
        const dateColumns = new Set(options.dateColumns || []);
        const plainColumns = new Set(options.plainSortColumns || []);
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
                header.setAttribute('aria-sort', 'none');
                if (header.dataset.sort === sortColumn) {
                    header.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
                    header.setAttribute('aria-sort', sortDirection === 'asc' ? 'ascending' : 'descending');
                }
            });
        }

        function applySort(column, direction) {
            sortColumn = column;
            sortDirection = direction;
            updateHeaderState();
            if (typeof options.onSort === 'function') {
                options.onSort();
            }
        }

        function openMenu(anchor, column) {
            const menu = ensureMenu();
            const currentDir = sortColumn === column ? sortDirection : null;

            menu.innerHTML = '';
            sortOptionsFor(column, numericColumns, dateColumns, plainColumns).forEach((opt) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'sk-sort-option' + (currentDir === opt.dir ? ' is-active' : '');
                btn.setAttribute('role', 'menuitem');
                const hintHtml = opt.hint
                    ? `<span class="sk-sort-option-hint">${opt.hint}</span>`
                    : '';
                btn.innerHTML = `<span class="sk-sort-option-icon">${opt.icon}</span><span class="sk-sort-option-text"><span class="sk-sort-option-label">${opt.label}</span>${hintHtml}</span>`;
                btn.addEventListener('click', () => {
                    applySort(column, opt.dir);
                    closeMenu();
                });
                menu.appendChild(btn);
            });

            const rect = anchor.getBoundingClientRect();
            menu.hidden = false;
            menu.style.top = `${rect.bottom + 4}px`;
            menu.style.left = `${Math.min(rect.left, window.innerWidth - 260)}px`;
            menuAnchor = anchor;
            menuApi = { applySort };
            anchor.setAttribute('aria-expanded', 'true');
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

            if (!headerRow || headerRow.dataset.skSortReady === '1') {
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
                header.setAttribute('aria-sort', 'none');
                header.innerHTML = `
                    <button type="button" class="sk-sort-btn" data-sort="${sortKey}" aria-label="Sort column" aria-haspopup="menu" aria-expanded="false">
                        <span class="sk-sort-label">${labelHtml}</span>
                        <span class="sk-sort-icon" aria-hidden="true"></span>
                    </button>
                `;
            });

            headerRow.addEventListener('click', (event) => {
                const button = event.target.closest('.sk-sort-btn');
                if (!button || !headerRow.contains(button)) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const column = button.dataset.sort;
                if (!column) {
                    return;
                }

                if (menuAnchor === button && sharedMenu && !sharedMenu.hidden) {
                    closeMenu();
                    return;
                }

                closeMenu();
                openMenu(button, column);
            });

            headerRow.dataset.skSortReady = '1';
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
