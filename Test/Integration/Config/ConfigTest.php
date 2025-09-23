<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration\Config;

use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use PHPUnit\Framework\TestCase;
use Loki\Base\Config\Config;

class ConfigTest extends TestCase
{
    #[ConfigFixture('loki_base/messages/timeout', 0)]
    public function testMessagesTimeoutWithEmptyValue()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $this->assertSame(0, $config->getMessagesTimeout());
    }

    #[ConfigFixture('loki_base/messages/timeout', 42)]
    public function testMessagesTimeoutWithPositiveValue()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $this->assertSame(42, $config->getMessagesTimeout());
    }

    #[ConfigFixture('loki_base/messages/timeout', -42)]
    public function testMessagesTimeoutWithNegativeValue()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $this->assertSame(0, $config->getMessagesTimeout());
    }
}
