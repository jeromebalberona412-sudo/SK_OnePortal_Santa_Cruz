/**
 * Scholarship Quick Guidelines — default bilingual steps (English + Tagalog), Laguna-style card layout.
 */
(function (global) {
    'use strict';

    const STEPS = [
        {
            en: 'Open the scholarship program, click <strong>Apply Now</strong>, and complete all sections of the application form. Upload clear <strong>PDF copies</strong> of all required documents (maximum 5MB per file).',
            tl: 'Buksan ang scholarship program, i-click ang <strong>Apply Now</strong>, at kumpletuhin ang lahat ng seksyon ng application form. I-upload ang malinaw na <strong>PDF copies</strong> ng lahat ng kinakailangang dokumento (maximum 5MB bawat file).',
        },
        {
            en: 'Review your personal information, educational background, background information, and uploaded requirements. Make sure all entries match your KK Profiling record and supporting documents.',
            tl: 'Suriin ang inyong personal na impormasyon, educational background, background information, at mga na-upload na requirements. Siguraduhing tumutugma ang lahat ng impormasyon sa inyong KK Profiling record at mga supporting documents.',
        },
        {
            en: 'On the <strong>Review &amp; Confirm</strong> step, check all confirmation boxes and submit your application. Wait for SK Officials to verify your requirements and application details.',
            tl: 'Sa <strong>Review &amp; Confirm</strong> step, lagyan ng check ang lahat ng confirmation boxes at isumite ang inyong aplikasyon. Maghintay habang bineberipika ng SK Officials ang inyong mga requirements at detalye ng aplikasyon.',
        },
        {
            en: 'Once your application and requirements are verified, monitor your application status on the portal. You may view your submitted application from the Scholarship Application page.',
            tl: 'Kapag na-verify na ang inyong aplikasyon at mga requirements, subaybayan ang status ng aplikasyon sa portal. Maaari ninyong tingnan ang inyong naisumiteng aplikasyon sa Scholarship Application page.',
        },
        {
            en: 'Wait for announcements regarding scholarship evaluation results and release schedules. Stay updated by checking the <strong>SK Community Feed</strong> and announcements from your barangay SK.',
            tl: 'Maghintay ng mga anunsyo ukol sa resulta ng scholarship evaluation at iskedyul ng pagkuha. Manatiling updated sa pamamagitan ng <strong>SK Community Feed</strong> at mga anunsyo mula sa inyong barangay SK.',
        },
    ];

    let modalEl = null;
    let isMaximized = false;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderRichText(html) {
        return String(html || '');
    }

    function renderStepCard(step, index) {
        const stepNum = index + 1;
        const listHtml = step.list
            ? `<ul class="sch-qg-doc-list">
                ${step.list.map((item) => `
                    <li>
                        <span class="sch-qg-doc-en">${renderRichText(item.en)}</span>
                        <em class="sch-qg-doc-tl">${escapeHtml(item.tl)}</em>
                    </li>
                `).join('')}
               </ul>`
            : '';

        return `
            <article class="sch-qg-step-card">
                <h3 class="sch-qg-step-heading">Step #${stepNum}</h3>
                <p class="sch-qg-step-en">${renderRichText(step.en)}</p>
                <p class="sch-qg-step-tl"><em>${escapeHtml(step.tl)}</em></p>
                ${listHtml}
            </article>
        `;
    }

    function renderModalContent() {
        const gridHtml = STEPS.map((step, index) => renderStepCard(step, index)).join('');

        return `
            <div class="sch-qg-shell kabataan-modal-box ${isMaximized ? 'modal-maximized' : ''}" role="dialog" aria-modal="true" aria-labelledby="schQgTitle">
                <div class="sch-qg-modal-header modal-header">
                    <h2 class="modal-title" id="schQgTitle">QUICK GUIDE</h2>
                    <div class="modal-window-controls">
                        <button type="button" class="modal-toggle-btn" id="schQgMaximizeBtn" aria-label="${isMaximized ? 'Restore down' : 'Maximize'}">${isMaximized ? '⧉' : '□'}</button>
                        <button type="button" class="modal-close" data-close-sch-qg aria-label="Close">&times;</button>
                    </div>
                </div>

                <div class="sch-qg-body kabataan-modal-body">
                    <p class="sch-qg-page-subtitle">Scholarship Application · SK OnePortal Kabataan</p>
                    <div class="sch-qg-grid">${gridHtml}</div>
                </div>

                <footer class="sch-qg-footer">
                    <p>&copy; Copyright SK OnePortal Santa Cruz. All Rights Reserved.</p>
                </footer>
            </div>
        `;
    }

    function ensureModal() {
        if (modalEl) return modalEl;

        modalEl = document.createElement('div');
        modalEl.id = 'schQuickGuidelinesModal';
        modalEl.className = 'sch-qg-modal kabataan-modal-backdrop';
        modalEl.hidden = true;
        document.body.appendChild(modalEl);
        return modalEl;
    }

    function toggleMaximize() {
        isMaximized = !isMaximized;
        if (modalEl) {
            modalEl.classList.toggle('modal-maximized', isMaximized);
        }
        const shell = modalEl?.querySelector('.sch-qg-shell');
        if (shell) {
            shell.classList.toggle('modal-maximized', isMaximized);
        }
        const btn = modalEl?.querySelector('#schQgMaximizeBtn');
        if (btn) {
            btn.textContent = isMaximized ? '⧉' : '□';
            btn.setAttribute('aria-label', isMaximized ? 'Restore down' : 'Maximize');
        }
    }

    function bindModalEvents() {
        if (!modalEl) return;

        modalEl.querySelectorAll('[data-close-sch-qg]').forEach((el) => {
            el.addEventListener('click', close);
        });

        modalEl.querySelector('#schQgMaximizeBtn')?.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleMaximize();
        });

        modalEl.addEventListener('click', (event) => {
            if (event.target === modalEl) {
                close();
            }
        });

        const shell = modalEl.querySelector('.sch-qg-shell');
        shell?.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.removeEventListener('keydown', onKeyDown);
        document.addEventListener('keydown', onKeyDown);
    }

    function onKeyDown(event) {
        if (event.key === 'Escape' && modalEl && !modalEl.hidden) {
            close();
        }
    }

    function open() {
        const modal = ensureModal();
        modal.innerHTML = renderModalContent();
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        bindModalEvents();
    }

    function close() {
        if (!modalEl) return;
        modalEl.hidden = true;
        modalEl.classList.remove('modal-maximized');
        isMaximized = false;
        document.body.style.overflow = '';
        document.removeEventListener('keydown', onKeyDown);
    }

    function bindTriggers(root) {
        (root || document).querySelectorAll('[data-open-sch-quick-guidelines]').forEach((btn) => {
            if (btn.dataset.schQgBound === '1') return;
            btn.dataset.schQgBound = '1';
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                open();
            });
        });
    }

    global.ScholarshipQuickGuidelines = {
        STEPS,
        open,
        close,
        bindTriggers,
    };

    document.addEventListener('DOMContentLoaded', () => bindTriggers(document));
})(window);
