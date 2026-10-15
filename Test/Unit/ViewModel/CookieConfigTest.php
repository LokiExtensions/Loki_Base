<?php declare(strict_types=1);

namespace Loki\Base\Test\Unit\ViewModel;

use Loki\Base\ViewModel\CookieConfig;
use Magento\Framework\Session\Config as SessionConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CookieConfigTest extends TestCase
{
    public function testGetLifetime(): void
    {
        $sessionConfig = $this->createMock(SessionConfig::class);
        $sessionConfig->method('getCookieLifetime')->willReturn('3600');

        $this->assertSame(3600, (new CookieConfig($sessionConfig))->getLifetime());
    }

    public function testGetPath(): void
    {
        $sessionConfig = $this->createMock(SessionConfig::class);
        $sessionConfig->method('getCookiePath')->willReturn('/');

        $this->assertSame('/', (new CookieConfig($sessionConfig))->getPath());
    }

    #[DataProvider('getDomainData')]
    public function testGetDomain(?string $cookieDomain, string $expectedDomain): void
    {
        $sessionConfig = $this->createMock(SessionConfig::class);
        $sessionConfig->method('getCookieDomain')->willReturn($cookieDomain);

        $this->assertSame($expectedDomain, (new CookieConfig($sessionConfig))->getDomain());
    }

    public static function getDomainData(): array
    {
        return [
            [null, ''],
            ['', ''],
            ['example.com', '.example.com'],
            ['.example.com', '.example.com'],
        ];
    }

    #[DataProvider('getHttpOnlyData')]
    public function testIsHttpOnly(mixed $httpOnly, int $expected): void
    {
        $sessionConfig = $this->createMock(SessionConfig::class);
        $sessionConfig->method('getCookieHttpOnly')->willReturn($httpOnly);

        $this->assertSame($expected, (new CookieConfig($sessionConfig))->isHttpOnly());
    }

    public static function getHttpOnlyData(): array
    {
        return [
            [true, 1],
            [false, 0],
            ['1', 1],
            [null, 0],
        ];
    }

    public function testGetSameSite(): void
    {
        $sessionConfig = $this->createMock(SessionConfig::class);
        $this->assertSame('Lax', (new CookieConfig($sessionConfig))->getSameSite());
    }
}
