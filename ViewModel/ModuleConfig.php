<?php
declare(strict_types=1);

namespace Loki\Base\ViewModel;

use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ModuleConfig implements ArgumentInterface
{
    public function __construct(
        private readonly Manager $moduleManager
    ) {
    }

    public function isEnabled(string $moduleName): bool
    {
        return $this->moduleManager->isEnabled($moduleName);
    }
}
