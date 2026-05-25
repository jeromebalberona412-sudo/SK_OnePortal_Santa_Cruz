/**
 * CKEditor 5 premium config for Make a Report (UMD globals from CDN).
 */
export function buildMakeReportEditorConfig({
    documentId,
    licenseKey,
    cloudTokenUrl,
    cloudWebSocketUrl,
    initialData = '',
    exportFileBase = 'sk-report',
    paperFormat = 'A4',
    mode = 'local',
    minimal = false,
}) {
    const CK = window.CKEDITOR;
    const PREMIUM = window.CKEDITOR_PREMIUM_FEATURES;

    if (!CK || !PREMIUM) {
        throw new Error('CKEditor 5 scripts are not loaded.');
    }

    if (minimal) {
        return buildMinimalEditorConfig({
            licenseKey,
            initialData,
            exportFileBase,
            paperFormat,
        });
    }

    const {
        Autosave,
        Alignment,
        AutoImage,
        Autoformat,
        AutoLink,
        ImageBlock,
        BlockQuote,
        Bold,
        Bookmark,
        CKBox,
        CKBoxImageEdit,
        CloudServices,
        Code,
        CodeBlock,
        Emoji,
        FindAndReplace,
        FontBackgroundColor,
        FontColor,
        FontFamily,
        FontSize,
        Fullscreen,
        GeneralHtmlSupport,
        Heading,
        Highlight,
        HorizontalLine,
        HtmlEmbed,
        ImageCaption,
        ImageEditing,
        ImageInsert,
        ImageInsertViaUrl,
        ImageResize,
        ImageStyle,
        ImageTextAlternative,
        ImageToolbar,
        ImageUpload,
        ImageUtils,
        ImageInline,
        Indent,
        IndentBlock,
        Italic,
        Link,
        LinkImage,
        List,
        ListProperties,
        MediaEmbed,
        Mention,
        MenuBar,
        PageBreak,
        PasteFromOffice,
        PictureEditing,
        PlainTableOutput,
        RemoveFormat,
        ShowBlocks,
        SpecialCharacters,
        SpecialCharactersArrows,
        SpecialCharactersCurrency,
        SpecialCharactersEssentials,
        SpecialCharactersLatin,
        SpecialCharactersMathematical,
        SpecialCharactersText,
        Strikethrough,
        Subscript,
        Superscript,
        Table,
        TableCaption,
        TableCellProperties,
        TableColumnResize,
        TableLayout,
        TableProperties,
        TableToolbar,
        TextPartLanguage,
        TextTransformation,
        TodoList,
        Underline,
        Undo,
        WordCount,
        BalloonToolbar,
        Essentials,
        Paragraph,
    } = CK;

    const {
        CaseChange,
        Comments,
        ExportPdf,
        ExportWord,
        ExportInlineStyles,
        Footnotes,
        FormatPainter,
        ImportWord,
        LineHeight,
        MergeFields,
        MultiLevelList,
        PasteFromOfficeEnhanced,
        PresenceList,
        RealTimeCollaborativeComments,
        RealTimeCollaborativeEditing,
        RealTimeCollaborativeRevisionHistory,
        RealTimeCollaborativeTrackChanges,
        RevisionHistory,
        SlashCommand,
        TableOfContents,
        Template,
        TrackChanges,
        TrackChangesData,
        TrackChangesPreview,
    } = PREMIUM;

    const useCollaboration = mode === 'collaboration';
    const cdnVersion = window.MR_CKEDITOR_CONFIG?.cdnVersion || '47.6.1';
    const stylesheetBase = `https://cdn.ckeditor.com/ckeditor5/${cdnVersion}`;

    const toolbarItems = [
        'undo', 'redo', '|',
        ...(useCollaboration ? ['trackChanges', 'comment', '|'] : []),
        'importWord', 'exportWord', 'exportPdf', 'showBlocks', 'formatPainter', 'caseChange',
        'findAndReplace', 'textPartLanguage', 'fullscreen', '|',
        'heading', '|',
        'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
        'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', 'code', 'removeFormat', '|',
        'specialCharacters', 'horizontalLine', 'pageBreak', 'link', 'insertFootnote', 'bookmark',
        'insertImage', 'insertImageViaUrl', 'ckbox', 'mediaEmbed', 'insertTable',
        'tableOfContents', 'insertTemplate', 'highlight', 'blockQuote', 'codeBlock', 'htmlEmbed', '|',
        'alignment', 'lineHeight', '|',
        'bulletedList', 'numberedList', 'multiLevelList', 'todoList', 'outdent', 'indent',
    ];

    const plugins = [
        Alignment, Autoformat, AutoImage, AutoLink, BalloonToolbar,
        BlockQuote, Bold, Bookmark, CaseChange, CKBox, CKBoxImageEdit,
        Code, CodeBlock, Essentials, ExportInlineStyles, ExportPdf, ExportWord,
        Emoji, FindAndReplace, FontBackgroundColor, FontColor, FontFamily, FontSize,
        Footnotes, FormatPainter, Fullscreen, GeneralHtmlSupport, Heading, Highlight,
        HorizontalLine, HtmlEmbed, ImageBlock, ImageCaption, ImageEditing, ImageInline,
        ImageInsert, ImageInsertViaUrl, ImageResize, ImageStyle, ImageTextAlternative,
        ImageToolbar, ImageUpload, ImageUtils, ImportWord, Indent, IndentBlock, Italic,
        LineHeight, Link, LinkImage, List, ListProperties, MediaEmbed, Mention, MergeFields,
        MultiLevelList, PageBreak, Paragraph, PasteFromOffice, PasteFromOfficeEnhanced,
        PictureEditing, PlainTableOutput, RemoveFormat, ShowBlocks,
        SlashCommand, SpecialCharacters, SpecialCharactersArrows, SpecialCharactersCurrency,
        SpecialCharactersEssentials, SpecialCharactersLatin, SpecialCharactersMathematical,
        SpecialCharactersText, Strikethrough, Subscript, Superscript, Table, TableCaption,
        TableCellProperties, TableColumnResize, TableLayout, TableOfContents, TableProperties,
        TableToolbar, Template, TextPartLanguage, TextTransformation, TodoList, Underline, Undo, WordCount,
    ];

    if (MenuBar) {
        plugins.unshift(MenuBar);
    }

    /* CKBox requires CloudServices even in local (non-collaboration) mode */
    if (CloudServices && cloudTokenUrl && !plugins.includes(CloudServices)) {
        plugins.push(CloudServices);
    }

    if (useCollaboration) {
        plugins.push(
            Autosave,
            Comments,
            PresenceList,
            RealTimeCollaborativeComments,
            RealTimeCollaborativeEditing,
            RealTimeCollaborativeRevisionHistory,
            RealTimeCollaborativeTrackChanges,
            RevisionHistory,
            TrackChanges,
            TrackChangesData,
            TrackChangesPreview,
        );
    }

    const config = {
        toolbar: {
            items: toolbarItems,
            shouldNotGroupWhenFull: false,
        },
        plugins: plugins.filter(Boolean),
        balloonToolbar: useCollaboration
            ? ['comment', '|', 'bold', 'italic', '|', 'link', 'insertImage', '|', 'bulletedList', 'numberedList']
            : ['bold', 'italic', '|', 'link', 'insertImage', '|', 'bulletedList', 'numberedList'],
        exportInlineStyles: {
            stylesheets: [
                `${stylesheetBase}/ckeditor5.css`,
                `https://cdn.ckeditor.com/ckeditor5-premium-features/${cdnVersion}/ckeditor5-premium-features.css`,
            ],
        },
        exportPdf: {
            stylesheets: [
                `${stylesheetBase}/ckeditor5.css`,
                `https://cdn.ckeditor.com/ckeditor5-premium-features/${cdnVersion}/ckeditor5-premium-features.css`,
            ],
            fileName: `${exportFileBase}.pdf`,
            converterOptions: {
                format: paperFormat,
                margin_top: '20mm',
                margin_bottom: '20mm',
                margin_right: '20mm',
                margin_left: '20mm',
                page_orientation: 'portrait',
            },
        },
        exportWord: {
            stylesheets: [
                `${stylesheetBase}/ckeditor5.css`,
                `https://cdn.ckeditor.com/ckeditor5-premium-features/${cdnVersion}/ckeditor5-premium-features.css`,
            ],
            fileName: `${exportFileBase}.docx`,
            converterOptions: {
                document: {
                    orientation: 'portrait',
                    size: paperFormat,
                    margins: { top: '20mm', bottom: '20mm', right: '20mm', left: '20mm' },
                },
            },
        },
        fontFamily: { supportAllValues: true },
        fontSize: { options: [10, 12, 14, 'default', 18, 20, 22], supportAllValues: true },
        fullscreen: {
            onEnterCallback: container => {
                container.classList.add(
                    'editor-container',
                    'editor-container_classic-editor',
                    'editor-container_include-annotations',
                    'editor-container_include-word-count',
                    'editor-container_include-fullscreen',
                    'mr-ckeditor-root'
                );
            },
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' },
            ],
        },
        htmlSupport: {
            allow: [{ name: /^.*$/, styles: true, attributes: true, classes: true }],
        },
        image: {
            toolbar: [
                'toggleImageCaption', 'imageTextAlternative', '|',
                'imageStyle:inline', 'imageStyle:wrapText', 'imageStyle:breakText', '|',
                'resizeImage', '|', 'ckboxImageEdit',
            ],
        },
        initialData,
        licenseKey,
        lineHeight: { supportAllValues: true },
        link: {
            addTargetToExternalLinks: true,
            defaultProtocol: 'https://',
            decorators: {
                toggleDownloadable: {
                    mode: 'manual',
                    label: 'Downloadable',
                    attributes: { download: 'file' },
                },
            },
        },
        list: { properties: { styles: true, startIndex: true, reversed: true } },
        mention: { feeds: [{ marker: '@', feed: [] }] },
        menuBar: { isVisible: true },
        mergeFields: {},
        table: {
            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties'],
        },
        template: { definitions: [] },
    };

    if (cloudTokenUrl) {
        config.cloudServices = {
            tokenUrl: cloudTokenUrl,
            ...(useCollaboration && cloudWebSocketUrl ? { webSocketUrl: cloudWebSocketUrl } : {}),
        };
    }

    if (useCollaboration) {
        config.collaboration = { channelId: documentId };
        config.comments = {
            editorConfig: {
                extraPlugins: [Autoformat, Bold, Italic, List, Mention],
                mention: { feeds: [{ marker: '@', feed: [] }] },
            },
        };
        const presenceEl = document.querySelector('#mr-editor-presence');
        if (presenceEl) {
            config.presenceList = { container: presenceEl };
        }
    }

    return config;
}

