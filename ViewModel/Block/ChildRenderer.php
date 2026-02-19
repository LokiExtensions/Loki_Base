<?php declare(strict_types=1);

namespace Loki\Base\ViewModel\Block;

use InvalidArgumentException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Text;
use RuntimeException;

class ChildRenderer extends AbstractRenderer
{
    public function all(
        AbstractBlock $parentBlock,
        string $blockAliasPrefix = '',
    ): string {
        $html = '';
        $childNames = $parentBlock->getChildNames();
        $children = [];

        $layout = $parentBlock->getLayout();
        foreach ($childNames as $childName) {
            if ($blockAliasPrefix && 0 !== strpos($childName, $blockAliasPrefix)) {
                continue;
            }

            $childBlock = $layout->getBlock($childName);
            if ($childBlock instanceof AbstractBlock) {
                $children[] = $childBlock;
                continue;
            }

            $childHtml = $parentBlock->getChildHtml($childName);
            if (!empty($childHtml)) {
                $block = $layout->createBlock(Text::class);
                $block->setText($childHtml);
                $children[] = $block;
                continue;
            }

            if ($this->isDeveloperMode()) {
                $html .= '<!-- WARNING: No child found "' . $childName . '" -->';
            }
        }

        $sortedChildren = $this->sortBlocks($children);

        foreach ($sortedChildren as $sortedChild) {
            $sortedChild->setAncestorBlock($parentBlock);
            $html .= $sortedChild->toHtml();
        }

        return $html;
    }

    public function get(
        AbstractBlock $ancestorBlock,
        string $blockAlias,
        array $data = [],
    ): AbstractBlock {
        $this->ancestorBlock = $ancestorBlock;
        $block = $this->ancestorBlock->getChildBlock($blockAlias);

        if (false === $block instanceof AbstractBlock) {
            throw new RuntimeException(
                (string)__(
                    'No child alias "%1" for parent "%2"',
                    $blockAlias,
                    $this->ancestorBlock->getNameInLayout()
                )
            );
        }

        $block->setAlias($blockAlias);
        $this->setNameInLayout($block);
        $this->populateBlock($block, $data);

        return $block;
    }

    public function html(
        AbstractBlock $ancestorBlock,
        string $blockAlias,
        array $data = []
    ) {
        try {
            return (string)$this->get($ancestorBlock, $blockAlias, $data)
                ->toHtml();
        } catch (RuntimeException|InvalidArgumentException $e) {
            if ($this->isDeveloperMode()) {
                return '<!-- WARNING: ' . $e->getMessage() . ' -->';
            }

            return '';
        }
    }

    private function sortBlocks(array $blocks): array
    {
        usort($blocks, function (AbstractBlock $blockA, AbstractBlock $blockB) {
            return (int)$blockA->getSortOrder() <=> (int)$blockB->getSortOrder();
        });

        return $blocks;
    }
}
