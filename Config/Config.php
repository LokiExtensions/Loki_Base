<?php declare(strict_types=1);

namespace Loki\Base\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ArgumentInterface
{
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    public function getMessagesTimeout(): int
    {
        $timeout = (int)$this->scopeConfig->getValue(
            'loki_base/messages/timeout',
            ScopeInterface::SCOPE_STORE
        );

        if ($timeout < 0) {
            return 0;
        }

        return $timeout;
    }
}
