<?php declare(strict_types=1);

namespace Loki\Base\Test\Unit\ViewModel;

use Loki\Base\ViewModel\LocaleCode;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LocaleCodeTest extends TestCase
{
    #[DataProvider('getCodeData')]
    public function testGetCode(string $locale, string $expectedCode): void
    {
        $localeResolver = $this->createMock(LocaleResolver::class);
        $localeResolver->method('getLocale')->willReturn($locale);

        $this->assertSame($expectedCode, (new LocaleCode($localeResolver))->getCode());
    }

    public static function getCodeData(): array
    {
        return [
            ['en_US', 'en-US'],
            ['nl_NL', 'nl-NL'],
            ['zh_Hans_CN', 'zh-Hans-CN'],
            ['en', 'en'],
        ];
    }
}
