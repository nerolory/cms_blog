/**
 * Dirty-state tracking and save-button gating for the profile edit form.
 *
 * @module resources/js/profile-form
 */

import { Tooltip } from 'bootstrap';

import { hasDirtyFields, PROFILE_TRACKED_FIELDS } from './lib/form-dirty-state';

const form = document.getElementById('profile-form');
const saveButton = document.getElementById('profile-save-btn');
const saveWrapper = document.getElementById('profile-save-wrapper');

if (form && saveButton && saveWrapper) {
    const disabledHint = form.dataset.saveDisabledHint ?? '';
    const trackedFields = [...PROFILE_TRACKED_FIELDS];

    /** @type {Record<string, string>} Initial field values used to detect changes. */
    const baselineState = {
        name: form.dataset.initialName ?? '',
        email: form.dataset.initialEmail ?? '',
        theme: form.dataset.initialTheme ?? '',
        password: '',
        password_confirmation: '',
    };

    /**
     * Read current values of all tracked form fields.
     *
     * @returns {Record<string, string>} Field name to value mapping.
     */
    const readFormState = () => Object.fromEntries(
        trackedFields.map((name) => [name, form.elements[name]?.value ?? '']),
    );

    /**
     * Determine whether any tracked field differs from the baseline.
     *
     * @returns {boolean} True when the form has unsaved changes.
     */
    const hasChanges = () => hasDirtyFields(baselineState, readFormState());

    const tooltip = disabledHint
        ? new Tooltip(saveWrapper, {
            title: disabledHint,
            trigger: 'hover focus',
        })
        : null;

    /**
     * Enable or disable the save button based on dirty state.
     *
     * @returns {void}
     */
    const updateSaveButton = () => {
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
    };

    form.addEventListener('input', updateSaveButton);
    form.addEventListener('change', updateSaveButton);

    form.addEventListener('submit', (event) => {
        if (!hasChanges()) {
            event.preventDefault();
        }
    });

    updateSaveButton();
}
