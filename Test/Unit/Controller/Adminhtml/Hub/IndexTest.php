<?php

namespace Pagarme\Pagarme\Test\Unit\Controller\Adminhtml\Hub;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Pagarme\Controller\Adminhtml\Hub\Index;
use Pagarme\Pagarme\Test\Unit\BaseTest;

class IndexTest extends BaseTest
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Index
     */
    private $hubIndex;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hubIndex = new Index(
            Mockery::spy(Context::class),
            Mockery::mock(PageFactory::class),
            Mockery::mock(WriterInterface::class),
            Mockery::mock(Manager::class),
            Mockery::mock(RequestInterface::class),
            Mockery::mock(StoreManagerInterface::class)
        );
    }

    /**
     * Test that encodePoiType returns null when poiType is null,
     * preventing the string "null" from being persisted.
     */
    public function testEncodePoiTypeReturnsNullWhenPoiTypeIsNull()
    {
        // Arrange
        $poiType = null;

        // Act
        $result = $this->callEncodePoiType($poiType);

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test that encodePoiType never produces the literal string "null",
     * which would cause the read side to deserialize it as ["null"].
     */
    public function testEncodePoiTypeDoesNotProduceNullString()
    {
        // Arrange
        $poiType = null;

        // Act
        $result = $this->callEncodePoiType($poiType);

        // Assert
        $this->assertNotSame('null', $result);
    }

    /**
     * Test that encodePoiType serializes a valid array to a JSON string.
     */
    public function testEncodePoiTypeReturnsJsonStringWhenPoiTypeIsArray()
    {
        // Arrange
        $poiType = ['type' => 'physical'];

        // Act
        $result = $this->callEncodePoiType($poiType);

        // Assert
        $this->assertSame('{"type":"physical"}', $result);
    }

    /**
     * Test the full round-trip for a valid array:
     * encode → persist → decode must reproduce the original value.
     */
    public function testPoiTypeArrayRoundTripPreservesOriginalValue()
    {
        // Arrange
        $poiType = ['type' => 'physical', 'location' => 'store'];

        // Act
        $encoded = $this->callEncodePoiType($poiType);
        $decoded = json_decode($encoded, true);
        $hydrated = is_array($decoded) ? $decoded : null;

        // Assert
        $this->assertSame($poiType, $hydrated);
    }

    /**
     * Test the full round-trip for null:
     * encode(null) must produce null (not "null"), so decode never produces ["null"].
     */
    public function testPoiTypeNullRoundTripProducesNullNotNullStringArray()
    {
        // Arrange
        $poiType = null;

        // Act
        $encoded = $this->callEncodePoiType($poiType);
        $decoded = json_decode($encoded, true);
        $hydrated = is_array($decoded) ? $decoded : null;

        // Assert
        $this->assertNull($hydrated);
        $this->assertNotSame(['null'], $hydrated);
    }

    /**
     * @param array|null $value
     * @return string|null
     */
    private function callEncodePoiType($value)
    {
        $method = (new \ReflectionClass($this->hubIndex))->getMethod('encodePoiType');
        $method->setAccessible(true);
        return $method->invoke($this->hubIndex, $value);
    }
}
