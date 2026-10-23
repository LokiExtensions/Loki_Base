<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration\Layout;

use Loki\Base\Block\LokiScript;
use Loki\Base\Config\Config;
use Loki\Base\Test\Integration\Traits\LayoutLoader;
use Loki\Base\ViewModel\CookieConfig;
use Loki\Base\ViewModel\FormKeyValue;
use Loki\Base\ViewModel\LocaleCode;
use Loki\Base\ViewModel\ModuleConfig;
use Loki\Base\ViewModel\SectionConfig;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class LokiBaseHandleTest extends TestCase
{
    use LayoutLoader;

    #[AppIsolation(true)]
    #[DataProvider('getBlockData')]
    public function testBlockExists(string $blockName, ?string $parentName): void
    {
        $layout = $this->loadLayout(['default', 'loki_base']);

        $this->assertNotFalse($layout->getBlock($blockName), 'Block "' . $blockName . '" not found');

        if ($parentName) {
            $this->assertSame($parentName, $layout->getParentName($blockName));
        }
    }

    public static function getBlockData(): array
    {
        return [
            ['loki.dynamic-script.variables', 'loki.dynamic-script'],
            ['loki.script', 'before.body.end'],
            ['loki.script.cookies', 'loki.script'],
            ['loki.alpinejs', 'loki.script'],
            ['loki.script.store', 'before.body.end'],
            ['loki.script.store.local-storage', 'loki.script.store'],
            ['loki.script.store.messages', 'loki.script.store'],
            ['loki.script.directive', 'before.body.end'],
            ['loki.script.directive.autoremove', 'loki.script.directive'],
            ['loki.script.directive.safehtml', 'loki.script.directive'],
            ['loki.script.component-partial', 'before.body.end'],
            ['loki.script.component-type', 'before.body.end'],
            ['loki.script.component', 'before.body.end'],
            ['loki.component.messages', 'loki.script.component'],
            ['loki.script.post', 'before.body.end'],
            ['loki.messages', 'page.messages'],
        ];
    }

    #[AppIsolation(true)]
    public function testScriptContainersAreLokiScriptBlocks(): void
    {
        $layout = $this->loadLayout(['default', 'loki_base']);

        $scriptContainers = [
            'loki.script',
            'loki.script.store',
            'loki.script.directive',
            'loki.script.component-partial',
            'loki.script.component-type',
            'loki.script.component',
            'loki.script.post',
        ];

        foreach ($scriptContainers as $scriptContainer) {
            $this->assertInstanceOf(LokiScript::class, $layout->getBlock($scriptContainer), $scriptContainer);
        }
    }

    #[AppIsolation(true)]
    public function testBlockArguments(): void
    {
        $layout = $this->loadLayout(['default', 'loki_base']);

        $variablesBlock = $this->getBlock($layout, 'loki.dynamic-script.variables');
        $this->assertInstanceOf(FormKeyValue::class, $variablesBlock->getFormKeyValue());
        $this->assertInstanceOf(LocaleCode::class, $variablesBlock->getLocaleCode());

        $cookiesBlock = $this->getBlock($layout, 'loki.script.cookies');
        $this->assertInstanceOf(CookieConfig::class, $cookiesBlock->getCookieConfig());
        $this->assertInstanceOf(ModuleConfig::class, $cookiesBlock->getModuleConfig());

        $localStorageBlock = $this->getBlock($layout, 'loki.script.store.local-storage');
        $this->assertInstanceOf(SectionConfig::class, $localStorageBlock->getSectionConfig());

        $messageComponentBlock = $this->getBlock($layout, 'loki.component.messages');
        $this->assertInstanceOf(Config::class, $messageComponentBlock->getConfig());
    }

    #[AppIsolation(true)]
    public function testMessageStoreDoesNotSkipAnythingWithoutHyvaHandle(): void
    {
        $layout = $this->loadLayout(['default', 'loki_base']);
        $block = $this->getBlock($layout, 'loki.script.store.messages');

        $this->assertEmpty($block->getSkipLocalStorage());
        $this->assertEmpty($block->getSkipCookies());
    }

    #[AppIsolation(true)]
    public function testHyvaHandleSkipsLocalStorageAndCookiesInMessageStore(): void
    {
        $layout = $this->loadLayout(['default', 'loki_base', 'hyva_loki_base']);
        $block = $this->getBlock($layout, 'loki.script.store.messages');

        $this->assertTrue($block->getSkipLocalStorage());
        $this->assertTrue($block->getSkipCookies());
    }

    #[AppIsolation(true)]
    public function testLokiAlpineJsIsAddedWithoutHyvaHandle(): void
    {
        $layout = $this->loadLayout(['default', 'loki_base']);
        $this->assertNotFalse($layout->getBlock('loki.alpinejs'));
    }

    #[AppIsolation(true)]
    public function testHyvaHandleRemovesLokiAlpineJs(): void
    {
        $layout = $this->loadLayout(['default', 'loki_base', 'hyva_loki_base']);
        $this->assertFalse($layout->getBlock('loki.alpinejs'));
    }

    #[AppIsolation(true)]
    public function testPageRendersLokiScripts(): void
    {
        $layout = $this->loadLayout(['default', 'loki_base']);
        $html = $layout->renderElement('before.body.end');

        $this->assertStringContainsString('var LOKI_FORM_KEY', $html);
        $this->assertStringContainsString('const MageCookies', $html);
        $this->assertStringContainsString('Loki_Base/js/alpine-csp.min.js', $html);
        $this->assertStringContainsString("Alpine.store('LocalStorage'", $html);
        $this->assertStringContainsString("Alpine.store('Message'", $html);
        $this->assertStringContainsString("Alpine.directive('autoremove'", $html);
        $this->assertStringContainsString("Alpine.directive('safe-html'", $html);
        $this->assertStringContainsString("Alpine.data('LokiMessagesComponent'", $html);
    }

    #[AppIsolation(true)]
    public function testScriptRenderOrder(): void
    {
        $layout = $this->loadLayout(['default', 'loki_base']);
        $html = $layout->renderElement('before.body.end');

        $orderedSnippets = [
            'const MageCookies',
            "Alpine.store('LocalStorage'",
            "Alpine.store('Message'",
            "Alpine.directive('autoremove'",
            "Alpine.data('LokiMessagesComponent'",
        ];

        $previousPosition = -1;
        foreach ($orderedSnippets as $snippet) {
            $position = strpos($html, $snippet);
            $this->assertNotFalse($position, 'Snippet "' . $snippet . '" not found');
            $this->assertGreaterThan($previousPosition, $position, 'Snippet "' . $snippet . '" is rendered too early');
            $previousPosition = $position;
        }
    }

    #[AppIsolation(true)]
    public function testMessagesBlockIsFirstInPageMessages(): void
    {
        $layout = $this->loadLayout(['default', 'loki_base']);
        $childNames = $layout->getChildNames('page.messages');

        $this->assertNotEmpty($childNames);
        $this->assertSame('loki.messages', reset($childNames));
    }

    private function getBlock(LayoutInterface $layout, string $name): AbstractBlock
    {
        $block = $layout->getBlock($name);
        $this->assertInstanceOf(AbstractBlock::class, $block, 'Block "' . $name . '" not found');

        return $block;
    }
}
