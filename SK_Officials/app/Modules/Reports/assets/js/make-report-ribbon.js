/**
 * CKEditor decoupled ribbon — mount toolbar + fast menu dropdown positioning.
 */

let mountedEditor = null;
let ribbonObserver = null;
let repositionHandler = null;
let repositionQueued = false;

function isMenuOpen(menuRoot) {
    if (!menuRoot) return false;
    if (menuRoot.classList.contains('ck-on')) return true;
    const btn = menuRoot.querySelector('.ck-menu-bar__menu__button');
    return btn?.getAttribute('aria-expanded') === 'true';
}

function clearPanelPosition(panel) {
    panel.classList.remove('mr-ck-panel--open');
    panel.style.removeProperty('position');
    panel.style.removeProperty('left');
    panel.style.removeProperty('top');
    panel.style.removeProperty('z-index');
    panel.style.removeProperty('max-height');
    panel.style.removeProperty('overflow-y');
    panel.style.removeProperty('min-width');
}

function positionOpenPanel(menuRoot) {
    const panel = menuRoot.querySelector(':scope > .ck-menu-bar__menu__panel');
    const btn = menuRoot.querySelector('.ck-menu-bar__menu__button');
    if (!panel || !btn || !isMenuOpen(menuRoot)) {
        if (panel) clearPanelPosition(panel);
        return;
    }

    const rect = btn.getBoundingClientRect();
    const left = Math.max(8, Math.min(rect.left, window.innerWidth - 280));

    panel.classList.add('mr-ck-panel--open');
    panel.style.setProperty('position', 'fixed', 'important');
    panel.style.setProperty('left', `${left}px`, 'important');
    panel.style.setProperty('top', `${rect.bottom + 2}px`, 'important');
    panel.style.setProperty('z-index', '10050', 'important');
    panel.style.setProperty('max-height', 'min(70vh, 480px)', 'important');
    panel.style.setProperty('overflow-y', 'auto', 'important');
    panel.style.setProperty('min-width', `${Math.max(rect.width, 180)}px`, 'important');
}

function repositionOpenPanels() {
    const mount = document.getElementById('mrRibbonToolbar');
    if (!mount) return;

    mount.querySelectorAll('.ck-menu-bar__menu').forEach(menu => {
        if (isMenuOpen(menu)) {
            positionOpenPanel(menu);
        } else {
            const panel = menu.querySelector(':scope > .ck-menu-bar__menu__panel');
            if (panel) clearPanelPosition(panel);
        }
    });
}

function scheduleReposition() {
    if (repositionQueued) return;
    repositionQueued = true;
    requestAnimationFrame(() => {
        repositionQueued = false;
        repositionOpenPanels();
    });
}

function bindRibbonMenuEvents(mount) {
    if (mount.dataset.mrRibbonBound === '1') return;
    mount.dataset.mrRibbonBound = '1';

    mount.addEventListener('click', (e) => {
        if (e.target.closest('.ck-menu-bar__menu__button')) {
            scheduleReposition();
        }
    }, false);

    mount.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') scheduleReposition();
    });
}

function initRibbonPositioning() {
    const mount = document.getElementById('mrRibbonToolbar');
    if (!mount) return;

    bindRibbonMenuEvents(mount);

    if (!ribbonObserver) {
        ribbonObserver = new MutationObserver(scheduleReposition);
        ribbonObserver.observe(mount, {
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'aria-expanded'],
        });

        repositionHandler = () => scheduleReposition();
        window.addEventListener('resize', repositionHandler, { passive: true });
        window.addEventListener('scroll', repositionHandler, { passive: true, capture: true });
        document.getElementById('mrWorkspace')?.addEventListener('scroll', repositionHandler, { passive: true });
    }

    scheduleReposition();
}

export function clearRibbon() {
    const mount = document.getElementById('mrRibbonToolbar');
    if (mount) {
        mount.querySelectorAll('.mr-ck-panel--open').forEach(clearPanelPosition);
        mount.innerHTML = '';
        delete mount.dataset.mrRibbonBound;
    }
    mountedEditor = null;
}

export function attachEditorToRibbon(editor) {
    const mount = document.getElementById('mrRibbonToolbar');
    const dock = document.getElementById('mrRibbonDock');
    if (!mount || !editor) return;

    clearRibbon();
    mountedEditor = editor;

    const ui = editor.ui?.view;
    const menuBar = ui?.menuBarView?.element;
    let toolbar = ui?.toolbar?.element;

    if (!toolbar && ui?.element) {
        toolbar = ui.element.querySelector('.ck-toolbar, .ck-editor__top');
    }

    if (menuBar) {
        menuBar.classList.add('mr-ribbon-menu-bar');
        mount.appendChild(menuBar);
    }
    if (toolbar) {
        toolbar.classList.add('mr-ribbon-main-toolbar');
        mount.appendChild(toolbar);
    }

    if (dock) {
        dock.hidden = false;
        dock.removeAttribute('aria-hidden');
    }

    initRibbonPositioning();

    requestAnimationFrame(() => {
        const editable = editor.editing?.view?.getDomRoot?.()
            || document.querySelector('.mr-paper-sheet.is-active .ck-editor__editable');
        if (editable && !editor.isReadOnly) {
            editable.setAttribute('contenteditable', 'true');
            editable.removeAttribute('aria-disabled');
        }
    });
}

export function setRibbonVisible(visible) {
    const dock = document.getElementById('mrRibbonDock');
    if (dock) {
        dock.hidden = !visible;
        if (!visible) dock.setAttribute('aria-hidden', 'true');
        else dock.removeAttribute('aria-hidden');
    }
}

export function syncRibbonFullscreenState() {
    const main = document.querySelector('.mr-main');
    const isFs = main?.classList.contains('is-fullscreen');
    setRibbonVisible(!isFs);
}

export function initRibbon() {
    const main = document.querySelector('.mr-main');
    if (!main) return;

    const observer = new MutationObserver(() => syncRibbonFullscreenState());
    observer.observe(main, { attributes: true, attributeFilter: ['class'] });
    syncRibbonFullscreenState();
}

/** @deprecated kept for make-report.js import compatibility */
export function initCkPanelPortal() {
    initRibbonPositioning();
}

export function getMountedRibbonEditor() {
    return mountedEditor;
}
