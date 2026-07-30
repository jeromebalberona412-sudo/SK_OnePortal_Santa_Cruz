(function () {
    'use strict';

    function showLoading() {
        if (window.showLoading) window.showLoading('Saving');
        else document.getElementById('loadingOverlay')?.classList.remove('hidden');
    }

    function hideLoading() {
        if (window.hideLoading) window.hideLoading();
        else document.getElementById('loadingOverlay')?.classList.add('hidden');
    }

    function showNotification(message, type) {
        var container = document.getElementById('notificationContainer');
        if (!container) return;
        var el = document.createElement('div');
        el.className = 'notification notification-' + (type || 'info');
        el.textContent = message;
        container.appendChild(el);
        setTimeout(function () { el.remove(); }, 4000);
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatYmd(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toISOString().slice(0, 10);
    }

    // ── Budget utilization ──
    function updateBudgetDisplay() {
        var allocated = parseFloat(document.getElementById('field_budget_allocated')?.value) || 0;
        var expense = parseFloat(document.getElementById('actual_expense')?.value) || 0;
        var remaining = allocated - expense;
        var utilization = allocated > 0 ? (expense / allocated) * 100 : 0;

        document.getElementById('util_allocated').textContent = allocated.toFixed(2);
        document.getElementById('util_expense').textContent = expense.toFixed(2);
        document.getElementById('util_remaining').textContent = Math.max(0, remaining).toFixed(2);
        document.getElementById('util_percent').textContent = utilization.toFixed(2);

        var fill = document.getElementById('util_progress_fill');
        if (fill) fill.style.width = Math.min(utilization, 100).toFixed(1) + '%';

        var errorEl = document.getElementById('budgetError');
        if (errorEl) {
            if (expense > allocated) {
                errorEl.textContent = 'Actual expense cannot exceed the allocated budget.';
                errorEl.style.display = 'block';
            } else {
                errorEl.style.display = 'none';
            }
        }
    }

    document.addEventListener('input', function (e) {
        if (e.target.id === 'actual_expense') updateBudgetDisplay();
    });

    // ── Program search & auto-fill ──
    var programSearchTimer = null;
    var selectedProgramData = null;

    function initProgramSearch() {
        var searchInput = document.getElementById('program_search');
        var resultsDropdown = document.getElementById('programSearchResults');
        var selectedInfo = document.getElementById('selectedProgramInfo');
        var changeBtn = document.getElementById('changeProgramBtn');
        var autoCard = document.getElementById('autoDetailsCard');
        var manualCard = document.getElementById('manualInputsCard');
        var mediaCard = document.getElementById('mediaUploadCard');
        var formActions = document.getElementById('formActions');

        if (!searchInput || !resultsDropdown) return;

        function showProgramData(program) {
            selectedProgramData = program;

            document.getElementById('field_title').value = program.program_name || program.activity_name || '';
            document.getElementById('field_description').value = program.description || '';
            document.getElementById('field_objectives').value = program.expected_result || '';
            document.getElementById('field_date_started').value = formatYmd(program.implementation_start);
            document.getElementById('field_date_completed').value = formatYmd(program.implementation_end);
            document.getElementById('field_person_responsible').value = program.person_responsible || '';
            document.getElementById('field_budget_allocated').value = parseFloat(program.total) || 0;

            document.getElementById('program_id').value = program.id;

            document.getElementById('selectedProgramName').textContent = program.program_name || program.activity_name || 'Untitled';
            document.getElementById('selectedProgramCategory').textContent = program.category || (program.row_type || '').replace('_', ' ').toUpperCase() || 'PROGRAM';
            document.getElementById('selectedProgramBudget').textContent = (parseFloat(program.total) || 0).toFixed(2);
            selectedInfo.style.display = 'block';

            document.getElementById('auto_description').textContent = program.description || '—';
            document.getElementById('auto_expected_result').textContent = program.expected_result || '—';
            document.getElementById('auto_date_started').textContent = formatDate(program.implementation_start);
            document.getElementById('auto_date_completed').textContent = formatDate(program.implementation_end);
            document.getElementById('auto_person_responsible').textContent = program.person_responsible || '—';
            document.getElementById('auto_mooe').innerHTML = '&#8369;' + (parseFloat(program.mooe) || 0).toFixed(2);
            document.getElementById('auto_co').innerHTML = '&#8369;' + (parseFloat(program.co) || 0).toFixed(2);
            document.getElementById('auto_budget_allocated').innerHTML = '&#8369;' + (parseFloat(program.total) || 0).toFixed(2);

            autoCard.style.display = 'block';
            manualCard.style.display = 'block';
            mediaCard.style.display = 'block';
            formActions.style.display = 'flex';

            updateBudgetDisplay();

            if (!document.getElementById('actual_expense').value || document.getElementById('actual_expense').value === '0.00') {
                document.getElementById('actual_expense').value = (parseFloat(program.total) || 0).toFixed(2);
                updateBudgetDisplay();
            }

            resultsDropdown.classList.remove('active');
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(programSearchTimer);
            var query = this.value.trim();
            if (query.length < 2) {
                resultsDropdown.classList.remove('active');
                return;
            }
            programSearchTimer = setTimeout(function () {
                fetch('/api/program-accomplishment/search-programs?search=' + encodeURIComponent(query))
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        resultsDropdown.innerHTML = '';
                        var programs = data.data || [];
                        if (programs.length === 0) {
                            resultsDropdown.innerHTML = '<div class="search-result-item" style="color:#9ca3af;">No programs found</div>';
                            resultsDropdown.classList.add('active');
                            return;
                        }
                        programs.forEach(function (p) {
                            var item = document.createElement('div');
                            item.className = 'search-result-item';
                            var name = p.program_name || p.activity_name || p.description || 'Untitled';
                            var cat = p.category || (p.row_type || '').replace('_', ' ').toUpperCase() || '';
                            item.innerHTML = '<strong>' + escapeHtml(name) + '</strong>' +
                                (cat ? ' <span class="result-sub"> &middot; ' + escapeHtml(cat) + '</span>' : '') +
                                ' <span class="result-sub"> &middot; &#8369;' + (parseFloat(p.total) || 0).toFixed(2) + '</span>';
                            item.addEventListener('click', function () {
                                showProgramData(p);
                            });
                            resultsDropdown.appendChild(item);
                        });
                        resultsDropdown.classList.add('active');
                    })
                    .catch(function () {
                        resultsDropdown.innerHTML = '<div class="search-result-item" style="color:#dc2626;">Search failed</div>';
                        resultsDropdown.classList.add('active');
                    });
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
                resultsDropdown.classList.remove('active');
            }
        });

        if (changeBtn) {
            changeBtn.addEventListener('click', function () {
                selectedProgramData = null;
                selectedInfo.style.display = 'none';
                autoCard.style.display = 'none';
                manualCard.style.display = 'none';
                mediaCard.style.display = 'none';
                formActions.style.display = 'none';
                searchInput.value = '';
                searchInput.focus();
                document.getElementById('program_id').value = '';
                document.getElementById('field_title').value = '';
                document.getElementById('field_description').value = '';
                document.getElementById('field_objectives').value = '';
                document.getElementById('field_date_started').value = '';
                document.getElementById('field_date_completed').value = '';
                document.getElementById('field_person_responsible').value = '';
                document.getElementById('field_budget_allocated').value = '0';
            });
        }
    }

    // ── Cloudinary Upload ──
    var uploadType = 'image';

    function initCloudinaryUpload() {
        var btns = document.querySelectorAll('[data-cld-upload]');
        if (btns.length === 0) return;

        var cloudName = btns[0].getAttribute('data-cloud-name');
        var uploadPreset = btns[0].getAttribute('data-upload-preset');

        var widget = cloudinary.createUploadWidget({
            cloudName: cloudName,
            uploadPreset: uploadPreset,
            folder: 'Accomplishment_Report',
            multiple: false,
            maxFiles: 1,
            sources: ['local', 'camera', 'url', 'google_drive'],
        }, function (error, result) {
            if (error) {
                showNotification('Upload failed: ' + (error.statusText || 'Unknown error'), 'error');
                return;
            }
            if (result.event === 'success') {
                var info = result.info;
                if (uploadType === 'image') {
                    document.getElementById('image_name').value = info.public_id || '';
                    document.getElementById('image_path').value = info.secure_url || '';
                    document.getElementById('image_type').value = info.format || '';
                    document.getElementById('image_size').value = info.bytes || 0;
                    showImagePreview(info.secure_url);
                } else {
                    document.getElementById('file_name').value = (info.original_filename || 'file') + '.' + (info.format || 'bin');
                    document.getElementById('file_path').value = info.secure_url || '';
                    document.getElementById('file_type').value = info.format || '';
                    document.getElementById('file_size').value = info.bytes || 0;
                    showFilePreview((info.original_filename || 'file') + '.' + (info.format || 'bin'));
                }
                showNotification('Upload successful', 'success');
            }
        });

        btns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                uploadType = this.getAttribute('data-cld-upload') || 'image';
                widget.open();
            });
        });
    }

    function showImagePreview(url) {
        var container = document.getElementById('imagePreviewContainer');
        if (!container) return;
        container.innerHTML = '';
        var div = document.createElement('div');
        div.className = 'preview-file';
        var img = document.createElement('img');
        img.src = url;
        img.alt = 'Uploaded image';
        div.appendChild(img);
        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'preview-remove';
        removeBtn.innerHTML = '&times;';
        removeBtn.addEventListener('click', function () {
            document.getElementById('image_name').value = '';
            document.getElementById('image_path').value = '';
            document.getElementById('image_type').value = '';
            document.getElementById('image_size').value = '';
            container.innerHTML = '';
        });
        div.appendChild(removeBtn);
        container.appendChild(div);
    }

    function showFilePreview(name) {
        var container = document.getElementById('filePreviewContainer');
        if (!container) return;
        container.innerHTML = '';
        var div = document.createElement('div');
        div.className = 'preview-file preview-file-doc';
        div.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>' +
            '<span class="preview-filename">' + escapeHtml(name) + '</span>' +
            '<button type="button" class="preview-remove" onclick="this.closest(\'.preview-file\').remove();document.getElementById(\'file_name\').value=\'\';document.getElementById(\'file_path\').value=\'\';document.getElementById(\'file_type\').value=\'\';document.getElementById(\'file_size\').value=\'\';">&times;</button>';
        container.appendChild(div);
    }

    // ── Modal management ──
    function initCreateModal() {
        var modal = document.getElementById('createReportModal');
        var closeBtn = document.getElementById('modalCloseBtn');
        var cancelBtn = document.getElementById('modalCancelBtn');
        var openBtn = document.getElementById('openCreateModalBtn');
        if (!modal) return;

        function loadPendingPrograms() {
            fetch('/api/program-accomplishment/pending-programs', { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (result) {
                    var programs = result.data || [];
                    if (programs.length === 0) return;
                    if (programs.length === 1) {
                        autoSelectProgram(programs[0]);
                    } else {
                        showPendingProgramsList(programs);
                    }
                })
                .catch(function () {});
        }

        function openModal() {
            modal.style.display = 'flex';
            document.body.classList.add('modal-open');
            loadPendingPrograms();
            var search = document.getElementById('program_search');
            if (search) setTimeout(function () { search.focus(); }, 200);
        }

        openBtn?.addEventListener('click', openModal);

        function closeModal() {
            modal.style.display = 'none';
            document.body.classList.remove('modal-open');
            resetForm();
        }

        function resetForm() {
            selectedProgramData = null;
            document.getElementById('program_id').value = '';
            document.getElementById('program_search').value = '';
            document.getElementById('selectedProgramInfo').style.display = 'none';
            document.getElementById('autoDetailsCard').style.display = 'none';
            document.getElementById('manualInputsCard').style.display = 'none';
            document.getElementById('mediaUploadCard').style.display = 'none';
            document.getElementById('formActions').style.display = 'none';
            document.getElementById('imagePreviewContainer').innerHTML = '';
            document.getElementById('filePreviewContainer').innerHTML = '';
            ['field_title','field_description','field_objectives','field_date_started','field_date_completed',
             'field_person_responsible','field_budget_allocated','actual_expense','participants_count',
             'venue','implementation_summary','lessons_learned','recommendations','remarks',
             'image_name','image_path','image_type','image_size','file_name','file_path','file_type','file_size',
             'image_caption'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.value = '';
            });
        }

        closeBtn?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
        });

        // Auto-open on page load if pending programs exist
        fetch('/api/program-accomplishment/pending-programs', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                var programs = result.data || [];
                if (programs.length > 0) openModal();
            })
            .catch(function () {});
    }

    function autoSelectProgram(program) {
        var name = program.program_name || program.activity_name || 'Untitled';
        var cat = program.category || (program.row_type || '').replace('_', ' ').toUpperCase() || '';
        var searchInput = document.getElementById('program_search');
        var resultsDropdown = document.getElementById('programSearchResults');
        if (!searchInput || !resultsDropdown) return;

        selectedProgramData = program;

        document.getElementById('field_title').value = program.program_name || program.activity_name || '';
        document.getElementById('field_description').value = program.description || '';
        document.getElementById('field_objectives').value = program.expected_result || '';
        document.getElementById('field_date_started').value = formatYmd(program.implementation_start);
        document.getElementById('field_date_completed').value = formatYmd(program.implementation_end);
        document.getElementById('field_person_responsible').value = program.person_responsible || '';
        document.getElementById('field_budget_allocated').value = parseFloat(program.total) || 0;
        document.getElementById('program_id').value = program.id;

        document.getElementById('selectedProgramName').textContent = name;
        document.getElementById('selectedProgramCategory').textContent = cat || 'PROGRAM';
        document.getElementById('selectedProgramBudget').textContent = (parseFloat(program.total) || 0).toFixed(2);
        document.getElementById('selectedProgramInfo').style.display = 'block';

        document.getElementById('auto_description').textContent = program.description || '—';
        document.getElementById('auto_expected_result').textContent = program.expected_result || '—';
        document.getElementById('auto_date_started').textContent = formatDate(program.implementation_start);
        document.getElementById('auto_date_completed').textContent = formatDate(program.implementation_end);
        document.getElementById('auto_person_responsible').textContent = program.person_responsible || '—';
        document.getElementById('auto_mooe').innerHTML = '&#8369;' + (parseFloat(program.mooe) || 0).toFixed(2);
        document.getElementById('auto_co').innerHTML = '&#8369;' + (parseFloat(program.co) || 0).toFixed(2);
        document.getElementById('auto_budget_allocated').innerHTML = '&#8369;' + (parseFloat(program.total) || 0).toFixed(2);

        document.getElementById('autoDetailsCard').style.display = 'block';
        document.getElementById('manualInputsCard').style.display = 'block';
        document.getElementById('mediaUploadCard').style.display = 'block';
        document.getElementById('formActions').style.display = 'flex';

        updateBudgetDisplay();

        if (!document.getElementById('actual_expense').value || document.getElementById('actual_expense').value === '0.00') {
            document.getElementById('actual_expense').value = (parseFloat(program.total) || 0).toFixed(2);
            updateBudgetDisplay();
        }

        searchInput.value = name;
        resultsDropdown.classList.remove('active');
    }

    function showPendingProgramsList(programs) {
        var resultsDropdown = document.getElementById('programSearchResults');
        if (!resultsDropdown) return;
        resultsDropdown.innerHTML = '<div class="search-result-item" style="color:#6b7280;font-size:0.8rem;border-bottom:1px solid #e5e7eb;">Pending programs without a report:</div>';
        programs.forEach(function (p) {
            var item = document.createElement('div');
            item.className = 'search-result-item';
            var name = p.program_name || p.activity_name || p.description || 'Untitled';
            var cat = p.category || (p.row_type || '').replace('_', ' ').toUpperCase() || '';
            item.innerHTML = '<strong>' + escapeHtml(name) + '</strong>' +
                (cat ? ' <span class="result-sub"> &middot; ' + escapeHtml(cat) + '</span>' : '') +
                ' <span class="result-sub"> &middot; &#8369;' + (parseFloat(p.total) || 0).toFixed(2) + '</span>';
            item.addEventListener('click', function () {
                autoSelectProgram(p);
            });
            resultsDropdown.appendChild(item);
        });
        resultsDropdown.classList.add('active');
    }

    // ── Form submission ──
    function initFormSubmission() {
        var form = document.getElementById('accomplishmentForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var budgetError = document.getElementById('budgetError');
            if (budgetError && budgetError.style.display !== 'none') {
                showNotification('Please fix budget errors before saving.', 'error');
                return;
            }

            var method = form.querySelector('input[name="_method"]')?.value || 'POST';
            var isEdit = window.location.pathname.includes('/edit/');
            var idMatch = isEdit ? window.location.pathname.match(/\/edit\/(\d+)/) : null;
            var url = '/api/program-accomplishment' + (idMatch ? '/' + idMatch[1] : '');

            var formData = new FormData(form);
            if (method === 'PUT') formData.set('_method', 'PUT');

            showLoading();

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                body: formData,
            })
            .then(function (r) { return r.json().then(function (d) { return { status: r.status, data: d }; }); })
            .then(function (result) {
                hideLoading();
                if (result.status >= 200 && result.status < 300) {
                    showNotification(result.data.message || 'Saved successfully.', 'success');
                    if (isEdit && result.data.data?.id) {
                        window.location.href = '/program-accomplishment/' + result.data.data.id;
                    } else {
                        // Modal mode on index page — close modal & refresh table
                        var modal = document.getElementById('createReportModal');
                        if (modal) {
                            modal.style.display = 'none';
                            document.body.classList.remove('modal-open');
                        }
                        // Reset form
                        document.getElementById('accomplishmentForm')?.reset();
                        document.getElementById('selectedProgramInfo').style.display = 'none';
                        document.getElementById('autoDetailsCard').style.display = 'none';
                        document.getElementById('manualInputsCard').style.display = 'none';
                        document.getElementById('mediaUploadCard').style.display = 'none';
                        document.getElementById('formActions').style.display = 'none';
                        // Refresh the table
                        if (window._loadReports) window._loadReports();
                    }
                } else {
                    var msg = result.data.message || 'Failed to save.';
                    if (result.data.errors) msg = Object.values(result.data.errors).flat().join(' ');
                    showNotification(msg, 'error');
                }
            })
            .catch(function () {
                hideLoading();
                showNotification('Network error. Please try again.', 'error');
            });
        });
    }

    // ── Index page ──
    function initIndexPage() {
        var tbody = document.getElementById('reportsTableBody');
        var statusFilter = document.getElementById('statusFilter');
        var searchInput = document.getElementById('searchInput');
        var footer = document.getElementById('tableFooter');
        if (!tbody) return;

        var currentPage = 1;

        window._loadReports = function () {
            var params = new URLSearchParams({ page: currentPage, per_page: 15 });
            if (statusFilter?.value) params.set('status', statusFilter.value);
            if (searchInput?.value) params.set('search', searchInput.value);

            showLoading();
            fetch('/api/program-accomplishment?' + params.toString(), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                hideLoading();
                tbody.innerHTML = '';
                var rows = result.data || [];
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No reports found.</td></tr>';
                    if (footer) footer.innerHTML = '';
                    return;
                }
                rows.forEach(function (r) {
                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td><a href="/program-accomplishment/' + r.id + '" class="action-link">' + escapeHtml(r.title || 'Untitled') + '</a></td>' +
                        '<td>' + escapeHtml(r.program?.program_name || r.program?.activity_name || 'N/A') + '</td>' +
                        '<td>' + (r.date_completed || 'N/A') + '</td>' +
                        '<td>&#8369;' + (parseFloat(r.budget_allocated) || 0).toFixed(2) + '</td>' +
                        '<td>&#8369;' + (parseFloat(r.actual_expense) || 0).toFixed(2) + '</td>' +
                        '<td>' + (parseFloat(r.budget_utilization_percent) || 0).toFixed(2) + '%</td>' +
                        '<td><span class="status-badge status-' + (r.accomplishment_status || '').toLowerCase() + '">' + escapeHtml(r.accomplishment_status || '') + '</span></td>' +
                        '<td>' +
                            (r.accomplishment_status === 'Draft' ? '<a href="/program-accomplishment/' + r.id + '/edit" class="action-link">Edit</a>' : '') +
                            '<a href="/program-accomplishment/' + r.id + '" class="action-link">View</a>' +
                        '</td>';
                    tbody.appendChild(tr);
                });
                if (footer) {
                    var total = result.total || 0;
                    var last = result.last_page || 1;
                    var current = result.current_page || 1;
                    footer.innerHTML = '<span>' + total + ' total report(s)</span>' +
                        '<div class="pagination">' + buildPagination(current, last) + '</div>';
                }
            })
            .catch(function () {
                hideLoading();
                tbody.innerHTML = '<tr><td colspan="8" class="empty-state">Failed to load reports.</td></tr>';
            });
        };

        statusFilter?.addEventListener('change', function () { currentPage = 1; if (window._loadReports) window._loadReports(); });
        searchInput?.addEventListener('input', function () {
            clearTimeout(window._searchTimer);
            window._searchTimer = setTimeout(function () { currentPage = 1; if (window._loadReports) window._loadReports(); }, 300);
        });
        footer?.addEventListener('click', function (e) {
            if (e.target.tagName === 'BUTTON') {
                var page = parseInt(e.target.getAttribute('data-page'));
                if (page) { currentPage = page; if (window._loadReports) window._loadReports(); }
            }
        });
        window._loadReports();
    }

    function buildPagination(current, last) {
        var html = '';
        if (current > 1) html += '<button data-page="' + (current - 1) + '">&laquo;</button>';
        for (var i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
            html += '<button data-page="' + i + '" class="' + (i === current ? 'active' : '') + '">' + i + '</button>';
        }
        if (current < last) html += '<button data-page="' + (current + 1) + '">&raquo;</button>';
        return html;
    }

    // ── Show page ──
    function initShowPage() {
        var submitBtn = document.getElementById('submitReportBtn');
        var deleteBtn = document.getElementById('deleteReportBtn');

        submitBtn?.addEventListener('click', function () {
            if (!confirm('Submit this report for approval?')) return;
            var match = window.location.pathname.match(/\/program-accomplishment\/(\d+)/);
            if (!match) return;
            var id = match[1];
            showLoading();
            fetch('/api/program-accomplishment/' + id + '/submit', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                hideLoading();
                if (d.message) showNotification(d.message, 'success');
                window.location.reload();
            })
            .catch(function () { hideLoading(); showNotification('Failed to submit.', 'error'); });
        });

        deleteBtn?.addEventListener('click', function () {
            if (!confirm('Delete this report permanently?')) return;
            var match = window.location.pathname.match(/\/program-accomplishment\/(\d+)/);
            if (!match) return;
            var id = match[1];
            showLoading();
            fetch('/api/program-accomplishment/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                hideLoading();
                if (d.message) showNotification(d.message, 'success');
                window.location.href = '/program-accomplishment';
            })
            .catch(function () { hideLoading(); showNotification('Failed to delete.', 'error'); });
        });
    }

    // ── Transparency page ──
    function initTransparencyPage() {
        var yearFilter = document.getElementById('yearFilter');
        var tbody = document.getElementById('transparencyTableBody');
        if (!yearFilter || !tbody) return;

        function loadStats() {
            var year = yearFilter.value;
            showLoading();
            fetch('/api/program-accomplishment/stats/transparency?year=' + year, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                hideLoading();
                var stats = result.data || {};
                document.getElementById('statCompleted').textContent = stats.completed_programs || 0;
                document.getElementById('statAllocated').textContent = '&#8369;' + (stats.total_budget_allocated || 0).toFixed(2);
                document.getElementById('statExpense').textContent = '&#8369;' + (stats.total_actual_expense || 0).toFixed(2);
                document.getElementById('statRemaining').textContent = '&#8369;' + (stats.remaining_budget || 0).toFixed(2);
                document.getElementById('statUtilization').textContent = (stats.budget_utilization_percent || 0).toFixed(2) + '%';
                document.getElementById('statBeneficiaries').textContent = stats.total_participants || 0;
            })
            .catch(function () { hideLoading(); });

            var params = new URLSearchParams({ status: 'Published', per_page: 50 });
            if (year) params.set('year', year);
            fetch('/api/program-accomplishment?' + params.toString(), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                tbody.innerHTML = '';
                var rows = result.data || [];
                if (rows.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="empty-state">No completed programs for this year.</td></tr>';
                    return;
                }
                rows.forEach(function (r) {
                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td><a href="/program-accomplishment/' + r.id + '" class="action-link">' + escapeHtml(r.title || 'Untitled') + '</a></td>' +
                        '<td>' + (r.date_completed || 'N/A') + '</td>' +
                        '<td>&#8369;' + (parseFloat(r.budget_allocated) || 0).toFixed(2) + '</td>' +
                        '<td>&#8369;' + (parseFloat(r.actual_expense) || 0).toFixed(2) + '</td>' +
                        '<td>&#8369;' + (parseFloat(r.remaining_budget) || 0).toFixed(2) + '</td>' +
                        '<td>' + (parseFloat(r.budget_utilization_percent) || 0).toFixed(2) + '%</td>' +
                        '<td>' + (r.participants_count || 0) + '</td>';
                    tbody.appendChild(tr);
                });
            })
            .catch(function () { tbody.innerHTML = '<tr><td colspan="7" class="empty-state">Failed to load.</td></tr>'; });
        }

        yearFilter.addEventListener('change', loadStats);
        loadStats();
    }

    // ── Cancel buttons ──
    function initCancelButton() {
        var ids = ['cancelBtn', 'cancelEditBtn'];
        ids.forEach(function (id) {
            var btn = document.getElementById(id);
            if (!btn) return;
            btn.addEventListener('click', function () {
                window.location.href = this.getAttribute('data-cancel-url') || '/program-accomplishment';
            });
        });
    }

    // ── Init ──
    document.addEventListener('DOMContentLoaded', function () {
        initProgramSearch();
        initCloudinaryUpload();
        initFormSubmission();
        initIndexPage();
        initShowPage();
        initTransparencyPage();
        initCancelButton();
        initCreateModal();
        updateBudgetDisplay();
    });

})();
