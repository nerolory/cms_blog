import { expect, test } from '@playwright/test';

import { E2E_DIRTY_POST_EDIT_PATH, loginAsAuthor } from '../fixtures/auth';

test.describe('Form dirty-state (Save button)', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAuthor(page);
    });

    test('post edit keeps update disabled until a field changes', async ({ page }) => {
        await page.goto(E2E_DIRTY_POST_EDIT_PATH);
        await expect(page.locator('#post-edit-form')).toBeVisible();

        const saveButton = page.locator('#post-update-btn');
        await expect(saveButton).toBeDisabled({ timeout: 15_000 });

        await page.locator('input[name="title"]').fill('E2E dirty state changed title');
        await expect(saveButton).toBeEnabled();

        await page.locator('input[name="title"]').fill('E2E dirty state fixture');
        await expect(saveButton).toBeDisabled();
    });

    test('profile edit keeps save disabled until a field changes', async ({ page }) => {
        await page.goto('/profile');
        await expect(page.locator('#profile-form')).toBeVisible();

        await page.locator('input[name="password"]').fill('');
        await page.locator('input[name="password_confirmation"]').fill('');

        const saveButton = page.locator('#profile-save-btn');
        await expect(saveButton).toBeDisabled({ timeout: 15_000 });

        await page.locator('input[name="name"]').fill('E2E Author Renamed');
        await expect(saveButton).toBeEnabled();
    });
});
