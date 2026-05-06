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
            $sortedChildAlias = $layout->getElementAlias($sortedChild->getNameInLayout());
            $sortedChild->setAncestorBlock($parentBlock);
            $sortedChildHtml = $sortedChild->toHtml();
            $sortedChildHtml = $this->appendBlockAliasToBlockHtml($sortedChildHtml, $sortedChildAlias);
            $html .= $sortedChildHtml."\n";
        }

        return $html;
    }

    private function appendBlockAliasToBlockHtml(string $blockHtml, string $blockAlias): string
    {
        if (trim($blockHtml) === '') {
            return $blockHtml;
        }

        return preg_replace_callback(
            '/<([a-zA-Z][a-zA-Z0-9:-]*)([^>]*)>/',
            static function (array $matches) use ($blockAlias): string {
                return sprintf(
                    '<%s%s data-child-name="%s">',
                    $matches[1],
                    $matches[2],
                    $blockAlias
                );
            },
            $blockHtml,
            1
        );
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
