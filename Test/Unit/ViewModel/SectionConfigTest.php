<?php declare(strict_types=1);

namespace Loki\Base\Test\Unit\ViewModel;

use Loki\Base\ViewModel\SectionConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SectionConfigTest extends TestCase
{
    #[DataProvider('getSectionDataLifeTimeData')]
    public function testGetSectionDataLifeTime(mixed $value, int $expected): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('customer/online_customers/section_data_lifetime')
            ->willReturn($value);

        $this->assertSame($expected, (new SectionConfig($scopeConfig))->getSectionDataLifeTime());
    }

    public static function getSectionDataLifeTimeData(): array
    {
        return [
            ['60', 60],
            [120, 120],
            [null, 0],
        ];
    }
}
