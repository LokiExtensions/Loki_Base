<?php declare(strict_types=1);

namespace Loki\Base\Test\Unit\ViewModel;

use Loki\Base\ViewModel\FormKeyValue;
use Magento\Framework\Data\Form\FormKey;
use PHPUnit\Framework\TestCase;

class FormKeyValueTest extends TestCase
{
    public function testGet(): void
    {
        $formKey = $this->createMock(FormKey::class);
        $formKey->method('getFormKey')->willReturn('abc123');

        $this->assertSame('abc123', (new FormKeyValue($formKey))->get());
    }
}
