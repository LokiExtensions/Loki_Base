<?php declare(strict_types=1);

namespace Loki\Base\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;

class LocaleCode implements ArgumentInterface
{
    public function __construct(
        private LocaleResolver $localeResolver,
    ){
    }

    public function getCode(): string
    {
        return str_replace('_', '-', $this->localeResolver->getLocale());
    }
}
