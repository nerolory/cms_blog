/**
 * TinyMCE and pro-mode HTML editor initialization for post body fields.
 *
 * @module resources/js/tinymce-editor
 */

/** @type {string} Base URL for TinyMCE assets derived from the script tag or fallback path. */
const tinymceBaseUrl = document.querySelector('script[src*="/vendor/tinymce/tinymce"]')?.src.replace(/\/tinymce(?:\.min)?\.js(?:\?.*)?$/, '') ?? '/vendor/tinymce';

if (typeof tinymce !== 'undefined') {
    tinymce.overrideDefaults({
        license_key: 'gpl',
        base_url: tinymceBaseUrl,
        suffix: '.min',
    });
}

/** @type {string} Space-separated list of TinyMCE plugins for simple mode. */
const SIMPLE_PLUGINS = [
    'accordion', 'advlist', 'autolink', 'autoresize', 'charmap', 'codesample',
    'fullscreen', 'image', 'insertdatetime', 'link', 'lists', 'media', 'preview',
    'searchreplace', 'table', 'visualblocks', 'wordcount',
].join(' ');

/** @type {string} Toolbar layout for simple editor mode. */
const SIMPLE_TOOLBAR = [
    'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor',
    'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
    'link image media table | postquote accordion codesample | removeformat preview fullscreen',
].join(' | ');

/**
 * @typedef {Object} CodeSampleLanguage
 * @property {string} text - Human-readable language label.
 * @property {string} value - Prism/TinyMCE language identifier.
 */

/** @type {CodeSampleLanguage[]} Languages offered in the codesample plugin dropdown. */
const CODE_SAMPLE_LANGUAGES = [
    { text: 'HTML/XML', value: 'markup' },
    { text: 'JavaScript', value: 'javascript' },
    { text: 'CSS', value: 'css' },
    { text: 'PHP', value: 'php' },
    { text: 'SQL', value: 'sql' },
    { text: 'Bash', value: 'bash' },
    { text: 'JSON', value: 'json' },
];

/** @type {string} Inline CSS injected into the TinyMCE editable iframe. */
const EDITOR_CONTENT_STYLE = `
    body { font-family: Instrument Sans, system-ui, sans-serif; font-size: 16px; line-height: 1.6; }
    img { max-width: 100%; height: auto; }
    blockquote.post-quote, blockquote {
        border-left: 4px solid #0d6efd;
        padding: 0.75rem 1rem;
        margin: 1rem 0;
        background: rgba(13, 110, 253, 0.08);
        border-radius: 0 0.375rem 0.375rem 0;
    }
    details.mce-accordion, details.post-spoiler {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        margin: 1rem 0;
    }
    details.mce-accordion summary, details.post-spoiler summary {
        cursor: pointer;
        font-weight: 600;
        padding: 0.75rem 1rem;
        list-style: none;
        background: #f8f9fa;
    }
    details.mce-accordion summary::-webkit-details-marker,
    details.post-spoiler summary::-webkit-details-marker { display: none; }
    details.mce-accordion .mce-accordion-body,
    details.post-spoiler .post-spoiler__body {
        padding: 0.75rem 1rem 1rem;
        border-top: 1px solid #dee2e6;
    }
    pre[class*="language-"], pre.post-code-block {
        tab-size: 4;
        white-space: pre;
        overflow: auto;
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 1rem 1rem 1rem 3.5rem;
        border-radius: 0.375rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 13px;
        line-height: 1.5;
        position: relative;
    }
    pre[class*="language-"] code, pre.post-code-block code {
        white-space: pre;
        tab-size: 4;
        font-family: inherit;
        font-size: inherit;
        background: transparent;
        color: inherit;
        display: block;
    }
    pre.line-numbers::before {
        content: attr(data-line-numbers);
        position: absolute;
        left: 0;
        top: 1rem;
        width: 2.75rem;
        text-align: right;
        padding-right: 0.75rem;
        color: #858585;
        white-space: pre;
        pointer-events: none;
        user-select: none;
        font-family: inherit;
        font-size: inherit;
        line-height: 1.5;
    }
`;

/**
 * Read the CSRF token from the page meta tag.
 *
 * @returns {string} CSRF token value or empty string if not found.
 */
const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

