<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration\Template;

use Loki\Base\Block\LokiScript;
use Loki\Base\Test\Integration\Traits\LayoutLoader;
use Loki\Base\ViewModel\ModuleConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class CssUtilsObserverTest extends TestCase
{
    use LayoutLoader;

    public function testObserverAssignsCssToContainerTemplate(): void
    {
        $block = $this->createLayout()->createBlock(LokiScript::class, 'loki.observer.container', [
            'data' => [
                'template' => 'Loki_Base::container.phtml',
                'css_classes' => 'my-observer-class',
            ],
        ]);

        $html = (string)$block->toHtml();

        $this->assertMatchesRegularExpression('#<div class="[^"]*\bloki-observer-container\b[^"]*">#', $html);
        $this->assertMatchesRegularExpression('#<div class="[^"]*\bscope-block\b[^"]*">#', $html);
        $this->assertMatchesRegularExpression('#<div class="[^"]*\bmy-observer-class\b[^"]*">#', $html);
    }

    public function testObserverAssignsCssToMessagesTemplate(): void
    {
        $block = $this->createLayout()->createBlock(Template::class, 'loki.observer.messages', [
            'data' => [
                'template' => 'Loki_Base::component/messages.phtml',
                'module_config' => ObjectManager::getInstance()->get(ModuleConfig::class),
            ],
        ]);

        $html = (string)$block->toHtml();

        $this->assertStringContainsString('loki-observer-messages__message ', $html);
        $this->assertStringContainsString('loki-observer-messages__message-text ', $html);
        $this->assertStringContainsString('loki-observer-messages__button ', $html);
        $this->assertStringContainsString('container mx-auto py-3', $html);
    }
}