function buildMinimalEditorConfig({ licenseKey, initialData = '', exportFileBase = 'sk-report', paperFormat = 'A4' }) {
    const CK = window.CKEDITOR;
    const {
        Alignment,
        Autoformat,
        Bold,
        Essentials,
        Heading,
        Indent,
        Italic,
        Link,
        List,
        MenuBar,
        Paragraph,
        Strikethrough,
        Underline,
        Undo,
    } = CK;

    const plugins = [
        Essentials, Paragraph, Heading, Bold, Italic, Underline, Strikethrough,
        Link, List, Indent, Alignment, Autoformat, Undo,
    ];
    if (MenuBar) plugins.unshift(MenuBar);

    const cdnVersion = window.MR_CKEDITOR_CONFIG?.cdnVersion || '47.6.1';

    return {
        licenseKey,
        initialData,
        plugins: plugins.filter(Boolean),
        menuBar: { isVisible: true },
        toolbar: {
            items: [
                'undo', 'redo', '|',
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'bulletedList', 'numberedList', '|',
                'alignment', 'outdent', 'indent', '|',
                'link',
            ],
            shouldNotGroupWhenFull: false,
        },
        exportPdf: {
            stylesheets: [`https://cdn.ckeditor.com/ckeditor5/${cdnVersion}/ckeditor5.css`],
            fileName: `${exportFileBase}.pdf`,
            converterOptions: { format: paperFormat, page_orientation: 'portrait' },
        },
        exportWord: {
            stylesheets: [`https://cdn.ckeditor.com/ckeditor5/${cdnVersion}/ckeditor5.css`],
            fileName: `${exportFileBase}.docx`,
        },
    };
}