/**
 * Build a TinyMCE image upload handler that POSTs to the configured endpoint.
 *
 * @param {string} uploadUrl - Server endpoint that accepts multipart uploads.
 * @returns {(blobInfo: { blob: () => Blob, filename: () => string }, progress: (percent: number) => void) => Promise<string>}
 *   Upload handler resolving to the public image URL.
 */
const buildUploadHandler = (uploadUrl) => (blobInfo, progress) => new Promise((resolve, reject) => {
    if (!uploadUrl) {
        reject('Upload URL is not configured.');
        return;
    }

    const formData = new FormData();
    formData.append('file', blobInfo.blob(), blobInfo.filename());

    const xhr = new XMLHttpRequest();
    xhr.open('POST', uploadUrl);
    xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
    xhr.setRequestHeader('Accept', 'application/json');

    xhr.upload.onprogress = (event) => {
        if (event.lengthComputable) {
            progress((event.loaded / event.total) * 100);
        }
    };

    xhr.onload = () => {
        if (xhr.status < 200 || xhr.status >= 300) {
            reject(`Upload failed (${xhr.status}).`);
            return;
        }

        try {
            const payload = JSON.parse(xhr.responseText);
            if (!payload.location) {
                reject('Upload response is invalid.');
                return;
            }

            resolve(payload.location);
        } catch {
            reject('Upload response is invalid.');
        }
    };

    xhr.onerror = () => reject('Network error during upload.');
    xhr.send(formData);
});

/**
 * Resolve the editor mode from the textarea data attribute.
 *
 * @param {HTMLTextAreaElement} textarea - Rich text textarea element.
 * @returns {'pro' | 'simple'} Active editor mode.
 */
const getEditorMode = (textarea) => textarea.dataset.editorMode === 'pro' ? 'pro' : 'simple';

/**
 * Check whether pro-mode raw HTML editing is active on the textarea.
 *
 * @param {HTMLTextAreaElement | null | undefined} textarea - Target textarea.
 * @returns {boolean} True when pro-mode styling is applied.
 */
const isProEditorActive = (textarea) => textarea?.classList.contains('post-html-source-editor');

/**
 * Build a newline-separated gutter of line numbers for a code block.
 *
 * @param {string} codeText - Source code text inside the block.
 * @returns {string} Line numbers as a multi-line string.
 */
const buildLineNumberGutter = (codeText) => {
    const lineCount = Math.max(1, codeText.replace(/\n$/, '').split('\n').length);

    return Array.from({ length: lineCount }, (_, index) => String(index + 1)).join('\n');
};

/**
 * Add line-number gutters to all code blocks in the editor document.
 *
 * @param {import('tinymce').Editor} editor - TinyMCE editor instance.
 * @returns {void}
 */
const decorateCodeBlocks = (editor) => {
    editor.dom.select('pre[class*="language-"], pre.post-code-block').forEach((pre) => {
        pre.classList.add('line-numbers');
        const code = pre.querySelector('code');
        const text = code?.textContent ?? pre.textContent ?? '';
        pre.setAttribute('data-line-numbers', buildLineNumberGutter(text));
    });
};

/**
 * Wire Tab key handling and auto-decoration for code blocks inside TinyMCE.
 *
 * @param {import('tinymce').Editor} editor - TinyMCE editor instance.
 * @returns {void}
 */
const setupCodeBlockEditing = (editor) => {
    /**
     * Determine whether a DOM node is inside a code block element.
     *
     * @param {Node | null | undefined} node - Node under the caret.
     * @returns {boolean} True when the node is within a code block.
     */
    const isCodeBlockNode = (node) => {
        if (!node) {
            return false;
        }

        return Boolean(
            editor.dom.getParent(node, (candidate) => editor.dom.is(candidate, 'pre[class*="language-"]'))
            || editor.dom.getParent(node, (candidate) => editor.dom.is(candidate, 'pre.post-code-block')),
        );
    };

    editor.on('keydown', (event) => {
        if (event.key !== 'Tab' || !isCodeBlockNode(editor.selection.getNode())) {
            return;
        }

        event.preventDefault();

        if (event.shiftKey) {
            editor.execCommand('Outdent');

            return;
        }

        editor.insertContent('\t');
        decorateCodeBlocks(editor);
    });

    editor.on('SetContent Change NodeChange Undo Redo', () => {
        decorateCodeBlocks(editor);
    });

    editor.on('ExecCommand', (event) => {
        if (event.command === 'codesample') {
            window.setTimeout(() => decorateCodeBlocks(editor), 0);
        }
    });
};

