<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration\Block;

use Loki\Base\Block\LokiScript;
use Loki\Base\Test\Integration\Traits\LayoutLoader;
use Loki\Base\ViewModel\Block\ChildRenderer;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class LokiScriptTest extends TestCase
{
    use LayoutLoader;

    public function testGetCacheKeyInfoContainsNameInLayout(): void
    {
        $block = $this->createLokiScript($this->createLayout());
        $this->assertContains('loki.script.test', $block->getCacheKeyInfo());
    }

    public function testToHtmlRendersChildrenThroughChildRenderer(): void
    {
        $layout = $this->createLayout();
        $block = $this->createLokiScript($layout);
        $this->addChild($layout, 'first', '<script>first</script>', 20);
        $this->addChild($layout, 'second', '<script>second</script>', 10);

        $html = $block->toHtml();

        $this->assertStringContainsString('<script>first</script>', $html);
        $this->assertStringContainsString('<script>second</script>', $html);
        $this->assertLessThan(
            strpos($html, '<script>first</script>'),
            strpos($html, '<script>second</script>')
        );
    }

    public function testToHtmlDoesNotOverwriteAssignedChildRenderer(): void
    {
        $layout = $this->createLayout();
        $block = $this->createLokiScript($layout);
        $this->addChild($layout, 'first', '<script>first</script>');

        $childRenderer = $this->createMock(ChildRenderer::class);
        $childRenderer->method('all')->willReturn('custom');
        $block->assign('childRenderer', $childRenderer);

        $this->assertSame('custom', trim($block->toHtml(), " \f\n\r\t\v\x00"));
    }

    private function createLokiScript(LayoutInterface $layout): LokiScript
    {
        /** @var LokiScript $block */
        $block = $layout->createBlock(LokiScript::class, 'loki.script.test', [
            'data' => ['template' => 'Loki_Base::script/script-container.phtml'],
        ]);

        return $block;
    }

    private function addChild(LayoutInterface $layout, string $alias, string $text, int $sortOrder = 0): void
    {
        $name = 'loki.script.test.' . $alias;
        $layout->createBlock(Text::class, $name, ['data' => ['text' => $text, 'sort_order' => $sortOrder]]);
        $layout->setChild('loki.script.test', $name, $alias);
    }
}
