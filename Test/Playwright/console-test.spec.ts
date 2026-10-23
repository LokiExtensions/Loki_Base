import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {configure, THEMES, waitForAlpineStores} from './helpers/configure';

for (const theme of THEMES) {
    test.describe(`Loki browser console (${theme})`, () => {
        test('should not produce JavaScript errors or CSP violations', async ({page}) => {
            const errors: string[] = [];
            page.on('pageerror', error => errors.push('pageerror: ' + error.message));
            page.on('console', message => {
                if (message.type() === 'error') {
                    errors.push('console: ' + message.text());
                }
            });

            await page.addInitScript(() => {
                (window as any).lokiCspViolations = [];
                document.addEventListener('securitypolicyviolation', (event) => {
                    (window as any).lokiCspViolations.push(event.violatedDirective + ' ' + event.blockedURI);
                });
            });

            await configure(page, {theme});
            await openTestPage(page, ['loki_base']);
            await waitForAlpineStores(page);

            const cspViolations = await page.evaluate(() => (window as any).lokiCspViolations);
            expect(cspViolations, 'CSP violations').toEqual([]);
            expect(errors, 'JavaScript errors').toEqual([]);
        });
    });
}
