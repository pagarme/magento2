<?php

namespace Pagarme\Pagarme\Test\Unit\Concrete;

use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Test\Unit\BaseTest;

class Magento2CoreSetupTest extends BaseTest
{
    /**
     * @dataProvider poiTypeDecodeProvider
     */
    public function testDecodePoiTypeReturnsExpectedValue($raw, $expected)
    {
        // Arrange
        $method = (new \ReflectionClass(Magento2CoreSetup::class))->getMethod('decodePoiType');
        $method->setAccessible(true);

        // Act
        $result = $method->invoke(null, $raw);

        // Assert
        $this->assertSame($expected, $result);
    }

    public function poiTypeDecodeProvider(): array
    {
        return [
            'null raw (not persisted)'           => [null,                          null],
            'empty string'                       => ['',                             null],
            'literal string "null" (legacy bug)' => ['null',                        null],
            'plain string (non-array JSON)'      => ['"Ecommerce"',                 null],
            'valid JSON array'                   => ['["Ecommerce"]',               ['Ecommerce']],
            'valid JSON object/assoc array'      => ['{"type":"Ecommerce"}',        ['type' => 'Ecommerce']],
            'malformed JSON'                     => ['not-valid-json',              null],
        ];
    }
}
