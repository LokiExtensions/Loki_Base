import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {configure, isHyva, THEMES, waitForAlpineStores} from './helpers/configure';

declare const Alpine: any;

for (const theme of THEMES) {
    test.describe(`Loki Alpine.js setup (${theme})`, () => {
        test.beforeEach(async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, ['loki_base']);
        });

        test('should load Alpine.js only once', async ({page}) => {
            const lokiAlpineScript = page.locator('script[src*="Loki_Base/js/alpine-csp.min.js"]');
            await expect(lokiAlpineScript).toHaveCount(isHyva(theme) ? 0 : 1);
            await page.waitForFunction(() => typeof (window as any).Alpine === 'object');
        });

        test('should register the Loki stores and components', async ({page}) => {
            await waitForAlpineStores(page);

            const registered = await page.evaluate(() => ({
                localStorage: typeof Alpine.store('LocalStorage'),
                message: typeof Alpine.store('Message'),
            }));

            expect(registered.localStorage).toBe('object');
            expect(registered.message).toBe('object');
            await expect(page.locator('[x-data="LokiMessagesComponent"]')).toHaveCount(1);
        });
    });
}
