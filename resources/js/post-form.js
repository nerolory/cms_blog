/**
 * Dirty-state tracking, TinyMCE sync, and save-button gating for the post edit form.
 *
 * @module resources/js/post-form
 */

import { Tooltip } from 'bootstrap';

import { buildPostTrackedFields, checkboxFlag, hasDirtyFields } from './lib/form-dirty-state';

const form = document.getElementById('post-edit-form');
const saveButton = document.getElementById('post-update-btn');
const saveWrapper = document.getElementById('post-update-wrapper');

if (form && saveButton && saveWrapper) {
    const disabledHint = form.dataset.saveDisabledHint ?? '';
    const trackedFields = buildPostTrackedFields({
        hasSlug: Boolean(form.elements.slug),
        hasUserId: Boolean(form.elements.user_id),
    });

    /** @type {Record<string, string> | null} Baseline after editor/fields are ready. */
    let baselineState = null;

    const tooltip = disabledHint
        ? new Tooltip(saveWrapper, {
            title: disabledHint,
            trigger: 'hover focus',
        })
        : null;

    /**
     * Persist TinyMCE editor content back into the hidden textarea before submit.
     *
     * @returns {void}
     */
    function syncRichText() {
        const editor = typeof tinymce !== 'undefined' ? tinymce.get('body') : null;
        editor?.save();
    }

    /**
     * Read the current value of a tracked form field, including rich text body.
     *
     * @param {string} name - Form field name.
     * @returns {string} Current field value.
     */
    function readFieldValue(name) {
        if (name === 'body') {
            const editor = typeof tinymce !== 'undefined' ? tinymce.get('body') : null;

            return editor ? editor.getContent() : (form.elements.body?.value ?? '');
        }

        const element = form.elements[name];
        if (element instanceof HTMLInputElement && element.type === 'checkbox') {
            return readCheckboxValue(name);
        }

        return element?.value ?? '';
    }

    /**
     * Read the published checkbox as a string flag.
     *
     * @returns {'0' | '1'} Published state.
     */
    function readPublishedValue() {
        return checkboxFlag(Boolean(form.elements.is_published?.checked));
    }

    /**
     * Read a checkbox field as a string flag.
     *
     * @param {string} name - Checkbox field name.
     * @returns {'0' | '1'} Checked state.
     */
    function readCheckboxValue(name) {
        return checkboxFlag(Boolean(form.elements[name]?.checked));
    }

    /**
     * Read the selected file name for a file input, or empty string if none.
     *
     * @param {string} name - File input field name.
     * @returns {string} Selected file name or empty string.
     */
    function readFileValue(name) {
        const input = form.elements[name];
        if (!input || !input.files || input.files.length === 0) {
            return '';
        }

        return input.files[0].name;
    }

    /**
     * Snapshot all tracked fields including checkboxes and file inputs.
     *
     * @returns {Record<string, string>} Current form state.
     */
    function readFormState() {
        return Object.fromEntries([
            ...trackedFields.map((name) => [name, readFieldValue(name)]),
            ['is_published', readPublishedValue()],
            ['remove_featured_image', readCheckboxValue('remove_featured_image')],
            ['remove_background_image', readCheckboxValue('remove_background_image')],
            ['featured_image', readFileValue('featured_image')],
            ['background_image', readFileValue('background_image')],
        ]);
    }

    /**
     * Snapshot baseline once the body field (TinyMCE or textarea) is readable.
     *
     * @returns {void}
     */
    function captureBaseline() {
        syncRichText();
        baselineState = Object.fromEntries([
            ...trackedFields.map((name) => [name, readFieldValue(name)]),
            ['is_published', readPublishedValue()],
            ['remove_featured_image', readCheckboxValue('remove_featured_image')],
            ['remove_background_image', readCheckboxValue('remove_background_image')],
            ['featured_image', ''],
            ['background_image', ''],
        ]);
    }

    /**
     * Determine whether any tracked field differs from the baseline snapshot.
     *
     * @returns {boolean} True when the form has unsaved changes.
     */
    function hasChanges() {
        if (baselineState === null) {
            return false;
        }

        return hasDirtyFields(baselineState, readFormState());
    }

    /**
     * Enable or disable the save button and tooltip based on dirty state.
     *
     * @returns {void}
     */
    function updateSaveButton() {
        if (saveButton.dataset.submitting === 'true') {
            return;
        }

        const dirty = hasChanges();
        saveButton.disabled = !dirty;
        saveButton.style.pointerEvents = dirty ? '' : 'none';

        if (tooltip) {
            if (dirty) {
                tooltip.disable();
            } else {
                tooltip.enable();
            }
        }
    }

    /**
     * Attach change listeners to the body field (TinyMCE or pro-mode textarea).
     *
     * @param {number} [attempt=0] - Retry counter when TinyMCE is not yet ready.
     * @returns {void}
     */
    function attachBodyChangeListener(attempt = 0) {
        const textarea = form.elements.body;
        const selectedMode = form.querySelector('[name="editor_mode"]:checked')?.value ?? 'simple';

        if (selectedMode === 'pro') {
            if (!textarea) {
                return;
            }

            if (!textarea.dataset.dirtyListenerBound) {
                textarea.addEventListener('input', updateSaveButton);
                textarea.dataset.dirtyListenerBound = 'true';
            }

            if (baselineState === null) {
                captureBaseline();
            }

            updateSaveButton();

            return;
        }

        const editor = typeof tinymce !== 'undefined' ? tinymce.get('body') : null;

        if (!editor) {
            if (attempt < 50) {
                window.setTimeout(() => attachBodyChangeListener(attempt + 1), 100);

                return;
            }

            if (baselineState === null) {
                captureBaseline();
            }

            updateSaveButton();

            return;
        }

        if (!editor._dirtyBaselineBound) {
            editor.on('change input undo redo SetContent', updateSaveButton);
            editor._dirtyBaselineBound = true;
        }

        const captureBaselineWhenReady = () => {
            window.requestAnimationFrame(() => {
                if (baselineState === null) {
                    captureBaseline();
                }

                updateSaveButton();
            });
        };

        if (editor.initialized) {
            captureBaselineWhenReady();
        } else {
            editor.once('init', captureBaselineWhenReady);
        }
    }

    form.addEventListener('input', updateSaveButton);
    form.addEventListener('change', updateSaveButton);

    form.addEventListener('submit', (event) => {
        syncRichText();

        if (!hasChanges()) {
            event.preventDefault();

            return;
        }

        if (saveButton.dataset.submitting === 'true') {
            event.preventDefault();

            return;
        }

        saveButton.disabled = true;
        saveButton.dataset.submitting = 'true';
        saveButton.style.pointerEvents = 'none';

        if (tooltip) {
            tooltip.disable();
        }
    });

    form.addEventListener('post-editor:reinitialized', () => {
        baselineState = null;
        attachBodyChangeListener();
    });

    attachBodyChangeListener();
}
