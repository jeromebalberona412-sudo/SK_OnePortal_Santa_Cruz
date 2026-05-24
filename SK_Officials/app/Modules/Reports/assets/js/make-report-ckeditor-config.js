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
}) {
    const CK = window.CKEDITOR;
    const PREMIUM = window.CKEDITOR_PREMIUM_FEATURES;

    if (!CK || !PREMIUM) {
        throw new Error('CKEditor 5 scripts are not loaded.');
    }

    const {
        ClassicEditor,
        Autosave,
        Essentials,
        Paragraph,
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
        WordCount,
        BalloonToolbar,
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

    const cdnVersion = window.MR_CKEDITOR_CONFIG?.cdnVersion || '47.6.1';
    const stylesheetBase = `https://cdn.ckeditor.com/ckeditor5/${cdnVersion}`;

    return {
        toolbar: {
            items: [
                'undo', 'redo', '|',
                'trackChanges', 'comment', '|',
                'insertMergeField', 'previewMergeFields', '|',
                'importWord', 'exportWord', 'exportPdf', 'showBlocks', 'formatPainter', 'caseChange',
                'findAndReplace', 'textPartLanguage', 'fullscreen', '|',
                'heading', '|',
                'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', 'code', 'removeFormat', '|',
                'specialCharacters', 'horizontalLine', 'pageBreak', 'link', 'insertFootnote', 'bookmark',
                'insertImage', 'insertImageViaUrl', 'ckbox', 'mediaEmbed', 'insertTable', 'insertTableLayout',
                'tableOfContents', 'insertTemplate', 'highlight', 'blockQuote', 'codeBlock', 'htmlEmbed', '|',
                'alignment', 'lineHeight', '|',
                'bulletedList', 'numberedList', 'multiLevelList', 'todoList', 'outdent', 'indent',
            ],
            shouldNotGroupWhenFull: false,
        },
        plugins: [
            Alignment, Autoformat, AutoImage, AutoLink, Autosave, BalloonToolbar,
            BlockQuote, Bold, Bookmark, CaseChange, CKBox, CKBoxImageEdit, CloudServices,
            Code, CodeBlock, Comments, Essentials, ExportInlineStyles, ExportPdf, ExportWord,
            Emoji, FindAndReplace, FontBackgroundColor, FontColor, FontFamily, FontSize,
            Footnotes, FormatPainter, Fullscreen, GeneralHtmlSupport, Heading, Highlight,
            HorizontalLine, HtmlEmbed, ImageBlock, ImageCaption, ImageEditing, ImageInline,
            ImageInsert, ImageInsertViaUrl, ImageResize, ImageStyle, ImageTextAlternative,
            ImageToolbar, ImageUpload, ImageUtils, ImportWord, Indent, IndentBlock, Italic,
            LineHeight, Link, LinkImage, List, ListProperties, MediaEmbed, Mention, MergeFields,
            MultiLevelList, PageBreak, Paragraph, PasteFromOffice, PasteFromOfficeEnhanced,
            PictureEditing, PlainTableOutput, PresenceList, RealTimeCollaborativeComments,
            RealTimeCollaborativeEditing, RealTimeCollaborativeRevisionHistory,
            RealTimeCollaborativeTrackChanges, RemoveFormat, RevisionHistory, ShowBlocks,
            SlashCommand, SpecialCharacters, SpecialCharactersArrows, SpecialCharactersCurrency,
            SpecialCharactersEssentials, SpecialCharactersLatin, SpecialCharactersMathematical,
            SpecialCharactersText, Strikethrough, Subscript, Superscript, Table, TableCaption,
            TableCellProperties, TableColumnResize, TableLayout, TableOfContents, TableProperties,
            TableToolbar, Template, TextPartLanguage, TextTransformation, TodoList, TrackChanges,
            TrackChangesData, TrackChangesPreview, Underline, WordCount,
        ],
        balloonToolbar: [
            'comment', '|', 'bold', 'italic', '|',
            'link', 'insertImage', '|', 'bulletedList', 'numberedList',
        ],
        cloudServices: {
            tokenUrl: cloudTokenUrl,
            webSocketUrl: cloudWebSocketUrl,
        },
        collaboration: { channelId: documentId },
        comments: {
            editorConfig: {
                extraPlugins: [Autoformat, Bold, Italic, List, Mention],
                mention: { feeds: [{ marker: '@', feed: [] }] },
            },
        },
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
        presenceList: { container: document.querySelector('#mr-editor-presence') || undefined },
        table: {
            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties'],
        },
        template: { definitions: [] },
    };
}

export async function createMakeReportEditor(element, options) {
    const CK = window.CKEDITOR;
    const EditorClass = CK.DecoupledEditor || CK.ClassicEditor;
    const config = buildMakeReportEditorConfig(options);
    const editor = await EditorClass.create(element, config);

    const wordCount = editor.plugins.get('WordCount');
    const wordCountEl = document.querySelector('#mr-editor-word-count');
    if (wordCountEl && wordCount?.wordCountContainer) {
        wordCountEl.innerHTML = '';
        wordCountEl.appendChild(wordCount.wordCountContainer);
    }

    return editor;
}
