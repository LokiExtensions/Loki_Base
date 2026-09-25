import {setupCheckout} from '@loki/setup-checkout';
import {test, expect} from '@loki/test';
import coreConfig from '@loki/config';

declare const MageCookies: {get: (name: string) => unknown};

const COOKIEBOT_CONSENT_RAW = "{stamp:%27LFiG8dNaLUSwTIS2syari25dArIUWAd/Whq5tPT//r4Uuz/Pt7Lu5g==%27%2Cnecessary:true%2Cpreferences:false%2Cstatistics:false%2Cmarketing:false%2Cmethod:%27explicit%27%2Cver:1%2Cutc:1790061567638%2Cregion:%27nl%27}";

test.describe('MageCookies with a Cookiebot CookieConsent cookie', () => {
    test('should return the full CookieConsent value', async ({page, context, baseURL}) => {
        await context.addCookies([{
            name: 'CookieConsent',
            value: COOKIEBOT_CONSENT_RAW,
            url: baseURL,
        }]);

        await setupCheckout(page, context, coreConfig);

        const value = await page.evaluate(() => MageCookies.get('CookieConsent'));

        expect(value).toEqual(decodeURIComponent(COOKIEBOT_CONSENT_RAW));
        expect(value).toContain('marketing:false');
    });
});
