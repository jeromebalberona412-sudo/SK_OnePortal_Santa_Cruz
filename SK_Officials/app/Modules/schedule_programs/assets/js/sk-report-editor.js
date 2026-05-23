/**
 * Shared MS Word–style report editor: ribbon, paper pages, images, alignment
 */
(function (global) {
    const PAPER_SIZES = {
        a4: { label: 'A4', class: 'paper-a4' },
        letter: { label: 'Letter (Short)', class: 'paper-letter' },
        legal: { label: 'Legal (Long)', class: 'paper-legal' },
        long: { label: 'Long Bond', class: 'paper-long' },
        folio: { label: 'Folio', class: 'paper-folio' },
    };

    const FONT_SIZE_MAP = { 1: '8pt', 2: '10pt', 3: '12pt', 4: '14pt', 5: '18pt', 6: '24pt', 7: '36pt' };

    let activeWrap = null;
    let cropState = null;

    function readFileAsDataUrl(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    function deselectImage() {
        if (activeWrap) {
            activeWrap.classList.remove('is-selected');
            activeWrap = null;
        }
    }

    function selectImage(wrap) {
        deselectImage();
        activeWrap = wrap;
        wrap.classList.add('is-selected');
    }

    function createImageWrap(src, alt) {
        const wrap = document.createElement('span');
        wrap.className = 'doc-img-wrap';
        wrap.contentEditable = 'false';
        wrap.draggable = false;

        const img = document.createElement('img');
        img.src = src;
        img.alt = alt || 'Image';
        img.className = 'doc-img';
        img.style.width = '280px';
        wrap.appendChild(img);

        ['nw', 'ne', 'sw', 'se'].forEach(pos => {
            const h = document.createElement('span');
            h.className = 'doc-img-handle doc-img-handle--' + pos;
            h.dataset.handle = pos;
            wrap.appendChild(h);
        });

        wrap.addEventListener('click', e => {
            e.stopPropagation();
            selectImage(wrap);
        });

        return wrap;
    }

    function insertImage(editor, dataUrl, name) {
        if (!editor) return;
        const wrap = createImageWrap(dataUrl, name);
        editor.focus();
        const sel = window.getSelection();
        if (sel && sel.rangeCount) {
            const range = sel.getRangeAt(0);
            range.collapse(false);
            range.insertNode(wrap);
            const spacer = document.createTextNode('\u00A0');
            wrap.after(spacer);
        } else {
            editor.appendChild(wrap);
        }
        selectImage(wrap);
    }

    function initResize(editor) {
        let resizing = null;

        editor.addEventListener('mousedown', e => {
            const handle = e.target.closest('.doc-img-handle');
            if (!handle) return;
            e.preventDefault();
            const wrap = handle.closest('.doc-img-wrap');
            const img = wrap?.querySelector('img');
            if (!wrap || !img) return;

            resizing = {
                wrap,
                img,
                handle: handle.dataset.handle,
                startX: e.clientX,
                startY: e.clientY,
                startW: img.offsetWidth,
                startH: img.offsetHeight,
            };
            selectImage(wrap);
        });

        document.addEventListener('mousemove', e => {
            if (!resizing) return;
            const dx = e.clientX - resizing.startX;
            const h = resizing.handle;
            let newW = resizing.startW;
            if (h.includes('e')) newW = resizing.startW + dx;
            if (h.includes('w')) newW = resizing.startW - dx;
            const ratio = resizing.startH / resizing.startW;
            newW = Math.max(60, Math.min(720, newW));
            resizing.img.style.width = newW + 'px';
            resizing.img.style.height = Math.round(newW * ratio) + 'px';
        });

        document.addEventListener('mouseup', () => {
            resizing = null;
        });
    }

    function openCropModal(img, onApply) {
        let overlay = document.getElementById('skReportCropOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'skReportCropOverlay';
            overlay.className = 'sk-report-crop-overlay';
            overlay.innerHTML = `
                <div class="sk-report-crop-box">
                    <div class="sk-report-crop-header">
                        <h4>Crop Image</h4>
                        <button type="button" class="sk-report-crop-close">&times;</button>
                    </div>
                    <div class="sk-report-crop-stage-wrap">
                        <canvas id="skReportCropCanvas"></canvas>
                    </div>
                    <p class="sk-report-crop-hint">Drag on the image to select crop area, then Apply.</p>
                    <div class="sk-report-crop-actions">
                        <button type="button" class="sports-btn sports-btn-outline" id="skReportCropCancel">Cancel</button>
                        <button type="button" class="sports-btn sports-btn-primary" id="skReportCropApply">Apply Crop</button>
                    </div>
                </div>`;
            document.body.appendChild(overlay);
        }

        const canvas = overlay.querySelector('#skReportCropCanvas');
        const ctx = canvas.getContext('2d');
        const source = new Image();
        source.crossOrigin = 'anonymous';

        cropState = { startX: 0, startY: 0, endX: 0, endY: 0, dragging: false };
        let displayScale = 1;

        source.onload = () => {
            const maxW = Math.min(640, window.innerWidth - 48);
            displayScale = maxW / source.width;
            canvas.width = maxW;
            canvas.height = source.height * displayScale;
            ctx.drawImage(source, 0, 0, canvas.width, canvas.height);
            cropState.endX = canvas.width;
            cropState.endY = canvas.height;
            drawCropPreview();
            overlay.style.display = 'flex';
        };
        source.src = img.src;

        function drawCropPreview() {
            ctx.drawImage(source, 0, 0, canvas.width, canvas.height);
            const x = Math.min(cropState.startX, cropState.endX);
            const y = Math.min(cropState.startY, cropState.endY);
            const w = Math.abs(cropState.endX - cropState.startX);
            const h = Math.abs(cropState.endY - cropState.startY);
            ctx.fillStyle = 'rgba(0,0,0,0.45)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.clearRect(x, y, w, h);
            ctx.drawImage(
                source,
                x / displayScale, y / displayScale, w / displayScale, h / displayScale,
                x, y, w, h
            );
            ctx.strokeStyle = '#f5c518';
            ctx.lineWidth = 2;
            ctx.strokeRect(x, y, w, h);
        }

        canvas.onmousedown = e => {
            const rect = canvas.getBoundingClientRect();
            cropState.dragging = true;
            cropState.startX = e.clientX - rect.left;
            cropState.startY = e.clientY - rect.top;
            cropState.endX = cropState.startX;
            cropState.endY = cropState.startY;
        };
        canvas.onmousemove = e => {
            if (!cropState.dragging) return;
            const rect = canvas.getBoundingClientRect();
            cropState.endX = e.clientX - rect.left;
            cropState.endY = e.clientY - rect.top;
            drawCropPreview();
        };
        canvas.onmouseup = () => { cropState.dragging = false; };

        const close = () => { overlay.style.display = 'none'; };
        overlay.querySelector('.sk-report-crop-close').onclick = close;
        overlay.querySelector('#skReportCropCancel').onclick = close;
        overlay.querySelector('#skReportCropApply').onclick = () => {
            const x = Math.min(cropState.startX, cropState.endX);
            const y = Math.min(cropState.startY, cropState.endY);
            const w = Math.max(10, Math.abs(cropState.endX - cropState.startX));
            const h = Math.max(10, Math.abs(cropState.endY - cropState.startY));
            const out = document.createElement('canvas');
            out.width = Math.max(1, Math.round(w / displayScale));
            out.height = Math.max(1, Math.round(h / displayScale));
            out.getContext('2d').drawImage(
                source,
                x / displayScale, y / displayScale, w / displayScale, h / displayScale,
                0, 0, out.width, out.height
            );
            onApply(out.toDataURL('image/png'));
            close();
        };
    }

    function applyPaperSize(pageEl, sizeKey, root) {
        if (!pageEl) return;
        Object.values(PAPER_SIZES).forEach(p => pageEl.classList.remove(p.class));
        const cfg = PAPER_SIZES[sizeKey] || PAPER_SIZES.a4;
        pageEl.classList.add(cfg.class);
        pageEl.dataset.paperSize = sizeKey;

        const preview = root?.querySelector('[data-page-preview] .word-page-preview-sheet');
        if (preview) {
            const ratios = { a4: '210/297', letter: '8.5/11', legal: '8.5/14', long: '8.5/13', folio: '8.5/13' };
            preview.style.aspectRatio = ratios[sizeKey] || '210/297';
        }

        const statusPaper = root?.querySelector('[data-word-paper]');
        if (statusPaper) statusPaper.textContent = cfg.label;
    }

    function countWords(editor) {
        const text = (editor?.innerText || '').replace(/\s+/g, ' ').trim();
        if (!text) return 0;
        return text.split(' ').filter(Boolean).length;
    }

    function updateWordCount(editor, root) {
        const el = root?.querySelector('[data-word-count]');
        if (el) {
            const n = countWords(editor);
            el.textContent = n + (n === 1 ? ' word' : ' words');
        }
    }

    function initRibbon(root, editor, pageEl, options) {
        if (!root) return;

        root.querySelectorAll('.word-ribbon-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const name = tab.getAttribute('data-ribbon-tab');
                root.querySelectorAll('.word-ribbon-tab').forEach(t => t.classList.toggle('is-active', t === tab));
                root.querySelectorAll('.word-ribbon-panel').forEach(p => {
                    p.classList.toggle('is-active', p.getAttribute('data-ribbon-panel') === name);
                });
            });
        });

        const cmdSelector = options.toolBtnSelector || '.word-tool[data-cmd], .word-align-btn[data-cmd]';
        root.querySelectorAll(cmdSelector).forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                const cmd = btn.getAttribute('data-cmd');
                if (!cmd) return;
                if (cmd === 'createLink') return;
                editor.focus();
                document.execCommand(cmd, false, null);
                if (cmd.startsWith('justify')) {
                    root.querySelectorAll('.word-align-btn').forEach(b => b.classList.remove('is-active'));
                    root.querySelectorAll(`.word-align-btn[data-cmd="${cmd}"]`).forEach(b => b.classList.add('is-active'));
                }
                updateWordCount(editor, root);
            });
        });

        const fontFamily = root.querySelector('[data-font-family]');
        if (fontFamily) {
            fontFamily.addEventListener('change', () => {
                editor.focus();
                document.execCommand('fontName', false, fontFamily.value);
            });
        }

        const fontSize = root.querySelector('[data-font-size]');
        if (fontSize) {
            fontSize.addEventListener('change', () => {
                editor.focus();
                document.execCommand('fontSize', false, fontSize.value);
                const sel = window.getSelection();
                if (sel && sel.rangeCount) {
                    const node = sel.anchorNode?.parentElement;
                    if (node) {
                        const pt = FONT_SIZE_MAP[fontSize.value];
                        if (pt) node.style.fontSize = pt;
                    }
                }
            });
        }

        const fontColor = root.querySelector('[data-font-color]');
        if (fontColor) {
            fontColor.addEventListener('input', () => {
                editor.focus();
                document.execCommand('foreColor', false, fontColor.value);
            });
        }

        const highlightColor = root.querySelector('[data-highlight-color]');
        if (highlightColor) {
            highlightColor.addEventListener('input', () => {
                editor.focus();
                document.execCommand('hiliteColor', false, highlightColor.value);
            });
        }

        const lineSpacing = root.querySelector('[data-line-spacing]');
        if (lineSpacing) {
            lineSpacing.addEventListener('change', () => {
                editor.style.lineHeight = lineSpacing.value;
            });
        }

        root.querySelectorAll('[data-block-style]').forEach(chip => {
            chip.addEventListener('click', () => {
                const tag = chip.getAttribute('data-block-style');
                editor.focus();
                document.execCommand('formatBlock', false, tag === 'p' ? 'p' : tag);
                root.querySelectorAll('[data-block-style]').forEach(c => c.classList.remove('is-active'));
                chip.classList.add('is-active');
            });
        });

        const linkBtn = root.querySelector('[data-cmd-link]');
        if (linkBtn) {
            linkBtn.addEventListener('click', () => {
                const url = prompt('Enter link URL:', 'https://');
                if (url) {
                    editor.focus();
                    document.execCommand('createLink', false, url);
                }
            });
        }

        const zoomWrap = root.querySelector('[data-word-zoom-wrap]');
        const zoomSlider = root.querySelector('[data-zoom-slider]');
        const zoomLabel = root.querySelector('[data-zoom-label]');

        function setZoom(pct) {
            const z = Math.max(50, Math.min(150, pct));
            if (zoomWrap) zoomWrap.style.setProperty('--word-zoom', (z / 100).toString());
            if (zoomSlider) zoomSlider.value = z;
            if (zoomLabel) zoomLabel.textContent = z + '%';
        }

        if (zoomSlider) {
            zoomSlider.addEventListener('input', () => setZoom(parseInt(zoomSlider.value, 10)));
        }
        root.querySelector('[data-zoom-in]')?.addEventListener('click', () => {
            setZoom((parseInt(zoomSlider?.value || '100', 10)) + 10);
        });
        root.querySelector('[data-zoom-out]')?.addEventListener('click', () => {
            setZoom((parseInt(zoomSlider?.value || '100', 10)) - 10);
        });

        editor.addEventListener('input', () => updateWordCount(editor, root));
        editor.addEventListener('keyup', () => updateWordCount(editor, root));
        updateWordCount(editor, root);

        applyPaperSize(pageEl, options.paperSelect?.value || 'a4', root);
    }

    function init(options) {
        const editor = options.editor;
        const pageEl = options.pageEl || editor;
        const root = options.root || editor?.closest('.word-editor-shell');
        const paperSelect = options.paperSelect;
        const imageInput = options.imageInput;
        const cropBtn = options.cropBtn;
        const deleteImgBtn = options.deleteImgBtn;

        if (!editor) return {};

        editor.addEventListener('click', e => {
            if (!e.target.closest('.doc-img-wrap')) deselectImage();
        });

        initResize(editor);
        initRibbon(root, editor, pageEl, options);

        if (paperSelect) {
            paperSelect.addEventListener('change', () => applyPaperSize(pageEl, paperSelect.value, root));
            applyPaperSize(pageEl, paperSelect.value || 'a4', root);
        } else {
            applyPaperSize(pageEl, 'a4', root);
        }

        if (imageInput) {
            imageInput.addEventListener('change', async () => {
                const file = imageInput.files?.[0];
                if (!file || !file.type.startsWith('image/')) return;
                if (file.size > 5 * 1024 * 1024) {
                    options.onToast?.('Image must be under 5MB.', true);
                    imageInput.value = '';
                    return;
                }
                try {
                    const dataUrl = await readFileAsDataUrl(file);
                    insertImage(editor, dataUrl, file.name);
                    options.onToast?.('Image inserted.');
                    updateWordCount(editor, root);
                } catch {
                    options.onToast?.('Could not load image.', true);
                }
                imageInput.value = '';
            });
        }

        if (cropBtn) {
            cropBtn.addEventListener('click', () => {
                const img = activeWrap?.querySelector('img');
                if (!img) {
                    options.onToast?.('Click an image first to crop.', true);
                    return;
                }
                openCropModal(img, newSrc => {
                    img.src = newSrc;
                    options.onToast?.('Image cropped.');
                });
            });
        }

        if (deleteImgBtn) {
            deleteImgBtn.addEventListener('click', () => {
                if (activeWrap) {
                    activeWrap.remove();
                    activeWrap = null;
                    options.onToast?.('Image removed.');
                }
            });
        }

        return {
            getPaperSize: () => pageEl.dataset.paperSize || 'a4',
            setPaperSize: key => {
                if (paperSelect) paperSelect.value = key;
                applyPaperSize(pageEl, key, root);
            },
            insertImageFromFile: async file => {
                const dataUrl = await readFileAsDataUrl(file);
                insertImage(editor, dataUrl, file.name);
            },
            updateWordCount: () => updateWordCount(editor, root),
        };
    }

    function hydrateImages(editor) {
        if (!editor) return;
        editor.querySelectorAll('img:not(.doc-img)').forEach(img => {
            const wrap = createImageWrap(img.src, img.alt);
            wrap.querySelector('img').style.cssText = img.style.cssText || 'width:280px';
            img.replaceWith(wrap);
        });
    }

    global.SkReportEditor = {
        PAPER_SIZES,
        init,
        hydrateImages,
        applyPaperSize,
    };
})(window);
