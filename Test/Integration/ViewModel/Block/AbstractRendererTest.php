<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration\ViewModel\Block;

use Loki\Base\Test\Integration\Traits\LayoutLoader;
use Loki\Base\Util\Block\TransferableAncestorBlockProperties;
use Loki\Base\ViewModel\Block\AbstractRenderer;
use Loki\Base\ViewModel\Block\ChildCounter;
use Loki\Base\ViewModel\ModuleConfig;
use Loki\Base\ViewModel\SectionConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State as AppState;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class AbstractRendererTest extends TestCase
{
    use LayoutLoader;

    private const PARENT_NAME = 'loki.parent_block';

    public function testGetBlockAliasPrefersBlockAlias(): void
    {
        $layout = $this->createLayout();
        $renderer = $this->getRenderer();
        $renderer->setAncestorBlock($this->createParentBlock($layout));
        $block = $this->createTextBlock($layout, 'loki.some.block', ['alias' => 'own']);

        $this->assertSame('own', $renderer->callGetBlockAlias($block, ['alias' => 'data']));
    }

    public function testGetBlockAliasFallsBackToDataAlias(): void
    {
        $layout = $this->createLayout();
        $renderer = $this->getRenderer();
        $renderer->setAncestorBlock($this->createParentBlock($layout));
        $block = $this->createTextBlock($layout, 'loki.some.block');

        $this->assertSame('data', $renderer->callGetBlockAlias($block, ['alias' => 'data']));
        $this->assertSame('data', $renderer->callGetBlockAlias(null, ['alias' => 'data']));
    }

    public function testGetBlockAliasFallsBackToCounterOfAncestorBlock(): void
    {
        $layout = $this->createLayout();
        $renderer = $this->getRenderer();
        $renderer->setAncestorBlock($this->createParentBlock($layout));

        $this->assertSame('block0', $renderer->callGetBlockAlias());
        $this->assertSame('block1', $renderer->callGetBlockAlias());
        $this->assertSame('block2', $renderer->callGetBlockAlias($this->createTextBlock($layout, 'loki.some.block')));
    }

    public function testGetBlockAliasIsEmptyWithoutAncestorBlock(): void
    {
        $this->assertSame('', $this->getRenderer()->callGetBlockAlias());
    }

    public function testSetNameInLayoutKeepsExistingName(): void
    {
        $layout = $this->createLayout();
        $renderer = $this->getRenderer();
        $renderer->setAncestorBlock($this->createParentBlock($layout));
        $block = $this->createTextBlock($layout, 'loki.existing.name');

        $renderer->callSetNameInLayout($block);

        $this->assertSame('loki.existing.name', $block->getNameInLayout());
    }

    public function testSetNameInLayoutDoesNothingWithoutAncestorBlock(): void
    {
        $block = $this->createTextBlock($this->createLayout(), '');

        $this->getRenderer()->callSetNameInLayout($block);

        $this->assertEmpty($block->getNameInLayout());
    }

    public function testSetNameInLayoutUsesAncestorNameAndAlias(): void
    {
        $layout = $this->createLayout();
        $renderer = $this->getRenderer();
        $renderer->setAncestorBlock($this->createParentBlock($layout));

        $block = $this->createTextBlock($layout, '', ['alias' => 'foo']);
        $renderer->callSetNameInLayout($block);
        $this->assertSame(self::PARENT_NAME . '.foo', $block->getNameInLayout());

        $block = $this->createTextBlock($layout, '');
        $renderer->callSetNameInLayout($block);
        $this->assertSame(self::PARENT_NAME . '.block0', $block->getNameInLayout());
    }

    public function testGetAncestorViewModelPrefersOwnViewModel(): void
    {
        $layout = $this->createLayout();
        $ancestorViewModel = ObjectManager::getInstance()->get(ModuleConfig::class);
        $ownViewModel = ObjectManager::getInstance()->get(SectionConfig::class);

        $renderer = $this->getRenderer();
        $renderer->setAncestorBlock($this->createParentBlock($layout, ['view_model' => $ancestorViewModel]));
        $this->assertSame($ancestorViewModel, $renderer->getAncestorViewModel());

        $renderer->setAncestorViewModel($ownViewModel);
        $this->assertSame($ownViewModel, $renderer->getAncestorViewModel());
    }

    public function testGetAncestorViewModelIsNullWithoutViewModel(): void
    {
        $renderer = $this->getRenderer();
        $this->assertNull($renderer->getAncestorViewModel());

        $renderer->setAncestorBlock($this->createParentBlock($this->createLayout()));
        $this->assertNull($renderer->getAncestorViewModel());
    }

    public function testGetUniqIdSanitizesUniqAndAncestorName(): void
    {
        $layout = $this->createLayout();
        $renderer = $this->getRenderer();
        $block = $this->createTextBlock($layout, 'loki.some.block');

        $this->assertSame('block', $renderer->callGetUniqId($block));
        $this->assertSame('foo-bar-baz', $renderer->callGetUniqId($block, ['uniq' => 'foo bar_baz']));

        $renderer->setAncestorBlock($this->createParentBlock($layout));
        $this->assertSame('loki-parent-block-block', $renderer->callGetUniqId($block));
        $this->assertSame('loki-parent-block-foo-bar', $renderer->callGetUniqId($block, ['uniq' => 'foo/bar']));
    }

    #[DataProvider('getDeveloperModeData')]
    public function testIsDeveloperMode(string $mode, bool $expected): void
    {
        $this->assertSame($expected, $this->getRenderer($mode)->callIsDeveloperMode());
    }

    public static function getDeveloperModeData(): array
    {
        return [
            [AppState::MODE_DEVELOPER, true],
            [AppState::MODE_DEFAULT, false],
            [AppState::MODE_PRODUCTION, false],
        ];
    }

    private function createParentBlock(LayoutInterface $layout, array $data = []): Template
    {
        /** @var Template $parentBlock */
        $parentBlock = $layout->createBlock(Template::class, self::PARENT_NAME, ['data' => $data]);

        return $parentBlock;
    }

    private function createTextBlock(LayoutInterface $layout, string $name, array $data = []): Text
    {
        if ($name === '') {
            return ObjectManager::getInstance()->create(Text::class, ['data' => $data]);
        }

        /** @var Text $block */
        $block = $layout->createBlock(Text::class, $name, ['data' => $data]);

        return $block;
    }

    private function getRenderer(string $mode = AppState::MODE_PRODUCTION): AbstractRenderer
    {
        $appState = $this->createMock(AppState::class);
        $appState->method('getMode')->willReturn($mode);

        return new class(
            ObjectManager::getInstance()->get(LayoutInterface::class),
            $appState,
            new ChildCounter(),
            new TransferableAncestorBlockProperties()
        ) extends AbstractRenderer {
            public function setAncestorBlock(?AbstractBlock $ancestorBlock): void
            {
                $this->ancestorBlock = $ancestorBlock;
            }

            public function setAncestorViewModel(?ArgumentInterface $ancestorViewModel): void
            {
                $this->ancestorViewModel = $ancestorViewModel;
            }

            public function callGetBlockAlias(?AbstractBlock $block = null, array $data = []): string
            {
                return $this->getBlockAlias($block, $data);
            }

            public function callSetNameInLayout(AbstractBlock $block): void
            {
                $this->setNameInLayout($block);
            }

            public function callGetUniqId(AbstractBlock $block, array $data = []): string
            {
                return $this->getUniqId($block, $data);
            }

            public function callIsDeveloperMode(): bool
            {
                return $this->isDeveloperMode();
            }
        };
    }
}
