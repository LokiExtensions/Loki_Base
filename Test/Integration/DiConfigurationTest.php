<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration;

use Loki\Base\Util\Block\TransferableAncestorBlockProperties;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Message\MessageConfigurationsPool;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;

#[AppArea('frontend')]
class DiConfigurationTest extends TestCase
{
    public function testLokiComponentExceptionMessageConfiguration(): void
    {
        $pool = ObjectManager::getInstance()->create(MessageConfigurationsPool::class);
        $configuration = $pool->getMessageConfiguration('lokiComponentException');

        $this->assertIsArray($configuration);
        $this->assertSame(BlockRenderer::CODE, $configuration['renderer']);
        $this->assertSame('Loki_Base::message/exception.phtml', $configuration['data']['template']);
    }

    public function testTransferableAncestorBlockPropertiesContainCssClasses(): void
    {
        $properties = ObjectManager::getInstance()->create(TransferableAncestorBlockProperties::class);
        $this->assertContains('css_classes', $properties->getProperties());
    }
}
