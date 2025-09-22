<?php declare(strict_types=1);

namespace Loki\Base\Block;

use Magento\Framework\View\Element\Template;

class LokiScript extends Template
{
    public function getCacheKeyInfo(): array
    {
        return [
            ...parent::getCacheKeyInfo(),
            $this->getNameInLayout(),
        ];
    }
}
