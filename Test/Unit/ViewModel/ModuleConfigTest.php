<?php declare(strict_types=1);

namespace Loki\Base\Test\Unit\ViewModel;

use Loki\Base\ViewModel\ModuleConfig;
use Magento\Framework\Module\Manager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ModuleConfigTest extends TestCase
{
    #[DataProvider('getIsEnabledData')]
    public function testIsEnabled(string $moduleName, bool $enabled): void
    {
        $moduleManager = $this->createMock(Manager::class);
        $moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with($moduleName)
            ->willReturn($enabled);

        $this->assertSame($enabled, (new ModuleConfig($moduleManager))->isEnabled($moduleName));
    }

    public static function getIsEnabledData(): array
    {
        return [
            ['Magento_Store', true],
            ['MageOS_AlpineMessages', false],
        ];
    }
}
