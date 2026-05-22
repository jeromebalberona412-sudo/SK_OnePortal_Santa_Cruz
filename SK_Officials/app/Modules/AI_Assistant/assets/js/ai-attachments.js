/**
 * SK AI Assistant — file/photo attachments (frontend only)
 */
(function (global) {
    const MAX_FILES = 5;
    const MAX_FILE_BYTES = 5 * 1024 * 1024;
    const ACCEPT = 'image/*,.pdf,.doc,.docx,.txt,.xls,.xlsx';

    const pools = {
        modalWelcome: [],
        modalChat: [],
        pageWelcome: [],
        pageChat: [],
    };

    function readFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve({
                id: 'att_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8),
                name: file.name,
                type: file.type || 'application/octet-stream',
                size: file.size,
                dataUrl: reader.result,
                isImage: (file.type || '').startsWith('image/'),
            });
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    function getPool(key) {
        if (!pools[key]) pools[key] = [];
        return pools[key];
    }

    function clearPool(key) {
        pools[key] = [];
    }

    function count(key) {
        return getPool(key).length;
    }

    function remove(key, id) {
        pools[key] = getPool(key).filter(a => a.id !== id);
    }

    function renderPreview(key, container) {
        if (!container) return;
        const list = getPool(key);
        if (!list.length) {
            container.hidden = true;
            container.innerHTML = '';
            return;
        }
        container.hidden = false;
        container.innerHTML = '';
        list.forEach(att => {
            const chip = document.createElement('div');
            chip.className = 'ai-attach-chip';
            if (att.isImage) {
                const img = document.createElement('img');
                img.src = att.dataUrl;
                img.alt = att.name;
                chip.appendChild(img);
            } else {
                const label = document.createElement('span');
                label.className = 'ai-attach-chip-name';
                label.textContent = att.name;
                chip.appendChild(label);
            }
            const rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'ai-attach-chip-remove';
            rm.setAttribute('aria-label', 'Remove ' + att.name);
            rm.innerHTML = '&times;';
            rm.addEventListener('click', function () {
                remove(key, att.id);
                renderPreview(key, container);
                if (typeof pools[key]._onChange === 'function') pools[key]._onChange();
            });
            chip.appendChild(rm);
            container.appendChild(chip);
        });
    }

    async function addFiles(key, fileList, toast, onChange) {
        const pool = getPool(key);
        const remaining = MAX_FILES - pool.length;
        if (remaining <= 0) {
            toast?.show('Maximum ' + MAX_FILES + ' files per message', 'success');
            return;
        }

        const files = Array.from(fileList || []).slice(0, remaining);
        for (const file of files) {
            if (file.size > MAX_FILE_BYTES) {
                toast?.show(file.name + ' is too large (max 5MB)', 'success');
                continue;
            }
            try {
                pool.push(await readFile(file));
            } catch {
                toast?.show('Could not read ' + file.name, 'success');
            }
        }
        if (onChange) onChange();
    }

    function bind(options) {
        const {
            key,
            attachBtn,
            previewEl,
            toast,
            onChange,
        } = options;

        if (!attachBtn) return null;

        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.multiple = true;
        fileInput.accept = ACCEPT;
        fileInput.hidden = true;
        fileInput.className = 'ai-file-input-hidden';
        attachBtn.after(fileInput);

        getPool(key)._onChange = onChange;

        attachBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            fileInput.click();
        });

        fileInput.addEventListener('change', async function () {
            await addFiles(key, fileInput.files, toast, onChange);
            renderPreview(key, previewEl);
            fileInput.value = '';
            if (onChange) onChange();
        });

        return {
            key,
            getPending: () => getPool(key).map(a => ({ ...a })),
            clear: () => {
                clearPool(key);
                renderPreview(key, previewEl);
            },
            hasFiles: () => count(key) > 0,
            count: () => count(key),
        };
    }

    global.SkAiAttachments = {
        bind,
        getPool,
        clearPool,
        renderPreview,
    };
})(window);
