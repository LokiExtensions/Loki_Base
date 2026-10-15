<?php declare(strict_types=1);

namespace Loki\Base\Test\Unit\ViewModel\Block;

use Loki\Base\ViewModel\Block\ChildCounter;
use PHPUnit\Framework\TestCase;

class ChildCounterTest extends TestCase
{
    public function testGetCounterIncrementsPerName(): void
    {
        $childCounter = new ChildCounter();
        $this->assertSame(0, $childCounter->getCounter('foo'));
        $this->assertSame(1, $childCounter->getCounter('foo'));
        $this->assertSame(2, $childCounter->getCounter('foo'));
    }

    public function testGetCounterIsIndependentPerName(): void
    {
        $childCounter = new ChildCounter();
        $this->assertSame(0, $childCounter->getCounter('foo'));
        $this->assertSame(1, $childCounter->getCounter('foo'));
        $this->assertSame(0, $childCounter->getCounter('bar'));
        $this->assertSame(2, $childCounter->getCounter('foo'));
        $this->assertSame(1, $childCounter->getCounter('bar'));
    }
}
