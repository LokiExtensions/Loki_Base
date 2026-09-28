<?php declare(strict_types=1);

namespace Loki\Base\Block;

use Loki\Base\ViewModel\Block\ChildRenderer;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class LokiScript extends Template
{
    public function __construct(
        Context $context,
        private readonly ChildRenderer $childRenderer,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getCacheKeyInfo(): array
    {
        return [
            ...parent::getCacheKeyInfo(),
            $this->getNameInLayout(),
        ];
    }

    protected function _beforeToHtml()
    {
        if (!isset($this->_viewVars['childRenderer'])) {
            $this->assign('childRenderer', $this->childRenderer);
        }

        return parent::_beforeToHtml();
    }
}
