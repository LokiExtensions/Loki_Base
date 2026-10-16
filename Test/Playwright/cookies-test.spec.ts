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

        test('should only add the secure flag on a secure connection', async ({page, context, baseURL}) => {
            await page.evaluate(() => MageCookies.set('loki_test_secure', 'foobar'));

            const cookie = (await context.cookies(baseURL as string)).find(c => c.name === 'loki_test_secure');
            expect(cookie, 'Cookie set via MageCookies.set()').toBeDefined();
            expect(cookie?.secure).toBe(new URL(baseURL as string).protocol === 'https:');
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

        test('should keep a malformed URI-encoded cookie as raw string', async ({page, context, baseURL}) => {
            await context.addCookies([{name: 'loki_test_malformed', value: '%E0%A4%A', url: baseURL as string}]);

            const result = await page.evaluate(() => {
                try {
                    return {value: MageCookies.get('loki_test_malformed'), error: null};
                } catch (e) {
                    return {value: null, error: (e as Error).message};
                }
            });

            expect(result.error).toBeNull();
            expect(result.value).toBe('%E0%A4%A');
        });

        test('should keep a cookie when the configured lifetime is 0 (browser session)', async ({page, context, baseURL}) => {
            await page.evaluate(() => {
                (MageCookies as any).lifetime = 0;
                MageCookies.set('loki_test_session', 'foobar');
            });

            expect(await page.evaluate(() => MageCookies.get('loki_test_session'))).toBe('foobar');

            const cookie = (await context.cookies(baseURL as string)).find(c => c.name === 'loki_test_session');
            expect(cookie?.expires).toBe(-1);
        });

        test('should use the configured lifetime for an invalid lifetime argument', async ({page}) => {
            const parts = await page.evaluate(() => {
                (MageCookies as any).lifetime = 3600;
                return (MageCookies as any).getCookieParts('foo', 'bar', -10);
            });

            expect(parts).toContain('max-age=3600');
        });
    });
}
