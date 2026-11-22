<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration\Traits;

use Loki\CssUtils\Util\CssClassFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

trait LayoutLoader
{
    private function loadLayout(array $handles, string $theme = 'Magento/luma'): LayoutInterface
    {
        $objectManager = ObjectManager::getInstance();
        $objectManager->get(DesignInterface::class)->setDesignTheme($theme, 'frontend');

        /** @var Page $page */
        $page = $objectManager->get(PageFactory::class)->create(true);
        $page->getConfig()->setPageLayout('1column');
        $page->addHandle($handles);

        $layout = $page->getLayout();
        $layout->getBlock('root');

        return $layout;
    }

    private function createLayout(): LayoutInterface
    {
        return ObjectManager::getInstance()->create(LayoutInterface::class);
    }

    private function assignCssUtils(Template $block): void
    {
        $css = ObjectManager::getInstance()->get(CssClassFactory::class)->create();
        $block->assign('css', $css->setBlock($block));
    }
}
