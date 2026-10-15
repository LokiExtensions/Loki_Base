import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {configure, THEMES, waitForAlpineStores} from './helpers/configure';

declare const Alpine: any;

const STORAGE_KEY = 'mage-cache-storage';

for (const theme of THEMES) {
    test.describe(`Loki LocalStorage store (${theme})`, () => {
        test.beforeEach(async ({page}) => {
            await configure(page, {theme});
            await openTestPage(page, ['loki_base']);
            await waitForAlpineStores(page);
        });

        test('should set, get, remove and reset sections', async ({page}) => {
            const result = await page.evaluate(async (storageKey) => {
                const store = Alpine.store('LocalStorage');
                const section = {data_id: store.getCurrentTimestamp(), value: 'foo'};

                store.set('loki_test', section);
                const stored = JSON.parse(localStorage.getItem(storageKey) || '{}');
                const value = await store.get('loki_test');

                store.remove('loki_test');
                const afterRemove = JSON.parse(localStorage.getItem(storageKey) || '{}');

                store.set('loki_test', section);
                store.reset();

                return {
                    stored: stored.loki_test,
                    value: JSON.parse(JSON.stringify(value)),
                    afterRemove: afterRemove.loki_test,
                    afterReset: localStorage.getItem(storageKey),
                    dataAfterReset: JSON.parse(JSON.stringify(store.data)),
                };
            }, STORAGE_KEY);

            expect(result.stored.value).toBe('foo');
            expect(result.value.value).toBe('foo');
            expect(result.afterRemove).toBeUndefined();
            expect(result.afterReset).toBeNull();
            expect(result.dataAfterReset).toEqual({});
        });

        test('should purge expired sections on init', async ({page}) => {
            const result = await page.evaluate((storageKey) => {
                const store = Alpine.store('LocalStorage');
                const now = store.getCurrentTimestamp();
                localStorage.setItem(storageKey, JSON.stringify({
                    loki_expired: {data_id: now - store.sectionLifetime - 100},
                    loki_without_data_id: {foo: 'bar'},
                    loki_fresh: {data_id: now},
                }));

                store.init();

                return {
                    data: Object.keys(store.data),
                    stored: Object.keys(JSON.parse(localStorage.getItem(storageKey) || '{}')),
                };
            }, STORAGE_KEY);

            for (const keys of [result.data, result.stored]) {
                expect(keys).toContain('loki_fresh');
                expect(keys).not.toContain('loki_expired');
                expect(keys).not.toContain('loki_without_data_id');
            }
        });

        test('should dispatch a localStorageUpdate event when setting an item', async ({page}) => {
            const detail = await page.evaluate(() => new Promise(resolve => {
                document.addEventListener('localStorageUpdate', (event: any) => resolve(event.detail), {once: true});
                localStorage.setItem('loki_test_key', 'loki_test_value');
            }));

            expect(detail).toEqual({key: 'loki_test_key', value: 'loki_test_value'});
        });

        test('should sync the store with updates of the storage key', async ({page}) => {
            const data = await page.evaluate((storageKey) => {
                localStorage.setItem(storageKey, JSON.stringify({loki_synced: {data_id: 1, foo: 'bar'}}));
                return JSON.parse(JSON.stringify(Alpine.store('LocalStorage').data));
            }, STORAGE_KEY);

            expect(data).toEqual({loki_synced: {data_id: 1, foo: 'bar'}});
        });

        test('should fetch an unknown section from the server', async ({page}) => {
            const requestPromise = page.waitForRequest(request => request.url().includes('customer/section/load'));
            await page.evaluate(() => Alpine.store('LocalStorage').get('loki_unknown_section').catch(() => null));
            const request = await requestPromise;

            expect(new URL(request.url()).searchParams.get('sections')).toBe('loki_unknown_section');
        });
    });
}
