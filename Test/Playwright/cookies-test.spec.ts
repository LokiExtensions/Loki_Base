import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {configure, THEMES} from './helpers/configure';

declare const MageCookies: {
    get: (name: string) => unknown,
    getAll: () => Record<string, unknown>,
    set: (name: string, value: unknown, lifetime?: number) => void,
    remove: (name: string) => void,
};

for (const theme of THEMES) {
    test.describe(`MageCookies (${theme})`, () => {
        test.beforeEach(async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, ['loki_base']);
        });

        test('should set, get and remove a string cookie', async ({page}) => {
            const result = await page.evaluate(() => {
                MageCookies.set('loki_test_string', 'hello world; =&');
                const value = MageCookies.get('loki_test_string');
                MageCookies.remove('loki_test_string');
                return {value, removed: MageCookies.get('loki_test_string')};
            });

            expect(result.value).toBe('hello world; =&');
            expect(result.removed).toBeUndefined();
        });

        test('should encode and decode JSON values', async ({page}) => {
            const value = await page.evaluate(() => {
                MageCookies.set('loki_test_json', {foo: 'bar', list: [1, 2]});
                return MageCookies.get('loki_test_json');
            });

            expect(value).toEqual({foo: 'bar', list: [1, 2]});
        });

        test('should keep a foreign non-JSON cookie as raw string', async ({page, context, baseURL}) => {
            await context.addCookies([{name: 's_sq', value: '[[B]]', url: baseURL as string}]);

            const result = await page.evaluate(() => ({
                value: MageCookies.get('s_sq'),
                all: Object.keys(MageCookies.getAll()),
            }));

            expect(result.value).toBe('[[B]]');
            expect(result.all).toContain('s_sq');
        });
    });
}
