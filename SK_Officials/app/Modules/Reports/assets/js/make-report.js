/**
 * Make a Report — CKEditor 5 Premium + multi-page + localStorage
 */
import { createMakeReportEditor } from './make-report-ckeditor-config.js';
import { attachEditorToRibbon, clearRibbon, initRibbon } from './make-report-ribbon.js';

document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'sk_official_reports';
    const app = document.getElementById('mrApp');
    if (!app || !window.MR_CKEDITOR_CONFIG) return;

    const params = new URLSearchParams(window.location.search);
    const editId = params.get('id');
    const sourceParam = params.get('source') || 'general';

    let reports = loadReports();
    let currentId = editId || null;
    let deleteTargetId = null;
    let ckEditor = null;
    let autosaveTimer = null;
    let tablePage = 1;
    let editorReady = false;

    let documentPages = [{ html: '', header: '', footer: '' }];
    let activeDocPage = 0;
    const MAX_PAGE_TABS = 5;
    /** Approx. one A4 page of body text (11pt) */
    const PAGE_CHAR_LIMIT = 2800;
    let autoPageLock = false;
    let autoPageTimer = null;

    const sessionDocKey = 'mr_session_doc_' + (editId || 'new');
    let channelId = sessionStorage.getItem(sessionDocKey) || buildChannelId();

    const BADGE_CLASS = {
        draft: 'mr-badge-draft',
        pending: 'mr-badge-pending',
        approved: 'mr-badge-approved',
        returned: 'mr-badge-returned',
        archived: 'mr-badge-archived',
    };

    const PAPER_EXPORT = { a4: 'A4', letter: 'Letter', legal: 'Legal', short: 'Letter', long: 'Legal' };
    const reportsUrl = app.dataset.reportsUrl || '/reports';

    initRibbon();

    function isPageEmpty(html) {
        const text = (html || '')
            .replace(/<[^>]+>/g, ' ')
            .replace(/&nbsp;/gi, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        return !text;
    }

    function normalizeEditorHtml(html) {
        if (isPageEmpty(html)) return '';
        return html || '';
    }

    function buildChannelId() {
        const id = currentId || ('sk-new-' + Date.now());
        sessionStorage.setItem(sessionDocKey, id);
        return id;
    }

    function getReportTitle() {
        const stored = document.getElementById('mrDetailTitle')?.value?.trim();
        if (stored) return stored;
        const plain = getCombinedPlainText();
        const first = plain.split(/\s+/).slice(0, 8).join(' ');
        return first || 'Untitled Report';
    }

    function getExportFileBase() {
        const title = getReportTitle();
        return title.replace(/[^\w\s-]/g, '_') || 'sk-report';
    }

    function getPaperFormat() {
        const size = document.getElementById('mrPaperSize')?.value || 'a4';
        return PAPER_EXPORT[size] || 'A4';
    }

    function buildActiveSheetHtml(page, index) {
        const pageNum = index + 1;
        return `
            <div class="mr-sheet-label">Page ${pageNum}</div>
            <div class="mr-ckeditor-root mr-ckeditor-body" id="mr-editor-container">
                <div class="presence" id="mr-editor-presence"></div>
                <div id="mrEditor"></div>
                <div class="editor_container__word-count" id="mr-editor-word-count"></div>
            </div>
            <div class="revision-history" id="mr-editor-revision-history" hidden>
                <div class="revision-history__wrapper">
                    <div class="revision-history__editor" id="mr-editor-revision-history-editor"></div>
                    <div class="revision-history__sidebar" id="mr-editor-revision-history-sidebar"></div>
                </div>
            </div>
            <div class="mr-sheet-page-num">Page ${pageNum}</div>`;
    }

    function renderPageStack(highlightPageIndex = null) {
        const stack = document.getElementById('mrPaperStack');
        if (!stack) return;

        stack.innerHTML = '';

        documentPages.forEach((page, i) => {
            if (i > 0) {
                const sep = document.createElement('div');
                sep.className = 'mr-page-separator' + (highlightPageIndex === i ? ' is-new' : '');
                sep.setAttribute('role', 'separator');
                sep.setAttribute('aria-label', `Page break before page ${i + 1}`);
                sep.innerHTML = `
                    <span class="mr-page-separator-line"></span>
                    <span class="mr-page-separator-label">Page break — Page ${i + 1}</span>
                    <span class="mr-page-separator-line"></span>`;
                stack.appendChild(sep);
            }

            const sheet = document.createElement('article');
            sheet.className = 'mr-paper-sheet mr-paper mr-paper-a4' + (i === activeDocPage ? ' is-active' : '');
            sheet.dataset.pageIndex = String(i);

            if (i === activeDocPage) {
                sheet.innerHTML = buildActiveSheetHtml(page, i);
            } else {
                sheet.innerHTML = `
                    <div class="mr-sheet-label">Page ${i + 1}</div>
                    <div class="mr-page-preview">${isPageEmpty(page.html) ? '' : page.html}</div>
                    <div class="mr-sheet-page-num">Page ${i + 1}</div>`;
                sheet.addEventListener('click', () => switchDocPage(i));
            }

            stack.appendChild(sheet);
        });

        applyPaperLayout();
    }

    function scrollToActiveSheet() {
        const sheet = document.querySelector(`.mr-paper-sheet[data-page-index="${activeDocPage}"]`);
        sheet?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function highlightNewPageSeparator(pageIndex) {
        const stack = document.getElementById('mrPaperStack');
        if (!stack) return;
        const separators = stack.querySelectorAll('.mr-page-separator');
        const sep = separators[pageIndex - 1];
        if (sep) {
            sep.classList.add('is-new');
            setTimeout(() => sep.classList.remove('is-new'), 4000);
            sep.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    async function destroyEditor() {
        if (!ckEditor) {
            clearRibbon();
            return;
        }
        try {
            clearRibbon();
            await ckEditor.destroy();
        } catch (e) {
            console.warn(e);
        }
        ckEditor = null;
        editorReady = false;
    }

    async function mountEditor(initialData = '') {
        if (!document.getElementById('mrEditor')) {
            renderPageStack();
        }

        const el = document.getElementById('mrEditor');
        if (!el) return;

        await destroyEditor();
        channelId = currentId || channelId;

        try {
            ckEditor = await createMakeReportEditor(el, {
                documentId: channelId,
                licenseKey: window.MR_CKEDITOR_CONFIG.licenseKey,
                cloudTokenUrl: window.MR_CKEDITOR_CONFIG.cloudTokenUrl,
                cloudWebSocketUrl: window.MR_CKEDITOR_CONFIG.cloudWebSocketUrl,
                initialData,
                exportFileBase: getExportFileBase(),
                paperFormat: getPaperFormat(),
            });

            attachEditorToRibbon(ckEditor);

            ckEditor.model.document.on('change:data', () => {
                updateStats();
                triggerAutosave();
                scheduleAutoPagination();
            });

            editorReady = true;
            renderDocPageTabs();
            updateStats();
            updatePageIndicator();
        } catch (err) {
            console.error(err);
            toast('Could not initialize CKEditor. Check license and network.');
        }
    }

    function waitForCkScripts() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const tick = () => {
                if (window.CKEDITOR && window.CKEDITOR_PREMIUM_FEATURES) {
                    resolve();
                    return;
                }
                attempts += 1;
                if (attempts > 80) {
                    reject(new Error('CKEditor CDN timeout'));
                    return;
                }
                setTimeout(tick, 100);
            };
            tick();
        });
    }

    waitForCkScripts()
        .then(async () => {
            if (editId) {
                const r = reports.find(x => x.id === editId);
                if (r) {
                    currentId = r.id;
                    channelId = currentId;
                    document.getElementById('mrDetailTitle').value = r.title || '';
                    const cat = document.getElementById('mrDetailCategory');
                    if (cat && r.type) cat.value = r.type;
                    document.getElementById('mrDetailPeriod').value = r.period || '';
                    document.getElementById('mrDetailBarangay').value = r.barangay || 'Santa Cruz';
                    document.getElementById('mrDetailPrepared').value = r.submittedBy || '';
                    if (r.paperSize) document.getElementById('mrPaperSize').value = r.paperSize;
                    documentPages = normalizePagesFromReport(r);
                    activeDocPage = 0;
                    setStatusBadge(r.status || 'draft');
                }
            } else if (sourceParam === 'scholarship') {
                const cat = document.getElementById('mrDetailCategory');
                if (cat) cat.value = 'scholarship';
            }

            renderPageStack();
            await mountEditor(documentPages[activeDocPage]?.html || '');
            if (editId) toast('Report loaded.');
        })
        .catch(() => toast('CKEditor scripts failed to load.'));

    function plainTextLength(html) {
        return (html || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().length;
    }

    function splitHtmlIntoPages(html, limit) {
        if (isPageEmpty(html)) return [''];
        if (plainTextLength(html) <= limit) {
            return [html];
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const nodes = [...wrapper.childNodes].filter(n =>
            (n.nodeType === Node.ELEMENT_NODE && n.textContent?.trim()) || (n.nodeType === Node.TEXT_NODE && n.textContent?.trim())
        );

        if (!nodes.length) return [''];

        const pages = [];
        let bucket = document.createElement('div');
        let bucketLen = 0;

        const flush = () => {
            const chunk = bucket.innerHTML.trim();
            if (chunk) pages.push(chunk);
            bucket = document.createElement('div');
            bucketLen = 0;
        };

        for (const node of nodes) {
            const clone = node.cloneNode(true);
            const snippet = clone.nodeType === Node.ELEMENT_NODE ? clone.outerHTML : String(clone.textContent || '');
            const snippetLen = plainTextLength(snippet);

            if (snippetLen > limit) {
                if (bucketLen > 0) flush();
                pages.push(snippet);
                continue;
            }

            if (bucketLen + snippetLen > limit && bucketLen > 0) {
                flush();
            }

            bucket.appendChild(clone);
            bucketLen += snippetLen;
        }

        flush();
        return pages.length ? pages : [''];
    }

    function scheduleAutoPagination() {
        clearTimeout(autoPageTimer);
        autoPageTimer = setTimeout(handleAutoPagination, 450);
    }

    async function handleAutoPagination() {
        if (autoPageLock || !ckEditor || !editorReady) return;

        const html = getEditorHtml();
        const len = plainTextLength(html);
        updatePageIndicator(len);

        if (len <= PAGE_CHAR_LIMIT) return;

        const parts = splitHtmlIntoPages(html, PAGE_CHAR_LIMIT);
        if (parts.length <= 1) return;

        autoPageLock = true;
        try {
            saveCurrentDocPage();
            documentPages[activeDocPage].html = parts[0];

            const extra = parts.slice(1).map(h => ({
                html: h,
                header: '',
                footer: '',
            }));
            const newPageStart = activeDocPage + 1;
            documentPages.splice(newPageStart, 0, ...extra);

            await destroyEditor();
            renderPageStack(newPageStart);
            await mountEditor(parts[0]);
            highlightNewPageSeparator(newPageStart);
            toast(`New page ${newPageStart + 1} created — overflow text moved to the next sheet.`);
        } finally {
            setTimeout(() => { autoPageLock = false; }, 200);
        }
    }

    function updatePageIndicator(currentLen) {
        const indicator = document.getElementById('mrPageIndicator');
        if (!indicator) return;
        const len = currentLen ?? plainTextLength(getEditorHtml());
        indicator.textContent = `Page ${activeDocPage + 1} of ${documentPages.length} · ${len.toLocaleString()} / ${PAGE_CHAR_LIMIT.toLocaleString()} chars`;
        indicator.classList.toggle('is-near-limit', len > PAGE_CHAR_LIMIT * 0.9 && len <= PAGE_CHAR_LIMIT);
        indicator.classList.toggle('is-over-limit', len > PAGE_CHAR_LIMIT);
    }

    function saveCurrentDocPage() {
        if (!documentPages[activeDocPage]) {
            documentPages[activeDocPage] = { html: '', header: '', footer: '' };
        }
        documentPages[activeDocPage].html = normalizeEditorHtml(getEditorHtml());
    }

    async function switchDocPage(index) {
        if (index < 0 || index >= documentPages.length || index === activeDocPage) return;
        saveCurrentDocPage();
        await destroyEditor();
        activeDocPage = index;
        renderPageStack();
        await mountEditor(documentPages[index]?.html || '');
        renderDocPageTabs();
        updateStats();
        scrollToActiveSheet();
    }

    async function addDocPage() {
        saveCurrentDocPage();
        await destroyEditor();
        documentPages.push({ html: '', header: '', footer: '' });
        activeDocPage = documentPages.length - 1;
        renderPageStack(activeDocPage);
        await mountEditor('');
        renderDocPageTabs();
        updateStats();
        scrollToActiveSheet();
        toast('New page added.');
    }

    function getVisibleTabIndices() {
        const total = documentPages.length;
        if (total <= MAX_PAGE_TABS) return Array.from({ length: total }, (_, i) => i);
        const start = Math.max(0, Math.min(activeDocPage - 2, total - MAX_PAGE_TABS));
        return Array.from({ length: MAX_PAGE_TABS }, (_, i) => start + i);
    }

    function renderDocPageTabs() {
        const container = document.getElementById('mrDocPageTabs');
        if (!container) return;

        container.innerHTML = getVisibleTabIndices().map(i => `
            <button type="button" role="tab" class="mr-page-tab${i === activeDocPage ? ' is-active' : ''}"
                data-doc-page="${i}">${i + 1}</button>`).join('');

        container.querySelectorAll('[data-doc-page]').forEach(btn => {
            btn.addEventListener('click', () => switchDocPage(parseInt(btn.dataset.docPage, 10)));
        });

        const total = documentPages.length;
        document.getElementById('mrPrevPage').disabled = activeDocPage <= 0;
        document.getElementById('mrNextPage').disabled = activeDocPage >= total - 1;
        updatePageIndicator();
    }

    document.getElementById('mrPrevPage')?.addEventListener('click', () => switchDocPage(activeDocPage - 1));
    document.getElementById('mrNextPage')?.addEventListener('click', () => switchDocPage(activeDocPage + 1));
    document.getElementById('mrAddPage')?.addEventListener('click', addDocPage);

    function normalizePagesFromReport(r) {
        if (Array.isArray(r.pages) && r.pages.length) {
            return r.pages.map(p => (typeof p === 'string'
                ? { html: p, header: '', footer: '' }
                : { html: p.html || '', header: p.header || '', footer: p.footer || '' }));
        }
        return [{ html: r.html || '', header: '', footer: '' }];
    }

    function getAllPagesHtml() {
        saveCurrentDocPage();
        return documentPages.map((p, i) => {
            let html = p.html || '';
            if (i < documentPages.length - 1) {
                html += '<div class="page-break" style="page-break-after: always;"><span style="display:none">&nbsp;</span></div>';
            }
            return html;
        }).join('');
    }

    function getCombinedPlainText() {
        saveCurrentDocPage();
        return documentPages
            .map(p => (p.html || '').replace(/<[^>]+>/g, ' '))
            .join(' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function loadReports() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        } catch {
            return [];
        }
    }

    function saveReports() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(reports));
    }

    function getEditorHtml() {
        return ckEditor ? ckEditor.getData() : '';
    }

    function setEditorHtml(html) {
        if (ckEditor) ckEditor.setData(html || '');
    }

    function escapeHtml(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function toast(msg) {
        const el = document.getElementById('mrToast');
        if (!el) return;
        el.textContent = msg;
        el.hidden = false;
        clearTimeout(toast._t);
        toast._t = setTimeout(() => { el.hidden = true; }, 2800);
    }

    const dateEl = document.getElementById('mrCurrentDate');
    if (dateEl) {
        dateEl.textContent = new Date().toLocaleDateString('en-PH', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        });
    }

    const submitted = document.getElementById('mrDetailSubmitted');
    if (submitted && !submitted.value) {
        submitted.value = new Date().toISOString().slice(0, 10);
    }

    function syncHiddenTitle() {
        const el = document.getElementById('mrDetailTitle');
        if (!el) return;
        const plain = getCombinedPlainText();
        const words = plain ? plain.split(/\s+/).filter(Boolean).slice(0, 8).join(' ') : '';
        if (!el.value.trim() && words) el.value = words;
    }

    function setStatusBadge(status) {
        const el = document.getElementById('mrStatusBadge');
        if (!el) return;
        el.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        el.className = 'mr-badge ' + (BADGE_CLASS[status] || BADGE_CLASS.draft);
    }

    function triggerAutosave() {
        const el = document.getElementById('mrAutosaveStatus');
        if (el) {
            el.classList.add('is-saving');
            el.innerHTML = '<span class="mr-autosave-dot"></span> Saving…';
        }
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(() => {
            if (el) {
                el.classList.remove('is-saving');
                el.innerHTML = '<span class="mr-autosave-dot"></span> All changes saved';
            }
            const last = document.getElementById('mrLastEdited');
            if (last) last.textContent = 'Last edited: ' + new Date().toLocaleTimeString();
        }, 700);
    }

    function updateStats() {
        const plain = getCombinedPlainText();
        const words = plain ? plain.split(' ').filter(Boolean).length : 0;
        const chars = plain.length;
        const readMin = Math.max(1, Math.ceil(words / 200));
        const wc = document.getElementById('mrWordCount');
        const cc = document.getElementById('mrCharCount');
        const rt = document.getElementById('mrReadTime');
        if (wc) wc.textContent = words + (words === 1 ? ' word' : ' words');
        if (cc) cc.textContent = chars + ' characters';
        if (rt) rt.textContent = readMin + ' min read';
        syncHiddenTitle();
    }

    const paperMap = {
        a4: 'mr-paper-a4',
        letter: 'mr-paper-letter',
        legal: 'mr-paper-legal',
        short: 'mr-paper-short',
        long: 'mr-paper-long',
    };

    function applyPaperLayout() {
        const sheets = document.querySelectorAll('.mr-paper-sheet');
        if (!sheets.length) return;
        const size = document.getElementById('mrPaperSize')?.value || 'a4';
        const orient = document.getElementById('mrOrientation')?.value || 'portrait';
        const margin = document.getElementById('mrMargins')?.value || 'normal';
        const pads = { narrow: '12mm 14mm', normal: '18mm 20mm', moderate: '16mm 18mm', wide: '24mm 26mm' };
        const pad = pads[margin] || pads.normal;

        sheets.forEach(sheet => {
            Object.values(paperMap).forEach(c => sheet.classList.remove(c));
            sheet.classList.add(paperMap[size] || 'mr-paper-a4');
            sheet.classList.toggle('mr-paper-landscape', orient === 'landscape');
            sheet.style.padding = pad;
        });

        const zoom = document.getElementById('mrZoom')?.value || '1';
        document.getElementById('mrZoomWrap')?.style.setProperty('--mr-zoom', zoom);
    }

    ['mrPaperSize', 'mrOrientation', 'mrMargins', 'mrZoom'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', applyPaperLayout);
    });
    applyPaperLayout();

    document.getElementById('mrFullscreenBtn')?.addEventListener('click', () => {
        document.querySelector('.mr-main')?.classList.toggle('is-fullscreen');
    });

    async function downloadPdf() {
        if (!ckEditor) {
            toast('Editor is not ready.');
            return;
        }
        saveCurrentDocPage();
        const restoreIndex = activeDocPage;

        const fileName = getExportFileBase() + '.pdf';
        try {
            if (documentPages.length > 1) {
                ckEditor.setData(getAllPagesHtml());
            }
            await ckEditor.execute('exportPdf', { fileName });
            toast('PDF download started.');
        } catch (err) {
            console.error(err);
            toast('PDF export failed. Use toolbar Export PDF or Print.');
        } finally {
            if (documentPages.length > 1) {
                await switchDocPage(restoreIndex);
            }
        }
    }

    async function downloadWord() {
        if (!ckEditor) return;
        saveCurrentDocPage();
        const fileName = getExportFileBase() + '.docx';
        const restoreIndex = activeDocPage;
        try {
            if (documentPages.length > 1) ckEditor.setData(getAllPagesHtml());
            await ckEditor.execute('exportWord', { fileName });
            toast('Word document download started.');
        } catch (err) {
            console.error(err);
            toast('Word export failed.');
        } finally {
            if (documentPages.length > 1) await switchDocPage(restoreIndex);
        }
    }

    function buildPayload(status) {
        saveCurrentDocPage();
        return {
            id: currentId || ('rpt_' + Date.now()),
            title: getReportTitle(),
            type: document.getElementById('mrDetailCategory')?.value || 'accomplishment',
            category: document.getElementById('mrDetailCategory')?.value || 'accomplishment',
            barangay: document.getElementById('mrDetailBarangay')?.value || 'Santa Cruz',
            submittedBy: document.getElementById('mrDetailPrepared')?.value || 'SK Official',
            period: document.getElementById('mrDetailPeriod')?.value || '',
            paperSize: document.getElementById('mrPaperSize')?.value || 'a4',
            pages: documentPages.map(p => ({ ...p })),
            pageCount: documentPages.length,
            html: getAllPagesHtml(),
            source: sourceParam,
            status: status || 'draft',
            createdAt: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
            updatedAt: new Date().toISOString(),
        };
    }

    function persistReport(status) {
        const payload = buildPayload(status);
        const idx = reports.findIndex(r => r.id === payload.id);
        if (idx >= 0) {
            payload.createdAt = reports[idx].createdAt || payload.createdAt;
            reports[idx] = { ...reports[idx], ...payload };
        } else {
            reports.unshift(payload);
        }
        currentId = payload.id;
        channelId = currentId;
        sessionStorage.setItem(sessionDocKey, channelId);
        saveReports();
        setStatusBadge(payload.status);
        return payload;
    }

    async function loadReportIntoForm(id) {
        const r = reports.find(x => x.id === id);
        if (!r) return;
        saveCurrentDocPage();
        await destroyEditor();

        currentId = r.id;
        channelId = currentId;
        sessionStorage.setItem(sessionDocKey, channelId);

        document.getElementById('mrDetailTitle').value = r.title || '';
        const cat = document.getElementById('mrDetailCategory');
        if (cat && r.type) cat.value = r.type;
        document.getElementById('mrDetailPeriod').value = r.period || '';
        document.getElementById('mrDetailBarangay').value = r.barangay || 'Santa Cruz';
        document.getElementById('mrDetailPrepared').value = r.submittedBy || '';
        if (r.paperSize) document.getElementById('mrPaperSize').value = r.paperSize;

        documentPages = normalizePagesFromReport(r);
        activeDocPage = 0;
        renderPageStack();
        await mountEditor(documentPages[0]?.html || '');
        renderDocPageTabs();
        setStatusBadge(r.status || 'draft');
        updateStats();
        scrollToActiveSheet();
        toast('Report loaded.');
    }

    document.querySelectorAll('[data-mr-action]').forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.mrAction;
            if (action === 'draft') { persistReport('draft'); toast('Draft saved.'); }
            else if (action === 'save') { persistReport('draft'); toast('Changes saved.'); }
            else if (action === 'preview') openPreview();
            else if (action === 'export') openModal('mrModalExport');
            else if (action === 'pdf') downloadPdf();
            else if (action === 'word') downloadWord();
            else if (action === 'print') window.print();
            else if (action === 'save-exit') openSaveNameModal();
        });
    });

    function openSaveNameModal() {
        saveCurrentDocPage();
        const input = document.getElementById('mrSaveFileName');
        if (input) {
            input.value = getReportTitle();
            input.focus();
            input.select();
        }
        openModal('mrModalSaveName');
    }

    function saveAndRedirectToReports() {
        const input = document.getElementById('mrSaveFileName');
        const name = input?.value?.trim();
        if (!name) {
            toast('Please enter a file name.');
            input?.focus();
            return;
        }
        const titleEl = document.getElementById('mrDetailTitle');
        if (titleEl) titleEl.value = name;
        persistReport('draft');
        closeModals();
        window.location.href = reportsUrl;
    }

    document.getElementById('mrConfirmSaveName')?.addEventListener('click', saveAndRedirectToReports);
    document.getElementById('mrSaveFileName')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveAndRedirectToReports();
        }
    });

    function openModal(id) {
        document.getElementById(id).hidden = false;
    }
    function closeModals() {
        document.querySelectorAll('.mr-modal-backdrop').forEach(m => { m.hidden = true; });
    }
    document.querySelectorAll('[data-close-modal]').forEach(b => b.addEventListener('click', closeModals));
    document.querySelectorAll('.mr-modal-backdrop').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) closeModals(); });
    });

    function getPreviewPaperClasses() {
        const size = document.getElementById('mrPaperSize')?.value || 'a4';
        const orient = document.getElementById('mrOrientation')?.value || 'portrait';
        const paperClass = paperMap[size] || 'mr-paper-a4';
        const landscape = orient === 'landscape' ? ' mr-paper-landscape' : '';
        return `${paperClass}${landscape}`;
    }

    function getPreviewPadding() {
        const margin = document.getElementById('mrMargins')?.value || 'normal';
        const pads = { narrow: '12mm 14mm', normal: '18mm 20mm', moderate: '16mm 18mm', wide: '24mm 26mm' };
        return pads[margin] || pads.normal;
    }

    function getPreviewSheetContents() {
        saveCurrentDocPage();
        const sheets = [];
        documentPages.forEach((p) => {
            const html = p.html?.trim() || '';
            if (isPageEmpty(html)) {
                sheets.push('');
                return;
            }
            const parts = splitHtmlIntoPages(html, PAGE_CHAR_LIMIT);
            parts.forEach(part => sheets.push(part));
        });
        return sheets.length ? sheets : [''];
    }

    function openPreview() {
        const body = document.getElementById('mrPreviewBody');
        if (body) {
            const sheets = getPreviewSheetContents();
            const total = sheets.length;
            const paperClasses = getPreviewPaperClasses();
            const pad = getPreviewPadding();
            const pagesHtml = sheets.map((pageBody, i) => {
                const pageNum = i + 1;
                return `
                <article class="mr-preview-sheet ${paperClasses}" style="padding:${pad}">
                    <span class="mr-preview-sheet-label">Page ${pageNum}</span>
                    <div class="mr-preview-sheet-body">${pageBody}</div>
                    <span class="mr-preview-sheet-footer">Page ${pageNum} of ${total}</span>
                </article>`;
            }).join('');
            body.innerHTML = `
                <div class="mr-preview-stack" id="mrPreviewStack">
                    <p class="mr-preview-meta">${total} page${total === 1 ? '' : 's'} · matches PDF/Word export</p>
                    ${pagesHtml}
                </div>`;
            const zoom = document.getElementById('mrPreviewZoom')?.value || '1';
            body.style.setProperty('--mr-preview-zoom', zoom);
        }
        openModal('mrModalPreview');
    }

    document.getElementById('mrPreviewZoom')?.addEventListener('change', e => {
        const body = document.getElementById('mrPreviewBody');
        if (body) body.style.setProperty('--mr-preview-zoom', e.target.value);
    });
    document.getElementById('mrPreviewPrint')?.addEventListener('click', () => window.print());
    document.getElementById('mrPreviewPdf')?.addEventListener('click', () => downloadPdf());

    document.querySelectorAll('[data-export]').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.export;
            closeModals();
            if (type === 'pdf') downloadPdf();
            else if (type === 'word') downloadWord();
            else if (type === 'print') window.print();
            else if (type === 'draft') { persistReport('draft'); toast('Saved as draft.'); }
        });
    });

    function downloadHtml() {
        const title = getReportTitle();
        const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${escapeHtml(title)}</title></head><body>${getAllPagesHtml()}</body></html>`;
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([html], { type: 'text/html' }));
        a.download = title.replace(/[^\w\s-]/g, '') + '.html';
        a.click();
        toast('HTML downloaded.');
    }

    const darkBtn = document.getElementById('mrDarkToggle');
    function syncDarkLabel() {
        if (darkBtn) darkBtn.textContent = app.classList.contains('mr-dark') ? 'Light' : 'Dark';
    }
    darkBtn?.addEventListener('click', () => {
        app.classList.toggle('mr-dark');
        document.getElementById('mrHtml')?.classList.toggle('mr-dark');
        localStorage.setItem('mr_dark', app.classList.contains('mr-dark') ? '1' : '0');
        syncDarkLabel();
    });
    if (localStorage.getItem('mr_dark') === '1') {
        app.classList.add('mr-dark');
        document.getElementById('mrHtml')?.classList.add('mr-dark');
    }
    syncDarkLabel();

    function syncSidebarLayout() {
        const sidebar = document.getElementById('mainSidebar');
        const mrApp = document.getElementById('mrApp');
        if (!sidebar || !mrApp) return;
        if (window.innerWidth <= 768) {
            mrApp.classList.remove('mr-sidebar-collapsed');
            return;
        }
        mrApp.classList.toggle('mr-sidebar-collapsed', sidebar.classList.contains('collapsed'));
    }

    const sidebar = document.getElementById('mainSidebar');
    if (sidebar) {
        new MutationObserver(syncSidebarLayout).observe(sidebar, { attributes: true, attributeFilter: ['class'] });
    }
    document.getElementById('sidebarToggle')?.addEventListener('click', () => setTimeout(syncSidebarLayout, 60));
    window.addEventListener('resize', syncSidebarLayout);
    syncSidebarLayout();
});
