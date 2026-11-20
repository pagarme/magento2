<?php

namespace Pagarme\Pagarme\Test\Unit\Concrete;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Mockery;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\ValueObjects\Configuration\AddressAttributes;
use Pagarme\Core\Payment\Aggregates\Address;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class Magento2PlatformOrderDecoratorTest extends BaseTest
{
    private $previousObjectManager;

    public function setUp(): void
    {
        $this->previousObjectManager = (new ReflectionProperty(ObjectManager::class, '_instance'))
            ->getValue();

        $configuration = new Configuration();
        $configuration->setAddressAttributes(
            new AddressAttributes('street_1', 'street_2', 'street_4', 'street_3')
        );

        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('getModuleConfiguration')
            ->andReturn($configuration);

        $objectManagerMock = Mockery::mock(ObjectManagerInterface::class);
        $objectManagerMock->shouldReceive('get')
            ->with('Magento\Directory\Model\RegionFactory')
            ->andReturn(Mockery::mock(RegionFactory::class));
        ObjectManager::setInstance($objectManagerMock);
    }

    public function tearDown(): void
    {
        (new ReflectionProperty(ObjectManager::class, '_instance'))
            ->setValue(null, $this->previousObjectManager);

        parent::tearDown();
    }

    /**
     * @dataProvider postcodeProvider
     */
    public function testGetAddressFormatsPostcode($countryId, $postcode, $expectedZipCode)
    {
        $platformAddress = Mockery::mock(QuoteAddress::class);
        $platformAddress->shouldReceive('getStreet')
            ->andReturn(['Rua dos Andradas', '1001', 'Centro Historico', 'Sala 2']);
        $platformAddress->shouldReceive('getCity')->andReturn('Porto Alegre');
        $platformAddress->shouldReceive('getCountryId')->andReturn($countryId);
        $platformAddress->shouldReceive('getPostcode')->andReturn($postcode);
        $platformAddress->shouldReceive('getRegionId')->andReturn(null);

        $decorator = (new ReflectionClass(Magento2PlatformOrderDecorator::class))
            ->newInstanceWithoutConstructor();
        $getAddress = new ReflectionMethod($decorator, 'getAddress');

        $address = $getAddress->invoke($decorator, $platformAddress);

        $this->assertInstanceOf(Address::class, $address);
        $this->assertSame($expectedZipCode, $address->getZipCode());
        $this->assertSame($expectedZipCode, $address->convertToSDKRequest()->zipCode);
        $this->assertSame('Rua dos Andradas', $address->getStreet());
        $this->assertSame('1001', $address->getNumber());
        $this->assertSame('Porto Alegre', $address->getCity());
        $this->assertSame($countryId, $address->getCountry());
    }

    public function postcodeProvider()
    {
        return [
            'brazilian postcode with dot and hyphen' => ['BR', '90.110-001', '90110001'],
            'brazilian postcode with hyphen' => ['BR', '90110-001', '90110001'],
            'brazilian postcode with digits only' => ['BR', '90110001', '90110001'],
            'brazilian postcode with space' => ['BR', '90110 001', '90110001'],
            'brazilian postcode with lower-case country' => ['br', '90.110-001', '90110001'],
            'british postcode' => ['GB', 'SW1A 1AA', 'SW1A 1AA'],
            'portuguese postcode' => ['PT', '1000-001', '1000001'],
            'american zip+4' => ['US', '10001-1234', '100011234'],
        ];
    }
}
