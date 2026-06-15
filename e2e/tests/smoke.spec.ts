import { expect, test } from '@playwright/test';

test.describe('Smoke', () => {
    test('home page loads', async ({ page }) => {
        const response = await page.goto('/');
        expect(response?.status()).toBeLessThan(400);
    });

    test('posts index loads', async ({ page }) => {
        const response = await page.goto('/posts');
        expect(response?.status()).toBeLessThan(400);
    });

    test('health endpoint returns JSON', async ({ request }) => {
        const response = await request.get('/health');
        expect(response.status()).toBeLessThanOrEqual(503);

        const body = await response.json();
        expect(body).toHaveProperty('status');
        expect(body).toHaveProperty('checks');
    });

    test('response includes X-Request-Id', async ({ request }) => {
        const response = await request.get('/posts');
        expect(response.headers()['x-request-id']).toBeTruthy();
    });
});

test.describe('Search (F10)', () => {
    test('search page loads when route exists', async ({ page }) => {
        const response = await page.goto('/search', { waitUntil: 'domcontentloaded' });

        if (response?.status() === 404) {
            test.skip(true, 'Search route not deployed yet (F10).');
        }

        expect(response?.status()).toBeLessThan(400);
    });
});

test.describe('Comments (F11)', () => {
    test('comment form visible on post show when engagement UI exists', async ({ page }) => {
        await page.goto('/posts');

        const firstPostLink = page.locator('a[href*="/posts/"]').first();

        if ((await firstPostLink.count()) === 0) {
            test.skip(true, 'No published posts to test comments.');
        }

        await firstPostLink.click();

        const commentForm = page.locator('[data-testid="comment-form"], form[action*="comments"]');

        if ((await commentForm.count()) === 0) {
            test.skip(true, 'Comment UI not deployed yet (F11).');
        }

        await expect(commentForm.first()).toBeVisible();
    });
});
