import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {configure, THEMES} from './helpers/configure';

declare const BASE_URL: string;
declare const LOKI_LOCALE: string;
declare const LOKI_THEME_URL: string;
declare const LOKI_FORM_KEY: string;
declare const LOKI_CURRENT_URL: string;
declare const LOKI_IS_MOBILE: boolean;

for (const theme of THEMES) {
    test.describe(`Loki variables (${theme})`, () => {
        test.beforeEach(async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, ['loki_base']);
        });

        test('should define the global Loki variables', async ({page, baseURL}) => {
            const variables = await page.evaluate(() => ({
                baseUrl: BASE_URL,
                locale: LOKI_LOCALE,
                themeUrl: LOKI_THEME_URL,
                formKey: LOKI_FORM_KEY,
                currentUrl: LOKI_CURRENT_URL,
            }));

            expect(variables.baseUrl.startsWith(new URL(baseURL as string).origin)).toBeTruthy();
            expect(variables.locale).toBe('en-US');
            expect(variables.themeUrl).toContain('/frontend/' + theme + '/');
            expect(variables.formKey).toMatch(/^[a-zA-Z0-9]{16}$/);
            expect(variables.currentUrl).not.toContain('?');
            expect(page.url().startsWith(variables.currentUrl)).toBeTruthy();
        });

        test('should detect a desktop viewport', async ({page}) => {
            await page.setViewportSize({width: 1280, height: 800});
            await page.reload();
            expect(await page.evaluate(() => LOKI_IS_MOBILE)).toBe(false);
        });

        test('should detect a mobile viewport', async ({page}) => {
            await page.setViewportSize({width: 375, height: 800});
            await page.reload();
            expect(await page.evaluate(() => LOKI_IS_MOBILE)).toBe(true);
        });
    });
}
