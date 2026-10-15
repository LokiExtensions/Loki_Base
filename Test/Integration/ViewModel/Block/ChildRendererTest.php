<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration\ViewModel\Block;

use Loki\Base\Test\Integration\Traits\LayoutLoader;
use Loki\Base\Util\Block\TransferableAncestorBlockProperties;
use Loki\Base\ViewModel\Block\ChildCounter;
use Loki\Base\ViewModel\Block\ChildRenderer;
use Loki\Base\ViewModel\ModuleConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State as AppState;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[AppArea('frontend')]
class ChildRendererTest extends TestCase
{
    use LayoutLoader;

    private const PARENT_NAME = 'loki.parent_block';

    public function testAllRendersChildrenSortedBySortOrder(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);
        $this->addTextChild($layout, 'first', 'First', 30);
        $this->addTextChild($layout, 'second', 'Second', 10);
        $this->addTextChild($layout, 'third', 'Third', 20);

        $html = $this->getChildRenderer()->all($parentBlock);

        $this->assertSame("Second\nThird\nFirst\n", $html);
    }

    public function testAllSetsAncestorBlockOnChildren(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);
        $childBlock = $this->addTextChild($layout, 'first', 'First');

        $this->getChildRenderer()->all($parentBlock);

        $this->assertSame($parentBlock, $childBlock->getAncestorBlock());
    }

    public function testAllFiltersByBlockAliasPrefix(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);
        $this->addTextChild($layout, 'first', 'First');
        $this->addTextChild($layout, 'second', 'Second');

        $html = $this->getChildRenderer()->all($parentBlock, self::PARENT_NAME . '.sec');

        $this->assertSame("Second\n", $html);
    }

    public function testAllRendersContainerChildren(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);
        $containerName = self::PARENT_NAME . '.container';
        $layout->addContainer($containerName, 'Container', [], self::PARENT_NAME);
        $layout->createBlock(Text::class, $containerName . '.text', ['data' => ['text' => 'Contained']]);
        $layout->setChild($containerName, $containerName . '.text', 'text');

        $html = $this->getChildRenderer()->all($parentBlock);

        $this->assertStringContainsString('Contained', $html);
    }

    public function testAllWarnsAboutEmptyChildInDeveloperMode(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);
        $containerName = self::PARENT_NAME . '.empty';
        $layout->addContainer($containerName, 'Empty', [], self::PARENT_NAME);

        $html = $this->getChildRenderer(AppState::MODE_DEVELOPER)->all($parentBlock);
        $this->assertSame('<!-- WARNING: No child found "' . $containerName . '" -->', $html);

        $html = $this->getChildRenderer(AppState::MODE_PRODUCTION)->all($parentBlock);
        $this->assertSame('', $html);
    }

    public function testGetReturnsPopulatedChildBlock(): void
    {
        $layout = $this->createLayout();
        $viewModel = ObjectManager::getInstance()->get(ModuleConfig::class);
        $parentBlock = $this->createParentBlock($layout, ['view_model' => $viewModel]);
        $this->addTextChild($layout, 'foo', 'Foo');

        $block = $this->getChildRenderer()->get($parentBlock, 'foo', ['bar' => 'baz']);

        $this->assertInstanceOf(Text::class, $block);
        $this->assertSame('foo', $block->getAlias());
        $this->assertSame(self::PARENT_NAME . '.foo', $block->getNameInLayout());
        $this->assertSame('baz', $block->getBar());
        $this->assertSame($parentBlock, $block->getAncestorBlock());
        $this->assertSame($viewModel, $block->getAncestorViewModel());
        $this->assertSame('loki-parent-block-foo', $block->getUniqId());
    }

    public function testGetUsesUniqDataForUniqId(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);
        $this->addTextChild($layout, 'foo', 'Foo');

        $block = $this->getChildRenderer()->get($parentBlock, 'foo', ['uniq' => 'my uniq.id']);

        $this->assertSame('loki-parent-block-my-uniq-id', $block->getUniqId());
    }

    public function testGetThrowsExceptionForUnknownAlias(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No child alias "unknown" for parent "' . self::PARENT_NAME . '"');
        $this->getChildRenderer()->get($parentBlock, 'unknown');
    }

    public function testHtmlRendersChildBlock(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);
        $this->addTextChild($layout, 'foo', 'Foo');

        $this->assertSame('Foo', $this->getChildRenderer()->html($parentBlock, 'foo'));
    }

    public function testHtmlFallsBackToEmptyStringForUnknownAlias(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);

        $this->assertSame('', $this->getChildRenderer()->html($parentBlock, 'unknown'));
    }

    public function testAnyRendersGroupChildren(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout);
        $this->addTextChild($layout, 'first', 'First', 0, false);
        $this->addTextChild($layout, 'second', 'Second', 0, false);
        $this->addTextChild($layout, 'third', 'Third', 0, false);
        $layout->addToParentGroup(self::PARENT_NAME . '.first', 'example');
        $layout->addToParentGroup(self::PARENT_NAME . '.third', 'example');

        $html = $this->getChildRenderer()->any($parentBlock, 'example');

        $this->assertSame('FirstThird', $html);
    }

    public function testPopulateBlockTransfersAncestorProperties(): void
    {
        $layout = $this->createLayout();
        $parentBlock = $this->createParentBlock($layout, ['css_classes' => ['foo'], 'other' => 'bar']);
        $childBlock = $this->addTextChild($layout, 'foo', 'Foo');
        $emptyParentBlock = $layout->createBlock(Template::class, 'loki.empty_parent', ['data' => ['css_classes' => []]]);
        $otherChildBlock = $layout->createBlock(Text::class, 'loki.empty_parent.foo', ['data' => ['css_classes' => ['own']]]);

        $childRenderer = $this->getChildRenderer();
        $childRenderer->get($parentBlock, 'foo');
        $childRenderer->populateBlock($childBlock, [], $parentBlock);
        $childRenderer->populateBlock($otherChildBlock, [], $emptyParentBlock);

        $this->assertSame(['foo'], $childBlock->getCssClasses());
        $this->assertNull($childBlock->getOther());
        $this->assertSame(['own'], $otherChildBlock->getCssClasses());
    }

    public function testPopulateBlockWithoutAncestorBlock(): void
    {
        $layout = $this->createLayout();
        $block = $layout->createBlock(Text::class, 'loki.some.block');

        $this->getChildRenderer()->populateBlock($block, ['foo' => 'bar']);

        $this->assertSame('bar', $block->getFoo());
        $this->assertNull($block->getAncestorBlock());
        $this->assertSame('block', $block->getUniqId());
    }

    private function createParentBlock(LayoutInterface $layout, array $data = []): Template
    {
        /** @var Template $parentBlock */
        $parentBlock = $layout->createBlock(Template::class, self::PARENT_NAME, ['data' => $data]);

        return $parentBlock;
    }

    private function addTextChild(
        LayoutInterface $layout,
        string $alias,
        string $text,
        int $sortOrder = 0,
        bool $useAlias = true
    ): Text {
        $name = self::PARENT_NAME . '.' . $alias;

        /** @var Text $block */
        $block = $layout->createBlock(Text::class, $name, ['data' => ['text' => $text, 'sort_order' => $sortOrder]]);
        $layout->setChild(self::PARENT_NAME, $name, $useAlias ? $alias : '');

        return $block;
    }

    private function getChildRenderer(string $mode = AppState::MODE_PRODUCTION): ChildRenderer
    {
        $appState = $this->createMock(AppState::class);
        $appState->method('getMode')->willReturn($mode);

        return ObjectManager::getInstance()->create(ChildRenderer::class, [
            'appState' => $appState,
            'childCounter' => new ChildCounter(),
            'transferableAncestorBlock' => new TransferableAncestorBlockProperties(['css_classes' => 'css_classes']),
        ]);
    }
}
