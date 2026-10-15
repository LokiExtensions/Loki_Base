<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration\Template;

use Loki\Base\Config\Config;
use Loki\Base\Test\Integration\Traits\LayoutLoader;
use Loki\Base\ViewModel\CookieConfig;
use Loki\Base\ViewModel\FormKeyValue;
use Loki\Base\ViewModel\LocaleCode;
use Loki\Base\ViewModel\ModuleConfig;
use Loki\Base\ViewModel\SectionConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Session\Config as SessionConfig;
use Magento\Framework\View\Element\Template;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class TemplateRenderingTest extends TestCase
{
    use LayoutLoader;

    public function testVariablesTemplate(): void
    {
        $html = $this->renderTemplate('Loki_Base::script/variables.phtml', [
            'form_key_value' => $this->get(FormKeyValue::class),
            'locale_code' => $this->get(LocaleCode::class),
        ]);

        $formKey = $this->get(FormKey::class)->getFormKey();
        $this->assertNotEmpty($formKey);
        $this->assertStringContainsString("var LOKI_FORM_KEY = '" . $formKey . "';", $html);
        $this->assertStringContainsString("var LOKI_LOCALE = 'en-US';", $html);
        $this->assertStringContainsString('var BASE_URL = ', $html);
        $this->assertStringContainsString('var LOKI_THEME_URL = ', $html);
        $this->assertStringContainsString('< 767;', $html);
    }

    #[DataProvider('getMobileWidthData')]
    public function testVariablesTemplateMobileWidth(mixed $mobileWidth, int $expectedWidth): void
    {
        $html = $this->renderTemplate('Loki_Base::script/variables.phtml', [
            'form_key_value' => $this->get(FormKeyValue::class),
            'locale_code' => $this->get(LocaleCode::class),
            'mobile_width' => $mobileWidth,
        ]);

        $this->assertStringContainsString('< ' . $expectedWidth . ';', $html);
    }

    public static function getMobileWidthData(): array
    {
        return [
            [1024, 1024],
            ['1024', 1024],
            [50, 767],
            ['abc', 767],
            [null, 767],
        ];
    }

    #[ConfigFixture('loki_base/messages/timeout', 1234, 'store', 'default')]
    public function testMessageComponentTemplateUsesConfiguredTimeout(): void
    {
        $html = $this->renderTemplate('Loki_Base::script/component/message-component.phtml', [
            'config' => $this->get(Config::class),
            'module_config' => $this->get(ModuleConfig::class),
        ]);

        $this->assertStringContainsString("Alpine.data('LokiMessagesComponent'", $html);
        $this->assertStringContainsString('timeout: 1234,', $html);
    }

    #[ConfigFixture('loki_base/messages/timeout', 1234, 'store', 'default')]
    public function testMessageComponentTemplateUsesTimeoutArgument(): void
    {
        $html = $this->renderTemplate('Loki_Base::script/component/message-component.phtml', [
            'config' => $this->get(Config::class),
            'module_config' => $this->get(ModuleConfig::class),
            'timeout' => 99,
        ]);

        $this->assertStringContainsString('timeout: 99,', $html);
    }

    public function testCookiesTemplate(): void
    {
        $sessionConfig = $this->createMock(SessionConfig::class);
        $sessionConfig->method('getCookieLifetime')->willReturn(3600);
        $sessionConfig->method('getCookieDomain')->willReturn('example.com');
        $sessionConfig->method('getCookiePath')->willReturn('/');
        $sessionConfig->method('getCookieHttpOnly')->willReturn(true);
        $sessionConfig->method('getCookieSecure')->willReturn(false);

        $html = $this->renderTemplate('Loki_Base::script/cookies.phtml', [
            'cookie_config' => new CookieConfig($sessionConfig),
            'module_config' => $this->get(ModuleConfig::class),
        ]);

        $this->assertStringContainsString('const MageCookies', $html);
        $this->assertStringContainsString('lifetime: 3600,', $html);
        $this->assertStringContainsString("domain: '.example.com',", $html);
        $this->assertStringContainsString("path: '/',", $html);
        $this->assertStringContainsString('httpOnly: 1,', $html);
        $this->assertStringContainsString('secure: 0,', $html);
        $this->assertStringContainsString("sameSite: 'Lax',", $html);
    }

    public function testLocalStorageStoreTemplate(): void
    {
        $sectionConfig = $this->createMock(SectionConfig::class);
        $sectionConfig->method('getSectionDataLifeTime')->willReturn(60);

        $html = $this->renderTemplate('Loki_Base::script/store/localstorage-store.phtml', [
            'section_config' => $sectionConfig,
            'module_config' => $this->get(ModuleConfig::class),
        ]);

        $this->assertStringContainsString("Alpine.store('LocalStorage'", $html);
        $this->assertStringContainsString('sectionLifetime: 3600,', $html);
    }

    #[DataProvider('getMessageStoreSkipData')]
    public function testMessageStoreTemplateSkipFlags(
        bool $skipLocalStorage,
        bool $skipCookies,
        string $expectedFetchLocalStorage,
        string $expectedFetchCookies
    ): void {
        $html = $this->renderTemplate('Loki_Base::script/store/message-store.phtml', [
            'module_config' => $this->get(ModuleConfig::class),
            'skip_local_storage' => $skipLocalStorage,
            'skip_cookies' => $skipCookies,
        ]);

        $this->assertStringContainsString('fetchLocalStorage: ' . $expectedFetchLocalStorage . ',', $html);
        $this->assertStringContainsString('fetchCookies: ' . $expectedFetchCookies . ',', $html);
    }

    public static function getMessageStoreSkipData(): array
    {
        return [
            [false, false, 'true', 'true'],
            [true, true, 'false', 'false'],
            [false, true, 'true', 'false'],
            [true, false, 'false', 'true'],
        ];
    }

    public function testMessagesTemplate(): void
    {
        $html = $this->renderTemplate('Loki_Base::component/messages.phtml', [
            'module_config' => $this->get(ModuleConfig::class),
        ]);

        $this->assertStringContainsString('x-data="LokiMessagesComponent"', $html);
        $this->assertStringContainsString('x-autoremove="timeout"', $html);
        $this->assertStringContainsString('x-safe-html="message.text"', $html);
    }

    #[DataProvider('getAlpineTemplateData')]
    public function testAlpineCspTemplate(bool $defer): void
    {
        $html = $this->renderTemplate('Loki_Base::script/alpine-csp.phtml', [
            'module_config' => $this->get(ModuleConfig::class),
            'defer' => $defer,
        ]);

        $this->assertMatchesRegularExpression('#<script defer src="[^"]+Loki_Base/js/alpine-csp\.min\.js"#', $html);
        $this->assertSame($defer, str_contains($html, '" defer></script>'));
    }

    public static function getAlpineTemplateData(): array
    {
        return [[true], [false]];
    }

    public function testExceptionTemplate(): void
    {
        $html = $this->renderTemplate('Loki_Base::message/exception.phtml', [
            'message' => 'Something <b>bad</b> happened',
            'file' => '/var/www/Foo.php',
            'line' => 42,
            'trace' => ['#0 first()', '#1 <second>()'],
        ]);

        $this->assertStringContainsString('<summary>Something &lt;b&gt;bad&lt;/b&gt; happened</summary>', $html);
        $this->assertStringContainsString('[/var/www/Foo.php line 42]', $html);
        $this->assertStringContainsString('#0 first()', $html);
        $this->assertStringContainsString('#1 &lt;second&gt;()', $html);
    }

    #[DataProvider('getMageOsTemplateData')]
    public function testTemplatesAreSkippedWhenMageOsModuleIsEnabled(string $template, array $data): void
    {
        $moduleConfig = $this->createMock(ModuleConfig::class);
        $moduleConfig->method('isEnabled')->willReturn(true);

        $data['module_config'] = $moduleConfig;
        $this->assertSame('', trim($this->renderTemplate($template, $data)));
    }

    public static function getMageOsTemplateData(): array
    {
        return [
            ['Loki_Base::script/alpine-csp.phtml', []],
            ['Loki_Base::script/cookies.phtml', []],
            ['Loki_Base::script/store/localstorage-store.phtml', []],
            ['Loki_Base::script/store/message-store.phtml', []],
            ['Loki_Base::script/component/message-component.phtml', []],
            ['Loki_Base::component/messages.phtml', []],
        ];
    }

    private function renderTemplate(string $template, array $data): string
    {
        $block = $this->createLayout()->createBlock(Template::class, 'loki.test.' . uniqid(), [
            'data' => array_merge(['template' => $template], $data),
        ]);

        return (string)$block->toHtml();
    }

    private function get(string $className): object
    {
        return ObjectManager::getInstance()->get($className);
    }
}
