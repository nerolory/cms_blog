import { expect, type Page } from '@playwright/test';

/** Учётные данные из E2eFixturesSeeder. */
export const E2E_AUTHOR = {
    email: 'author1@example.com',
    password: 'password',
} as const;

export const E2E_DIRTY_POST_EDIT_PATH = '/posts/e2e-dirty-state/edit';

/**
 * Логин через web-форму /login.
 */
export async function loginAsAuthor(page: Page): Promise<void> {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(E2E_AUTHOR.email);
    await page.locator('input[name="password"]').fill(E2E_AUTHOR.password);
    await page.locator('button[type="submit"]').click();
    await expect(page).not.toHaveURL(/\/login$/);
}
