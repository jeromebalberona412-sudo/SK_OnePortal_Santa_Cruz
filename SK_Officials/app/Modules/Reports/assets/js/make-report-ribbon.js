/**
 * CKEditor decoupled ribbon — mount toolbar + overlay menu dropdowns on the page.
 */

let mountedEditor = null;
let panelPortalObserver = null;
let panelRepositionHandler = null;

function ensurePanelPortal() {
    let portal = document.getElementById('mrCkPanelPortal');
    if (!portal) {
        portal = document.createElement('div');
        portal.id = 'mrCkPanelPortal';
        portal.className = 'mr-ck-panel-portal';
        portal.setAttribute('aria-hidden', 'true');
        document.body.appendChild(portal);
    }
    return portal;
}

function isPanelVisible(panel) {
    if (!panel || !panel.isConnected) return false;
    const style = window.getComputedStyle(panel);
    return style.display !== 'none' && style.visibility !== 'hidden' && panel.offsetParent !== null;
}

function findMenuAnchor(panel) {
    const menuId = panel.getAttribute('aria-labelledby')
        || panel.getAttribute('id')?.replace('panel-', '');
    if (menuId) {
        const byId = document.getElementById(menuId);
        if (byId) return byId;
    }

    const menuRoot = panel.closest('.ck-menu-bar__menu');
    if (menuRoot) {
        return menuRoot.querySelector('.ck-menu-bar__menu__button, .ck-button');
    }

    const openBtn = document.querySelector('.ck-menu-bar__menu__button[aria-expanded="true"]');
    return openBtn || null;
}

function positionFloatingPanel(panel, anchor) {
    if (!anchor) return;
    const rect = anchor.getBoundingClientRect();
    panel.style.setProperty('position', 'fixed', 'important');
    panel.style.setProperty('left', `${Math.max(8, rect.left)}px`, 'important');
    panel.style.setProperty('top', `${rect.bottom + 2}px`, 'important');
    panel.style.setProperty('z-index', '10050', 'important');
    panel.style.setProperty('max-height', 'none', 'important');
    panel.style.setProperty('overflow', 'visible', 'important');
    panel.style.setProperty('overflow-y', 'visible', 'important');
}

function portalVisiblePanels() {
    const portal = ensurePanelPortal();
    const selectors = [
        '.ck-menu-bar__menu__panel',
        '.ck-dropdown-menu__nested-menu__panel',
        '.ck.ck-balloon-panel.ck-dropdown-menu__nested-menu__panel',
    ];

    document.querySelectorAll(selectors.join(',')).forEach(panel => {
        if (!isPanelVisible(panel)) return;

        const anchor = findMenuAnchor(panel);
        if (!anchor) return;

        if (panel.parentElement !== portal) {
            portal.appendChild(panel);
        }

        panel.classList.add('mr-ck-panel--floating');
        positionFloatingPanel(panel, anchor);
    });
}

export function initCkPanelPortal() {
    if (panelPortalObserver) return;

    ensurePanelPortal();

    panelPortalObserver = new MutationObserver(() => {
        requestAnimationFrame(portalVisiblePanels);
    });

    panelPortalObserver.observe(document.body, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: ['class', 'style', 'aria-expanded', 'hidden'],
    });

    panelRepositionHandler = () => requestAnimationFrame(portalVisiblePanels);
    window.addEventListener('resize', panelRepositionHandler);
    window.addEventListener('scroll', panelRepositionHandler, true);
    document.getElementById('mrWorkspace')?.addEventListener('scroll', panelRepositionHandler);
}

export function clearRibbon() {
    const mount = document.getElementById('mrRibbonToolbar');
    if (mount) mount.innerHTML = '';
    mountedEditor = null;
}

export function attachEditorToRibbon(editor) {
    const mount = document.getElementById('mrRibbonToolbar');
    const dock = document.getElementById('mrRibbonDock');
    if (!mount || !editor) return;

    clearRibbon();
    mountedEditor = editor;

    const menuBar = editor.ui?.view?.menuBarView?.element;
    let toolbar = editor.ui?.view?.toolbar?.element;

    if (!toolbar && editor.ui?.view?.element) {
        const top = editor.ui.view.element.querySelector('.ck-editor__top');
        if (top) toolbar = top;
    }

    if (menuBar) mount.appendChild(menuBar);
    if (toolbar) mount.appendChild(toolbar);

    if (dock) dock.hidden = false;

    initCkPanelPortal();
    requestAnimationFrame(portalVisiblePanels);
}

export function setRibbonVisible(visible) {
    const dock = document.getElementById('mrRibbonDock');
    if (dock) dock.hidden = !visible;
}

export function syncRibbonFullscreenState() {
    const main = document.querySelector('.mr-main');
    const isFs = main?.classList.contains('is-fullscreen');
    setRibbonVisible(!isFs);
}

export function initRibbon() {
    const main = document.querySelector('.mr-main');
    if (!main) return;

    initCkPanelPortal();

    const observer = new MutationObserver(() => syncRibbonFullscreenState());
    observer.observe(main, { attributes: true, attributeFilter: ['class'] });
    syncRibbonFullscreenState();
}

export function getMountedRibbonEditor() {
    return mountedEditor;
}
