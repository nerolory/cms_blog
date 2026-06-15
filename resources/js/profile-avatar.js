/**
 * Avatar upload flow with Cropper.js modal and client-side JPEG export.
 *
 * @module resources/js/profile-avatar
 */

import { Modal } from 'bootstrap';

const input = document.getElementById('avatar');
const trigger = document.getElementById('avatar-upload-trigger');
const form = document.getElementById('avatar-upload-form');
const modalElement = document.getElementById('avatar-crop-modal');
const cropImage = document.getElementById('avatar-crop-image');
const saveButton = document.getElementById('avatar-crop-save');

if (!input || !trigger || !form || !modalElement || !cropImage || !saveButton) {
    // Profile avatar UI is not on this page.
} else if (typeof window.Cropper === 'undefined') {
    console.error('Cropper.js is not loaded — avatar cropping is disabled.');
} else {
    const modal = Modal.getOrCreateInstance(modalElement);
    /** @type {import('cropperjs').default | null} */
    let cropper = null;
    /** @type {string | null} */
    let objectUrl = null;

    /**
     * Destroy the cropper instance and revoke any temporary object URL.
     *
     * @returns {void}
     */
    const cleanup = () => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }

        cropImage.removeAttribute('src');
        saveButton.disabled = false;
    };

    /**
     * Create or recreate the Cropper.js instance on the preview image.
     *
     * @returns {void}
     */
    const initCropper = () => {
        if (cropper) {
            cropper.destroy();
        }

        cropper = new window.Cropper(cropImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
        });
    };

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) {
            return;
        }

        cleanup();
        objectUrl = URL.createObjectURL(file);
        cropImage.src = objectUrl;
        modal.show();
    });

    modalElement.addEventListener('shown.bs.modal', () => {
        if (cropImage.src) {
            initCropper();
        }
    });

    trigger.addEventListener('click', () => {
        input.click();
    });

    modalElement.addEventListener('hidden.bs.modal', cleanup);

    saveButton.addEventListener('click', () => {
        if (!cropper) {
            return;
        }

        saveButton.disabled = true;

        cropper.getCroppedCanvas({ width: 1024, height: 1024 }).toBlob(
            (blob) => {
                if (!blob) {
                    saveButton.disabled = false;
                    return;
                }

                const cropped = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(cropped);
                input.files = transfer.files;
                form.submit();
            },
            'image/jpeg',
            0.9,
        );
    });
}