function canUseCollaboration(options) {
    return Boolean(
        options.licenseKey
        && options.cloudTokenUrl
        && options.cloudWebSocketUrl
        && options.documentId
    );
}

function getEditorClass() {
    const CK = window.CKEDITOR;
    return CK.DecoupledEditor || CK.ClassicEditor;
}

/** CKEditor marks a source element on create(); replace it before retrying. */
export function replaceEditorMount(element) {
    const parent = element?.parentNode;
    if (!parent) return element;

    const fresh = document.createElement('div');
    fresh.id = element.id || 'mrEditor';
    if (element.className) fresh.className = element.className;
    parent.replaceChild(fresh, element);
    return fresh;
}

export async function createMakeReportEditor(element, options) {
    const EditorClass = getEditorClass();
    if (!EditorClass) {
        throw new Error('CKEditor editor class is not available.');
    }

    const attempts = [
        { mode: 'local', minimal: false },
        ...(canUseCollaboration(options) ? [{ mode: 'collaboration', minimal: false }] : []),
        { mode: 'local', minimal: true },
    ];

    let lastError = null;
    let target = element;

    for (const attempt of attempts) {
        let editor = null;
        try {
            const config = buildMakeReportEditorConfig({ ...options, ...attempt });
            editor = await EditorClass.create(target, config);

            if (editor.isReady && typeof editor.isReady.then === 'function') {
                await editor.isReady;
            }

            const wordCountEl = document.querySelector('#mr-editor-word-count');
            if (wordCountEl) {
                wordCountEl.innerHTML = '';
            }

            editor._mrMode = attempt.minimal ? 'minimal' : attempt.mode;
            editor._mrIsDecoupled = EditorClass === window.CKEDITOR.DecoupledEditor;

            return editor;
        } catch (err) {
            lastError = err;
            console.warn(`[Make Report] CKEditor init (${attempt.mode}${attempt.minimal ? ', minimal' : ''}) failed:`, err);
            if (editor) {
                try {
                    await editor.destroy();
                } catch (_) { /* ignore */ }
            }
            target = replaceEditorMount(target);
        }
    }

    throw lastError || new Error('CKEditor failed to initialize.');
}
