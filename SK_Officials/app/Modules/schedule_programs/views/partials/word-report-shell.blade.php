{{-- MS Word–style editor shell. Requires: $editorId, $pageId, $paperSelectId, $imageInputId, $cropBtnId, $deleteImgBtnId --}}
@php
    $shellId = $shellId ?? 'wordEditorShell';
    $showGenerate = $showGenerate ?? false;
    $generateId = $generateId ?? null;
    $showPrint = $showPrint ?? true;
    $printId = $printId ?? null;
@endphp
<div class="word-editor-shell" id="{{ $shellId }}">
    <div class="word-ribbon">
        <nav class="word-ribbon-tabs" aria-label="Editor ribbon">
            <button type="button" class="word-ribbon-tab is-active" data-ribbon-tab="home">Home</button>
            <button type="button" class="word-ribbon-tab" data-ribbon-tab="insert">Insert</button>
            <button type="button" class="word-ribbon-tab" data-ribbon-tab="layout">Layout</button>
        </nav>

        <div class="word-ribbon-panels">
            {{-- HOME --}}
            <div class="word-ribbon-panel is-active" data-ribbon-panel="home">
                <div class="word-ribbon-group">
                    <span class="word-ribbon-group-title">Clipboard</span>
                    <div class="word-ribbon-group-body word-ribbon-group-body--clip">
                        <button type="button" class="word-tool word-tool--clip" data-cmd="cut" title="Cut">
                            <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M9.64 7.64 4.36 2.36 2 4.72l5.28 5.28zm0 8.72-5.28 5.28L2 19.28l5.28-5.28zM20 4.72l-1.41-1.41-5.28 5.28 1.41 1.41zM15.36 16.36l-1.41 1.41 5.28 5.28 1.41-1.41z"/></svg>
                        </button>
                        <button type="button" class="word-tool word-tool--clip" data-cmd="copy" title="Copy">
                            <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                        </button>
                        <button type="button" class="word-tool word-tool--clip word-tool--paste" data-cmd="paste" title="Paste">
                            <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M19 2h-4.18C14.4.84 13.3 0 12 0S9.6.84 9.18 2H5c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm7 18H5V4h2v3h10V4h2v16z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="word-ribbon-group word-ribbon-group--font">
                    <span class="word-ribbon-group-title">Font</span>
                    <div class="word-ribbon-group-body">
                        <select class="word-select word-font-family" data-font-family aria-label="Font">
                            <option value="Calibri">Calibri</option>
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Tahoma">Tahoma</option>
                        </select>
                        <select class="word-select word-font-size" data-font-size aria-label="Font size">
                            <option value="1">8</option>
                            <option value="2">10</option>
                            <option value="3" selected>12</option>
                            <option value="4">14</option>
                            <option value="5">18</option>
                            <option value="6">24</option>
                            <option value="7">36</option>
                        </select>
                        <div class="word-tool-row">
                            <button type="button" class="word-tool" data-cmd="bold" title="Bold (Ctrl+B)"><b>B</b></button>
                            <button type="button" class="word-tool" data-cmd="italic" title="Italic (Ctrl+I)"><i>I</i></button>
                            <button type="button" class="word-tool" data-cmd="underline" title="Underline (Ctrl+U)"><u>U</u></button>
                            <button type="button" class="word-tool" data-cmd="strikeThrough" title="Strikethrough"><s>ab</s></button>
                            <button type="button" class="word-tool" data-cmd="subscript" title="Subscript">x<sub>2</sub></button>
                            <button type="button" class="word-tool" data-cmd="superscript" title="Superscript">x<sup>2</sup></button>
                            <label class="word-tool word-tool--color" title="Font color">
                                <span>A</span>
                                <input type="color" data-font-color value="#000000" aria-label="Font color">
                            </label>
                            <label class="word-tool word-tool--highlight" title="Text highlight">
                                <svg viewBox="0 0 24 24" width="16" height="16"><path fill="#f5c518" d="M15.24 2.29l-1.44 1.44 6.04 6.04 1.44-1.44a2 2 0 0 0 0-2.83l-3.21-3.21a2 2 0 0 0-2.83 0zM4.58 13.47l-2.5 7.5 7.5-2.5L19.07 7.93l-5-5L4.58 13.47z"/></svg>
                                <input type="color" data-highlight-color value="#ffff00" aria-label="Highlight">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="word-ribbon-group word-ribbon-group--paragraph">
                    <span class="word-ribbon-group-title">Paragraph</span>
                    <div class="word-ribbon-group-body">
                        <div class="word-tool-row">
                            <button type="button" class="word-tool word-align-btn" data-cmd="justifyLeft" title="Align Left">
                                <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M3 4h18v2H3V4zm0 5h12v2H3V9zm0 5h18v2H3v-2zm0 5h12v2H3v-2z"/></svg>
                            </button>
                            <button type="button" class="word-tool word-align-btn" data-cmd="justifyCenter" title="Center">
                                <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M3 4h18v2H3V4zm3 5h12v2H6V9zm-3 5h18v2H3v-2zm3 5h12v2H6v-2z"/></svg>
                            </button>
                            <button type="button" class="word-tool word-align-btn" data-cmd="justifyRight" title="Align Right">
                                <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M3 4h18v2H3V4zm6 5h12v2H9V9zm-6 5h18v2H3v-2zm6 5h12v2H9v-2z"/></svg>
                            </button>
                            <button type="button" class="word-tool word-align-btn" data-cmd="justifyFull" title="Justify">
                                <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M3 4h18v2H3V4zm0 5h18v2H3V9zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/></svg>
                            </button>
                        </div>
                        <div class="word-tool-row">
                            <button type="button" class="word-tool" data-cmd="insertUnorderedList" title="Bullets">•</button>
                            <button type="button" class="word-tool" data-cmd="insertOrderedList" title="Numbering">1.</button>
                            <button type="button" class="word-tool" data-cmd="outdent" title="Decrease indent">⇤</button>
                            <button type="button" class="word-tool" data-cmd="indent" title="Increase indent">⇥</button>
                            <select class="word-select word-line-spacing" data-line-spacing aria-label="Line spacing">
                                <option value="1">1.0</option>
                                <option value="1.15">1.15</option>
                                <option value="1.5" selected>1.5</option>
                                <option value="2">2.0</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="word-ribbon-group word-ribbon-group--styles">
                    <span class="word-ribbon-group-title">Styles</span>
                    <div class="word-ribbon-group-body word-style-gallery">
                        <button type="button" class="word-style-chip is-active" data-block-style="p">Normal</button>
                        <button type="button" class="word-style-chip" data-block-style="h1">Heading 1</button>
                        <button type="button" class="word-style-chip" data-block-style="h2">Heading 2</button>
                        <button type="button" class="word-style-chip" data-block-style="h3">Heading 3</button>
                    </div>
                </div>
            </div>

            {{-- INSERT --}}
            <div class="word-ribbon-panel" data-ribbon-panel="insert">
                <div class="word-ribbon-group">
                    <span class="word-ribbon-group-title">Illustrations</span>
                    <div class="word-ribbon-group-body">
                        <label class="word-insert-btn" title="Insert picture">
                            <svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                            <span>Picture</span>
                            <input type="file" id="{{ $imageInputId }}" accept="image/*" hidden>
                        </label>
                        <button type="button" class="word-insert-btn" id="{{ $cropBtnId }}" title="Crop picture">
                            <span>Crop</span>
                        </button>
                        <button type="button" class="word-insert-btn" id="{{ $deleteImgBtnId }}" title="Delete picture">
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
                <div class="word-ribbon-group">
                    <span class="word-ribbon-group-title">Links</span>
                    <div class="word-ribbon-group-body">
                        <button type="button" class="word-insert-btn" data-cmd-link title="Insert hyperlink">Link</button>
                    </div>
                </div>
            </div>

            {{-- LAYOUT --}}
            <div class="word-ribbon-panel" data-ribbon-panel="layout">
                <div class="word-ribbon-group word-ribbon-group--layout">
                    <span class="word-ribbon-group-title">Page Setup</span>
                    <div class="word-ribbon-group-body">
                        <label class="word-layout-label">Size</label>
                        <select id="{{ $paperSelectId }}" class="word-select word-paper-select" aria-label="Paper size">
                            <option value="a4">A4 (210 × 297 mm)</option>
                            <option value="letter">Letter / Short (8.5 × 11 in)</option>
                            <option value="long">Long Bond (8.5 × 13 in)</option>
                            <option value="legal">Legal (8.5 × 14 in)</option>
                            <option value="folio">Folio (8.5 × 13 in)</option>
                        </select>
                        <div class="word-page-preview" data-page-preview aria-hidden="true">
                            <div class="word-page-preview-sheet"></div>
                        </div>
                    </div>
                </div>
                <div class="word-ribbon-group">
                    <span class="word-ribbon-group-title">Paragraph</span>
                    <div class="word-ribbon-group-body">
                        <button type="button" class="word-tool word-align-btn" data-cmd="justifyLeft" title="Align Left">Left</button>
                        <button type="button" class="word-tool word-align-btn" data-cmd="justifyCenter" title="Center">Center</button>
                        <button type="button" class="word-tool word-align-btn" data-cmd="justifyRight" title="Right">Right</button>
                        <button type="button" class="word-tool word-align-btn" data-cmd="justifyFull" title="Justify">Justify</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="word-workspace" data-word-workspace>
        <div class="word-workspace-scroll">
            <div class="word-zoom-wrap" data-word-zoom-wrap style="--word-zoom: 1">
                <div id="{{ $pageId }}" class="scholar-report-page paper-a4 word-page-sheet" data-word-page>
                    <div id="{{ $editorId }}" class="scholar-report-editor word-page-content" contenteditable="true" spellcheck="true"
                         data-placeholder="{{ $placeholder ?? 'Start typing your document here...' }}"></div>
                </div>
            </div>
        </div>
    </div>

    <footer class="word-status-bar">
        <span class="word-status-item" data-word-count>0 words</span>
        <span class="word-status-divider">|</span>
        <span class="word-status-item" data-word-paper>A4</span>
        <span class="word-status-divider">|</span>
        <span class="word-status-item">English (Philippines)</span>
        <span class="word-status-spacer"></span>
        @if($showGenerate && $generateId)
        <button type="button" class="word-status-action" id="{{ $generateId }}">Generate</button>
        @endif
        @if($showPrint && $printId)
        <button type="button" class="word-status-action" id="{{ $printId }}">Print</button>
        @endif
        <div class="word-status-zoom">
            <button type="button" class="word-zoom-btn" data-zoom-out title="Zoom out">−</button>
            <input type="range" class="word-zoom-slider" data-zoom-slider min="50" max="150" value="100" aria-label="Zoom">
            <button type="button" class="word-zoom-btn" data-zoom-in title="Zoom in">+</button>
            <span class="word-zoom-label" data-zoom-label>100%</span>
        </div>
    </footer>
</div>
