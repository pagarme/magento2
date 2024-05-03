<?php

namespace Pagarme\Pagarme\Test\Unit\Block\Adminhtml\Marketplace;

use Mockery;
use stdClass;
use ReflectionClass;
use Magento\Framework\Registry;
use Magento\Directory\Model\Country;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Magento\Framework\View\Element\Template\Context;
use Pagarme\Pagarme\Block\Adminhtml\Marketplace\Recipient;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Pagarme\Core\Marketplace\Repositories\RecipientRepository;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class RecipientTest extends BaseTest
{
    /**
     * @dataProvider statusDataProvider
     */
    public function testGetEditRecipientShouldReturnRecipientWithCorrectStatusLabel($status, $expectedStatusLabel)
    {
        $contextMock = Mockery::spy(Context::class);


        $recipientData = new stdClass();
        $recipient = new stdClass();
        $recipientData->recipient = $recipient;
        $recipientData->externalId = 'easd';
        $recipientData->localId = '234';
        $recipientData->status = $status;
        $recipientData->statusUpdated = true;

        $registryMock = Mockery::mock(Registry::class);
        $registryMock->shouldReceive('registry')
            ->with('recipient_data')
            ->andReturn(json_encode($recipientData));
        $registryMock->shouldReceive('registry')
            ->with('sellers')
            ->andReturnNull();

        $collectionMock = Mockery::mock(Collection::class);

        $collectionFactoryMock = Mockery::mock(CollectionFactory::class);
        $collectionFactoryMock->shouldReceive('create')
            ->andReturn($collectionMock);

        $countryMock = Mockery::mock(Country::class);

        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('bootstrap')
            ->andReturnSelf();

        $recipientRepositoryMock = Mockery::mock(RecipientRepository::class);

        $recipientBlock = new Recipient(
            $contextMock,
            $registryMock,
            $collectionFactoryMock,
            $countryMock,
            $recipientRepositoryMock
        );

        $editedRecipient = $recipientBlock->getEditRecipient();

        $this->assertStringContainsString("\"statusLabel\":\"$expectedStatusLabel\"", $editedRecipient);
    }


    public function testGetEditRecipientShouldReturnEmpty()
    {
        $contextSpy = Mockery::spy(Context::class);
        $registrySpy = Mockery::spy(Registry::class);
        $collectionMock = Mockery::mock(Collection::class);

        $collectionFactoryMock = Mockery::mock(CollectionFactory::class);
        $collectionFactoryMock->shouldReceive('create')
            ->andReturn($collectionMock);

        $countryMock = Mockery::mock(Country::class);

        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('bootstrap')
            ->andReturnSelf();

        $recipientRepositoryMock = Mockery::mock(RecipientRepository::class);


        $recipientBlock = new Recipient(
            $contextSpy,
            $registrySpy,
            $collectionFactoryMock,
            $countryMock,
            $recipientRepositoryMock
        );

        $this->assertEmpty($recipientBlock->getEditRecipient());
    }

    public function statusDataProvider()
    {
        return [
            "Registered status" => [RecipientInterface::REGISTERED, "Registered"],
            "Validation Request status" => [RecipientInterface::VALIDATION_REQUESTED, "Validation Requested"],
            "Waiting for analysis status" => [RecipientInterface::WAITING_FOR_ANALYSIS, "Waiting For Analysis"],
            "Active status" => [RecipientInterface::ACTIVE, "Approved"],
            "Disapproved status" => [RecipientInterface::DISAPPROVED, "Disapproved"],
            "Suspended status" => [RecipientInterface::SUSPENDED, "Suspended"],
            "Blocked status" => [RecipientInterface::BLOCKED, "Blocked"],
            "Inactive status" => [RecipientInterface::INACTIVE, "Inactive"],
        ];
    }
}
