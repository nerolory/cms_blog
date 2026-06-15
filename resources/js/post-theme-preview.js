/**
 * Live preview for per-post theme colors, opacity, and background image.
 *
 * @module resources/js/post-theme-preview
 */

import {
    buildBackgroundImageStyle,
    buildThemeCssVars,
    formatOpacityLabel,
} from './lib/post-theme';

const root = document.querySelector('[data-post-theme-fields]');

if (root) {
    const preview = root.querySelector('[data-theme-preview]');
    const panel = root.querySelector('[data-theme-preview-panel]');
    const settings = root.querySelector('[data-theme-settings]');
    const toggle = root.querySelector('[data-theme-toggle]');
    const primaryInput = root.querySelector('[data-theme-input="primary"]');
    const accentInput = root.querySelector('[data-theme-input="accent"]');
    const opacityInput = root.querySelector('[data-theme-input="opacity"]');
    const opacityValue = root.querySelector('[data-content-opacity-value]');
    const backgroundField = document.querySelector('[data-image-field="background_image"] input[type="file"]');

    /**
     * Apply current color and opacity inputs to CSS custom properties on the preview panel.
     *
     * @returns {void}
     */
    const updatePreview = () => {
        if (!preview || !panel) {
            return;
        }

        const vars = buildThemeCssVars(
            primaryInput?.value,
            accentInput?.value,
            opacityInput?.value,
        );

        preview.style.setProperty('--post-primary', vars['--post-primary']);
        preview.style.setProperty('--post-accent', vars['--post-accent']);
        preview.style.setProperty('--post-content-opacity', vars['--post-content-opacity']);
        panel.style.setProperty('--post-content-opacity', vars['--post-content-opacity']);

        if (opacityValue && opacityInput) {
            opacityValue.textContent = formatOpacityLabel(Number(opacityInput.value));
        }
    };

    /**
     * Read the selected background file and set it as the preview background image.
     *
     * @returns {void}
     */
    const updateBackgroundPreview = () => {
        if (!preview || !backgroundField?.files?.[0]) {
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            if (typeof reader.result === 'string') {
                preview.style.backgroundImage = buildBackgroundImageStyle(reader.result);
            }
        };
        reader.readAsDataURL(backgroundField.files[0]);
    };

    toggle?.addEventListener('change', () => {
        settings?.classList.toggle('d-none', !toggle.checked);
        updatePreview();
    });

    [primaryInput, accentInput, opacityInput].forEach((input) => {
        input?.addEventListener('input', updatePreview);
        input?.addEventListener('change', updatePreview);
    });

    backgroundField?.addEventListener('change', updateBackgroundPreview);
    updatePreview();
}
