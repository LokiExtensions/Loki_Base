import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {Page} from '@playwright/test';
import {configure, isHyva, THEMES, waitForAlpineStores} from './helpers/configure';

declare const Alpine: any;
declare const MageCookies: {get: (name: string) => unknown};

const TIMEOUT_PATH = 'loki_base/messages/timeout';

const messageLocator = (page: Page, type: string, text: string) =>
    page.locator(`[x-data="LokiMessagesComponent"] div[data-message-type="${type}"][data-message-text="${text}"]`);

async function openPage(page: Page, theme: string, timeout: number) {
    await configure(page, {theme, config: {[TIMEOUT_PATH]: timeout}});
    await openTestPage(page, ['loki_base']);
    await waitForAlpineStores(page);
}

async function addMessage(page: Page, method: string, text: string) {
    await page.evaluate(([method, text]) => Alpine.store('Message')[method](text), [method, text]);
}

async function waitForLumaCustomerData(page: Page) {
    // Luma its customer-data.js wipes the localStorage on the very first visit
    await page.waitForFunction(() => document.cookie.includes('mage-cache-sessid') && localStorage.getItem('mage-cache-timeout'));
}

async function setMessagesCookie(page: Page, value: string) {
    await page.evaluate((value) => {
        document.cookie = 'mage-messages=' + encodeURIComponent(value) + '; path=/';
    }, value);
}

for (const theme of THEMES) {
    test.describe(`Loki messages (${theme})`, () => {
        test.afterAll(async ({browser}) => {
            const page = await browser.newPage({baseURL: process.env.TEST_URL, ignoreHTTPSErrors: true});
            await configure(page, {config: {[TIMEOUT_PATH]: 5000}});
            await page.close();
        });

        test.describe('without automatic removal', () => {
            test.beforeEach(async ({page}) => {
                await openPage(page, theme, 0);
            });

            for (const [method, type] of [
                ['addSuccessMessage', 'success'],
                ['addErrorMessage', 'error'],
                ['addWarningMessage', 'warning'],
                ['addNoticeMessage', 'notice'],
            ]) {
                test(`should show a ${type} message`, async ({page}) => {
                    await addMessage(page, method, `Loki ${type} message`);

                    const message = messageLocator(page, type, `Loki ${type} message`);
                    await expect(message).toBeVisible();
                    await expect(message).toContainText(`Loki ${type} message`);
                    await expect(message).toHaveClass(new RegExp(`message-${type}`));
                });
            }

            test('should remove a message when clicking the close button', async ({page}) => {
                await addMessage(page, 'addSuccessMessage', 'Close me');
                const message = messageLocator(page, 'success', 'Close me');
                await expect(message).toBeVisible();

                await message.locator('button').click();
                await expect(message).toHaveCount(0);
            });

            test('should remove a message when double-clicking it', async ({page}) => {
                await addMessage(page, 'addSuccessMessage', 'Double-click me');
                const message = messageLocator(page, 'success', 'Double-click me');
                await expect(message).toBeVisible();

                await message.dblclick();
                await expect(message).toHaveCount(0);
            });

            test('should remove a message when pressing Escape', async ({page}) => {
                await addMessage(page, 'addSuccessMessage', 'Escape me');
                const message = messageLocator(page, 'success', 'Escape me');
                await expect(message).toBeVisible();

                await page.keyboard.press('Escape');
                await expect(message).toHaveCount(0);
            });

            test('should keep a message when the timeout is 0', async ({page}) => {
                await addMessage(page, 'addSuccessMessage', 'Keep me');
                const message = messageLocator(page, 'success', 'Keep me');
                await expect(message).toBeVisible();

                await page.waitForTimeout(2000);
                await expect(message).toBeVisible();
            });

            test('should handle the mage-messages cookie', async ({page}) => {
                await setMessagesCookie(page, JSON.stringify([{type: 'success', text: 'From cookie'}]));

                const messages = await page.evaluate(() => JSON.parse(JSON.stringify(Alpine.store('Message').getMessages())));
                const cookie = await page.evaluate(() => MageCookies.get('mage-messages'));

                if (isHyva(theme)) {
                    expect(messages).toEqual([]);
                    expect(cookie).toEqual([{type: 'success', text: 'From cookie'}]);
                    return;
                }

                expect(messages).toContainEqual({type: 'success', text: 'From cookie'});
                expect(cookie).toBeUndefined();
                await expect(messageLocator(page, 'success', 'From cookie')).toBeVisible();
            });

            test('should show a message stored in the localStorage messages section', async ({page, context}) => {
                test.skip(isHyva(theme), 'Hyvä skips loading messages from localStorage');

                await waitForLumaCustomerData(page);
                await context.clearCookies({name: 'section_data_ids'});
                await page.evaluate(() => {
                    localStorage.setItem('mage-cache-storage', JSON.stringify({
                        messages: {
                            data_id: Math.floor(Date.now() / 1000),
                            messages: [{type: 'success', text: 'From localStorage'}],
                        },
                    }));
                });

                await page.reload();
                await waitForAlpineStores(page);

                await expect(messageLocator(page, 'success', 'From localStorage')).toBeVisible();
            });

            test('should show a message saved with saveMessage() on the next page', async ({page, context}) => {
                test.skip(isHyva(theme), 'Hyvä skips loading messages from localStorage');

                await waitForLumaCustomerData(page);
                await context.clearCookies({name: 'section_data_ids'});
                await page.evaluate(() => Alpine.store('Message').saveMessage('notice', 'Saved for later'));

                await page.reload();
                await waitForAlpineStores(page);

                await expect(messageLocator(page, 'notice', 'Saved for later')).toBeVisible();
            });

            for (const invalidValue of ['{"foo":"bar"}', 'not an array']) {
                test(`should ignore an invalid mage-messages cookie: ${invalidValue}`, async ({page}) => {
                    await setMessagesCookie(page, invalidValue);

                    const messages = await page.evaluate(() => JSON.parse(JSON.stringify(Alpine.store('Message').getMessages())));
                    expect(messages).toEqual([]);

                    if (!isHyva(theme)) {
                        expect(await page.evaluate(() => MageCookies.get('mage-messages'))).toBeUndefined();
                    }
                });
            }
        });

        test.describe('with automatic removal', () => {
            test.beforeEach(async ({page}) => {
                await openPage(page, theme, 1000);
            });

            test('should remove a message after the configured timeout', async ({page}) => {
                await addMessage(page, 'addSuccessMessage', 'Remove me automatically');
                const message = messageLocator(page, 'success', 'Remove me automatically');
                await expect(message).toBeVisible();
                await expect(message).toHaveCount(0, {timeout: 5000});
            });
        });
    });
}
