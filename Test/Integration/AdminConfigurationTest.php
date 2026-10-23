<?php declare(strict_types=1);

namespace Loki\Base\Test\Integration;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Data as StructureData;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Config\Model\Config\Structure\Element\Section;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\TestFramework\Fixture\AppArea;
use PHPUnit\Framework\TestCase;

#[AppArea('adminhtml')]
class AdminConfigurationTest extends TestCase
{
    public function testLokiTabExists(): void
    {
        $structureData = ObjectManager::getInstance()->get(StructureData::class)->get();

        $this->assertArrayHasKey('loki', $structureData['tabs'] ?? []);
        $this->assertSame('Loki', (string)$structureData['tabs']['loki']['label']);
    }

    public function testLokiBaseSection(): void
    {
        $section = $this->getStructure()->getElement('loki_base');

        $this->assertInstanceOf(Section::class, $section);
        $this->assertSame('loki', $section->getAttribute('tab'));
        $this->assertSame('Loki_Base::config', $section->getAttribute('resource'));
    }

    public function testMessagesTimeoutField(): void
    {
        $field = $this->getStructure()->getElement('loki_base/messages/timeout');

        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('loki_base/messages/timeout', $field->getPath());
        $this->assertSame('text', $field->getType());
        $this->assertEquals(1, $field->getAttribute('showInDefault'));
        $this->assertEquals(1, $field->getAttribute('showInWebsite'));
        $this->assertEquals(0, $field->getAttribute('showInStore'));
    }

    public function testAclResourceIsRegisteredUnderAdmin(): void
    {
        $resources = ObjectManager::getInstance()->get(ProviderInterface::class)->getAclResources();

        $adminResource = $this->findResource($resources, 'Magento_Backend::admin');
        $this->assertIsArray($adminResource, 'ACL resource "Magento_Backend::admin" not found');

        $lokiResource = $this->findResource($adminResource['children'] ?? [], 'Loki_Base::config', false);
        $this->assertIsArray($lokiResource, 'ACL resource "Loki_Base::config" not found under "Magento_Backend::admin"');
        $this->assertSame('Loki Base', $lokiResource['title']);
    }

    private function findResource(array $resources, string $id, bool $recursive = true): ?array
    {
        foreach ($resources as $resource) {
            if (($resource['id'] ?? null) === $id) {
                return $resource;
            }

            if ($recursive && !empty($resource['children'])) {
                $found = $this->findResource($resource['children'], $id);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function getStructure(): Structure
    {
        return ObjectManager::getInstance()->get(Structure::class);
    }
}
