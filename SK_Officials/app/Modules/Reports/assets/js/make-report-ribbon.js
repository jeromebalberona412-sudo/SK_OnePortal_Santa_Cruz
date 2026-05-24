/**
 * CKEditor decoupled ribbon — mount toolbar below page navigation (sticky).
 */

let mountedEditor = null;

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

    const observer = new MutationObserver(() => syncRibbonFullscreenState());
    observer.observe(main, { attributes: true, attributeFilter: ['class'] });
    syncRibbonFullscreenState();
}

export function getMountedRibbonEditor() {
    return mountedEditor;
}
