(function () {
    'use strict';

    var sortMenu = null;
    var sortMenuAnchor = null;

    function closeKmSortMenu() {
        if (sortMenu) sortMenu.hidden = true;
        sortMenuAnchor = null;
        document.querySelectorAll('.km-sort-btn').forEach(function (btn) {
            btn.setAttribute('aria-expanded', 'false');
        });
    }

    function updateSortHeaderState(key, dir) {
        document.querySelectorAll('.km-th-sortable').forEach(function (th) {
            th.classList.remove('is-sorted-asc', 'is-sorted-desc');
            th.setAttribute('aria-sort', 'none');
        });
        var th = document.querySelector('.km-th-sortable[data-sort-key="' + key + '"]');
        if (th && dir) {
            th.classList.add(dir === 'asc' ? 'is-sorted-asc' : 'is-sorted-desc');
            th.setAttribute('aria-sort', dir === 'asc' ? 'ascending' : 'descending');
        }
    }

    function openKmSortMenu(anchor, key) {
        if (!sortMenu || !anchor) return;
        sortMenuAnchor = anchor;
        var th = anchor.closest('.km-th-sortable');
        var currentDir = th?.classList.contains('is-sorted-asc') ? 'asc'
            : th?.classList.contains('is-sorted-desc') ? 'desc' : null;

        sortMenu.innerHTML = '';
        [
            { dir: 'asc', label: 'Sort ascending', hint: 'A → Z', icon: '↑' },
            { dir: 'desc', label: 'Sort descending', hint: 'Z → A', icon: '↓' },
        ].forEach(function (opt) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'km-sort-option' + (currentDir === opt.dir ? ' is-active' : '');
            btn.setAttribute('role', 'menuitem');
            btn.innerHTML = '<span class="km-sort-option-icon">' + opt.icon + '</span><span class="km-sort-option-text"><span class="km-sort-option-label">' + opt.label + '</span><span class="km-sort-option-hint">' + opt.hint + '</span></span>';
            btn.addEventListener('click', function () {
                if (typeof window.kmApplySort === 'function') {
                    window.kmApplySort(key, opt.dir);
                }
                updateSortHeaderState(key, opt.dir);
                closeKmSortMenu();
            });
            sortMenu.appendChild(btn);
        });

        var rect = anchor.getBoundingClientRect();
        sortMenu.hidden = false;
        sortMenu.style.top = (rect.bottom + 4) + 'px';
        sortMenu.style.left = rect.left + 'px';
        anchor.setAttribute('aria-expanded', 'true');
    }

    function csvEscape(value) {
        var text = String(value ?? '');
        if (/[",\n]/.test(text)) {
            return '"' + text.replace(/"/g, '""') + '"';
        }
        return text;
    }

    function exportFileName(ext) {
        var barangay = String(window.kmBarangay || 'kabataan-monitoring').replace(/[^\w\- ]+/g, '').trim() || 'kabataan';
        return barangay.toLowerCase().replace(/\s+/g, '-') + '-kabataan-monitoring.' + ext;
    }

    function downloadBlob(content, mime, filename) {
        var blob = new Blob([content], { type: mime });
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    }

    function exportRows(format) {
        if (typeof window.kmGetExportRows !== 'function') return;
        var rows = window.kmGetExportRows();
        if (!rows.length) {
            alert('No table records to export.');
            return;
        }

        var headers = ['Respondent #', 'Full Name', 'Age', 'Barangay', 'Purok/Zone', 'Registered Voter'];
        var data = rows.map(function (row) {
            return [
                row.respondentNumber || '—',
                row.fullName || '—',
                row.age || '—',
                row.barangay || '—',
                row.purokZone || '—',
                row.registeredVoter || '—',
            ];
        });

        if (format === 'csv') {
            var csv = [headers].concat(data).map(function (line) {
                return line.map(csvEscape).join(',');
            }).join('\r\n');
            downloadBlob('\uFEFF' + csv, 'text/csv;charset=utf-8;', exportFileName('csv'));
            return;
        }

        if (typeof XLSX === 'undefined') {
            alert('Excel export is unavailable. Please refresh the page and try again.');
            return;
        }

        var sheet = XLSX.utils.aoa_to_sheet([headers].concat(data));
        var workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, sheet, 'Kabataan Monitoring');
        XLSX.writeFile(workbook, exportFileName('xlsx'));
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.kmPageMode !== 'barangay-detail') return;

        sortMenu = document.getElementById('kmSortMenu');

        document.querySelectorAll('.km-th-sortable .km-sort-btn').forEach(function (btn) {
            btn.addEventListener('click', function (event) {
                event.stopPropagation();
                var th = btn.closest('.km-th-sortable');
                if (!th) return;
                if (sortMenuAnchor === btn && sortMenu && !sortMenu.hidden) {
                    closeKmSortMenu();
                    return;
                }
                closeKmSortMenu();
                openKmSortMenu(btn, th.dataset.sortKey);
            });
        });

        document.addEventListener('click', function (event) {
            if (sortMenu && !sortMenu.hidden && !sortMenu.contains(event.target) && !sortMenuAnchor?.contains(event.target)) {
                closeKmSortMenu();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeKmSortMenu();
        });

        document.getElementById('km-export-excel-btn')?.addEventListener('click', function () {
            exportRows('xlsx');
        });
        document.getElementById('km-export-csv-btn')?.addEventListener('click', function () {
            exportRows('csv');
        });
    });
})();
