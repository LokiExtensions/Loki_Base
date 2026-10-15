<?php declare(strict_types=1);

namespace Loki\Base\Test\Unit\Config;

use Loki\Base\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    #[DataProvider('getMessagesTimeoutData')]
    public function testGetMessagesTimeout(mixed $value, int $expected): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('loki_base/messages/timeout', ScopeInterface::SCOPE_STORE)
            ->willReturn($value);

        $this->assertSame($expected, (new Config($scopeConfig))->getMessagesTimeout());
    }

    public static function getMessagesTimeoutData(): array
    {
        return [
            [null, 0],
            ['0', 0],
            ['5000', 5000],
            [42, 42],
            ['-42', 0],
        ];
    }
}