/**
 * Register custom blockquote formatter and toolbar button for post quotes.
 *
 * @param {import('tinymce').Editor} editor - TinyMCE editor instance.
 * @returns {void}
 */
const registerContentBlocks = (editor) => {
    editor.formatter.register('postquote', {
        block: 'blockquote',
        classes: 'post-quote',
        wrapper: true,
    });

    editor.ui.registry.addToggleButton('postquote', {
        icon: 'quote',
        tooltip: 'Цитата',
        onAction: () => editor.formatter.toggle('postquote'),
        onSetup: (api) => {
            const handler = () => {
                api.setActive(editor.formatter.match('postquote'));
            };

            editor.on('NodeChange', handler);
            handler();

            return () => editor.off('NodeChange', handler);
        },
    });
};

/**
 * Build TinyMCE initialization options for simple (WYSIWYG) mode.
 *
 * @param {HTMLTextAreaElement} textarea - Target textarea element.
 * @returns {import('tinymce').RawEditorOptions} TinyMCE configuration object.
 */
const buildSimpleEditorConfig = (textarea) => {
    const uploadUrl = textarea.dataset.imageUploadUrl ?? '';

    return {
        target: textarea,
        license_key: 'gpl',
        base_url: tinymceBaseUrl,
        suffix: '.min',
        height: 480,
        menubar: 'edit view insert format tools table',
        plugins: SIMPLE_PLUGINS,
        toolbar: SIMPLE_TOOLBAR,
        branding: false,
        promotion: false,
        convert_urls: false,
        relative_urls: false,
        remove_script_host: false,
        image_title: true,
        automatic_uploads: true,
        codesample_languages: CODE_SAMPLE_LANGUAGES,
        images_upload_handler: buildUploadHandler(uploadUrl),
        content_style: EDITOR_CONTENT_STYLE,
        setup: (editor) => {
            editor.on('init', () => {
                editor.getElement()?.removeAttribute('required');
                registerContentBlocks(editor);
                setupCodeBlockEditing(editor);
                decorateCodeBlocks(editor);
            });

            const form = editor.getElement()?.form;
            if (!form) {
                return;
            }

            form.addEventListener('submit', () => {
                editor.save();
            });
        },
    };
};

/**
 * Enable pro-mode raw HTML editing on a textarea without TinyMCE.
 *
 * @param {HTMLTextAreaElement} textarea - Target textarea element.
 * @returns {void}
 */
const setupProEditor = (textarea) => {
    textarea.classList.add('post-html-source-editor');
    textarea.dataset.richTextInitialized = 'true';
    textarea.style.display = '';
    textarea.style.visibility = 'visible';
    textarea.removeAttribute('aria-hidden');
    textarea.setAttribute('spellcheck', 'false');
    textarea.setAttribute('autocapitalize', 'off');
    textarea.setAttribute('autocomplete', 'off');
    textarea.setAttribute('autocorrect', 'off');

    if (!textarea.dataset.proEditorBound) {
        textarea.addEventListener('keydown', (event) => {
            if (event.key !== 'Tab') {
                return;
            }

            event.preventDefault();

            const start = textarea.selectionStart ?? 0;
            const end = textarea.selectionEnd ?? 0;
            const value = textarea.value;
            const insertion = event.shiftKey ? '' : '\t';

            if (event.shiftKey) {
                const lineStart = value.lastIndexOf('\n', start - 1) + 1;
                if (value.slice(lineStart, lineStart + 1) === '\t') {
                    textarea.value = value.slice(0, lineStart) + value.slice(lineStart + 1);
                    textarea.selectionStart = Math.max(lineStart, start - 1);
                    textarea.selectionEnd = Math.max(lineStart, end - 1);
                }

                return;
            }

            textarea.value = value.slice(0, start) + insertion + value.slice(end);
            textarea.selectionStart = start + insertion.length;
            textarea.selectionEnd = start + insertion.length;
        });

        textarea.dataset.proEditorBound = 'true';
    }
};

/**
 * Remove pro-mode styling and attributes from a textarea.
 *
 * @param {HTMLTextAreaElement} textarea - Target textarea element.
 * @returns {void}
 */
const teardownProEditor = (textarea) => {
    textarea.classList.remove('post-html-source-editor');
    textarea.removeAttribute('spellcheck');
    textarea.removeAttribute('autocapitalize');
    textarea.removeAttribute('autocomplete');
    textarea.removeAttribute('autocorrect');
};

