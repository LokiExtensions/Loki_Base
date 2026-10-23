<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration\Template;

use Loki\Base\Block\LokiScript;
use Loki\Base\Test\Integration\Traits\LayoutLoader;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class ContainerTemplateTest extends TestCase
{
    use LayoutLoader;

    private const CONTAINER_NAME = 'loki.container.test';

    public function testContainerWrapsChildrenSortedBySortOrder(): void
    {
        $layout = $this->createLayout();
        $block = $this->createContainerBlock($layout);
        $this->addChild($layout, 'first', '<p>First</p>', 20);
        $this->addChild($layout, 'second', '<p>Second</p>', 10);

        $html = trim((string)$block->toHtml());

        $this->assertMatchesRegularExpression('#^<div class="[^"]*">.*</div>$#s', $html);
        $this->assertStringContainsString('<p>First</p>', $html);
        $this->assertStringContainsString('<p>Second</p>', $html);
        $this->assertLessThan(strpos($html, '<p>First</p>'), strpos($html, '<p>Second</p>'));
    }

    public function testContainerAddsCssClasses(): void
    {
        $layout = $this->createLayout();
        $block = $this->createContainerBlock($layout, ['css_classes' => 'my-container-class']);

        $html = (string)$block->toHtml();

        $this->assertMatchesRegularExpression('#<div class="[^"]*\bloki-container-test\b[^"]*">#', $html);
        $this->assertMatchesRegularExpression('#<div class="[^"]*\bmy-container-class\b[^"]*">#', $html);
    }

    private function createContainerBlock(LayoutInterface $layout, array $data = []): AbstractBlock
    {
        return $layout->createBlock(LokiScript::class, self::CONTAINER_NAME, [
            'data' => array_merge(['template' => 'Loki_Base::container.phtml'], $data),
        ]);
    }

    private function addChild(LayoutInterface $layout, string $alias, string $text, int $sortOrder = 0): void
    {
        $name = self::CONTAINER_NAME . '.' . $alias;
        $layout->createBlock(Text::class, $name, ['data' => ['text' => $text, 'sort_order' => $sortOrder]]);
        $layout->setChild(self::CONTAINER_NAME, $name, $alias);
    }
}
