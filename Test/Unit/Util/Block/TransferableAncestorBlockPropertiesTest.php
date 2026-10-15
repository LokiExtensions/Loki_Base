<?php declare(strict_types=1);

namespace Loki\Base\Test\Unit\Util\Block;

use Loki\Base\Util\Block\TransferableAncestorBlockProperties;
use PHPUnit\Framework\TestCase;

class TransferableAncestorBlockPropertiesTest extends TestCase
{
    public function testGetPropertiesIsEmptyByDefault(): void
    {
        $properties = new TransferableAncestorBlockProperties();
        $this->assertSame([], $properties->getProperties());
    }

    public function testGetPropertiesReturnsInjectedProperties(): void
    {
        $properties = new TransferableAncestorBlockProperties(['css_classes' => 'css_classes', 'foo' => 'foo']);
        $this->assertSame(['css_classes' => 'css_classes', 'foo' => 'foo'], $properties->getProperties());
    }
}