/**
 * Read editor content from TinyMCE or the underlying textarea value.
 *
 * @param {HTMLTextAreaElement | null | undefined} textarea - Target textarea.
 * @returns {string} Current HTML content.
 */
const readTextareaContent = (textarea) => {
    if (!textarea) {
        return '';
    }

    const editor = typeof tinymce !== 'undefined' ? tinymce.get(textarea.id) : null;

    if (editor) {
        return editor.getContent();
    }

    return textarea.value ?? '';
};

/**
 * Save and remove a TinyMCE editor instance by element id.
 *
 * @param {string} editorId - DOM id of the textarea bound to TinyMCE.
 * @returns {Promise<void>}
 */
const destroyEditor = async (editorId) => {
    const editor = typeof tinymce !== 'undefined' ? tinymce.get(editorId) : null;
    if (!editor) {
        return;
    }

    await editor.save();
    await tinymce.remove(`#${editorId}`);
};

/**
 * Initialize TinyMCE in simple mode on a single textarea.
 *
 * @param {HTMLTextAreaElement | null | undefined} textarea - Target textarea.
 * @returns {Promise<void>}
 */
const initSimpleEditor = async (textarea) => {
    if (!textarea || textarea.dataset.richTextInitialized === 'true') {
        return;
    }

    textarea.dataset.richTextInitialized = 'true';
    await tinymce.init(buildSimpleEditorConfig(textarea));
};

/**
 * Initialize the appropriate editor (simple or pro) for one textarea.
 *
 * @param {HTMLTextAreaElement | null | undefined} textarea - Target textarea.
 * @returns {Promise<void>}
 */
const initEditorForTextarea = async (textarea) => {
    if (!textarea || textarea.dataset.richTextInitialized === 'true') {
        return;
    }

    if (getEditorMode(textarea) === 'pro') {
        setupProEditor(textarea);
        return;
    }

    await initSimpleEditor(textarea);
};

/**
 * Tear down the current editor and reinitialize in the requested mode.
 *
 * @param {HTMLTextAreaElement | null | undefined} textarea - Target textarea.
 * @param {'pro' | 'simple'} mode - Editor mode to activate.
 * @returns {Promise<void>}
 */
const reinitEditor = async (textarea, mode) => {
    if (!textarea) {
        return;
    }

    const currentContent = readTextareaContent(textarea);

    if (typeof tinymce !== 'undefined' && tinymce.get(textarea.id)) {
        await destroyEditor(textarea.id);
    }

    if (isProEditorActive(textarea)) {
        teardownProEditor(textarea);
    }

    textarea.value = currentContent;
    textarea.dataset.editorMode = mode;
    textarea.dataset.richTextInitialized = 'false';

    if (mode === 'pro') {
        setupProEditor(textarea);
    } else {
        await initSimpleEditor(textarea);
    }

    textarea.dispatchEvent(new CustomEvent('post-editor:reinitialized', { bubbles: true, detail: { mode } }));
};

/**
 * Discover and initialize all rich-text textareas on the page.
 *
 * @returns {Promise<void>}
 */
const initRichTextEditors = async () => {
    const textareas = document.querySelectorAll('textarea[data-rich-text="true"]');
    if (textareas.length === 0) {
        return;
    }

    const needsTinyMce = Array.from(textareas).some((textarea) => getEditorMode(textarea) !== 'pro');

    if (needsTinyMce) {
        if (typeof tinymce === 'undefined') {
            console.error('TinyMCE is not loaded.');
            return;
        }

        tinymce.overrideDefaults({
            license_key: 'gpl',
            base_url: tinymceBaseUrl,
            suffix: '.min',
        });
    }

    for (const textarea of textareas) {
        await initEditorForTextarea(textarea);
    }

    document.querySelectorAll('[data-editor-mode-input="true"]').forEach((input) => {
        input.addEventListener('change', async () => {
            if (!input.checked) {
                return;
            }

            const textarea = document.querySelector('textarea[data-rich-text="true"]');
            await reinitEditor(textarea, input.value);
        });
    });
};

/** @type {(textarea: HTMLTextAreaElement | null | undefined, mode: 'pro' | 'simple') => Promise<void>} */
window.reinitPostEditor = reinitEditor;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initRichTextEditors();
    });
} else {
    initRichTextEditors();
}
