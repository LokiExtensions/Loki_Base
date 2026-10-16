import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {configure, THEMES, waitForAlpineStores} from './helpers/configure';

declare const Alpine: any;

const UNSAFE_HTML = '<strong>Bold</strong> <em>Italic</em>'
    + '<script>window.__lokiXss = "script"</script>'
    + '<img src="x" onerror="window.__lokiXss = \'img\'">'
    + '<div>Unwrapped</div>'
    + '<a href="javascript:window.__lokiXss = \'href\'" onclick="window.__lokiXss = \'onclick\'">Link</a>';

for (const theme of THEMES) {
    test.describe(`Loki x-safe-html directive (${theme})`, () => {
        test.beforeEach(async ({page}) => {
            await configure(page, {theme, config: {'loki_base/messages/timeout': 0}});
            await openTestPage(page, ['loki_base']);
            await waitForAlpineStores(page);
        });

        test('should only render allowed HTML in messages', async ({page}) => {
            await page.evaluate((html) => Alpine.store('Message').addErrorMessage(html), UNSAFE_HTML);

            const text = page.locator('[x-data="LokiMessagesComponent"] div[data-message-type="error"] [x-safe-html]');
            await expect(text).toBeVisible();

            await expect(text.locator('strong')).toHaveText('Bold');
            await expect(text.locator('em')).toHaveText('Italic');
            await expect(text.locator('script')).toHaveCount(0);
            await expect(text.locator('img')).toHaveCount(0);
            await expect(text.locator('div')).toHaveCount(0);
            await expect(text).toContainText('Unwrapped');

            const link = text.locator('a');
            await expect(link).toHaveText('Link');
            await expect(link).not.toHaveAttribute('href');
            await expect(link).not.toHaveAttribute('onclick');

            await link.click();
            await page.waitForTimeout(500);
            expect(await page.evaluate(() => (window as any).__lokiXss)).toBeUndefined();
        });

        for (const href of [
            'JavaScript:window.__lokiXss = 1',
            ' javascript:window.__lokiXss = 1',
            'java&#x09;script:window.__lokiXss = 1',
            'vbscript:window.__lokiXss = 1',
            'data:text/html,<script>window.__lokiXss = 1</script>',
        ]) {
            test(`should strip an unsafe href: ${href}`, async ({page}) => {
                await page.evaluate((html) => Alpine.store('Message').addErrorMessage(html), `<a href="${href}">Unsafe</a>`);

                const link = page.locator('[x-data="LokiMessagesComponent"] div[data-message-type="error"] [x-safe-html] a');
                await expect(link).toHaveText('Unsafe');
                await expect(link).not.toHaveAttribute('href');
            });
        }

        test('should keep a safe href', async ({page}) => {
            await page.evaluate(() => Alpine.store('Message').addErrorMessage('<a href="/customer/account">Account</a>'));

            const link = page.locator('[x-data="LokiMessagesComponent"] div[data-message-type="error"] [x-safe-html] a');
            await expect(link).toHaveAttribute('href', '/customer/account');
        });
    });
}
