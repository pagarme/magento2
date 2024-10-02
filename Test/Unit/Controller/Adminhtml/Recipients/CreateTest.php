<?php

namespace Pagarme\Pagarme\Test\Unit\Controller\Adminhtml\Recipients;

use Mockery;
use Magento\Framework\Registry;
use Pagarme\Pagarme\Model\Recipient;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use PagarmeCoreApiLib\Models\GetRecipientResponse;
use Magento\Framework\Module\Manager as ModuleManager;
use Pagarme\Pagarme\Service\Marketplace\RecipientService;
use Pagarme\Pagarme\Controller\Adminhtml\Recipients\Create;
use Magento\Framework\Message\Factory as MagentoMessageFactory;
use Pagarme\Pagarme\Model\ResourceModel\Recipients as ResourceModelRecipient;

class CreateTest extends BaseTest
{
    public function testExecuteShouldUpdateRecipientStatus()
    {
        $contextSpy = Mockery::spy(Context::class);

        $sellerCollectionMock = Mockery::mock('Webkul\Marketplace\Model\ResourceModel\Seller\Collection');
        $sellerCollectionMock->shouldReceive('getItems')
            ->andReturn([]);

        $sellerFactoryMock = Mockery::mock('Webkul\Marketplace\Model\SellerFactory');
        $sellerFactoryMock->shouldReceive('create')
            ->andReturnSelf();
        $sellerFactoryMock->shouldReceive('getCollection')
            ->andReturnSelf();
        $sellerFactoryMock->shouldReceive('load')
            ->andReturn($sellerCollectionMock);

        $requestMock = Mockery::mock(RequestInterface::class);
        $requestMock->shouldReceive('getParam')
            ->andReturn(1);

        $objectManagerMock = Mockery::mock(ObjectManagerInterface::class);
        $objectManagerMock->shouldReceive('create')
            ->andReturn($sellerFactoryMock);

        $contextSpy->shouldReceive('getObjectManager')
            ->andReturn($objectManagerMock);
        $contextSpy->shouldReceive('getRequest')
            ->andReturn($requestMock);

        $registryMock = Mockery::mock(Registry::class);
        $registryMock->shouldReceive('register')
            ->withArgs(function ($key, $data) {
                return $key === 'sellers' || ($key === 'recipient_data' && strpos($data, '"statusUpdated":true') !== false);
            })
            ->andReturnSelf();

        $pageMock = Mockery::mock(Page::class);
        $pageMock->shouldReceive('getConfig')
            ->andReturnSelf();
        $pageMock->shouldReceive('getTitle')
            ->andReturnSelf();
        $pageMock->shouldReceive('prepend')
            ->andReturnSelf();

        $pageFactoryMock = Mockery::mock(PageFactory::class);
        $pageFactoryMock->shouldReceive('create')
            ->andReturn($pageMock);

        $magentoMessageFactoryMock = Mockery::mock(MagentoMessageFactory::class);

        $moduleManagerMock = Mockery::mock(ModuleManager::class);
        $moduleManagerMock->shouldReceive('isEnabled')
            ->andReturnTrue();

        $resourceModelRecipientSpy = Mockery::spy(ResourceModelRecipient::class);

        $recipientSpy = Mockery::spy(Recipient::class);
        $recipientSpy->shouldReceive('getStatus')
            ->andReturnNull();

        $recipientResponse = new GetRecipientResponse();
        $recipientResponse->id = 'rp_xxxxxxxxxxx';
        $recipientResponse->status = 'active';
        $recipientServiceMock = Mockery::mock(RecipientService::class);
        $recipientServiceMock->shouldReceive('searchRecipient')
            ->andReturn($recipientResponse);

        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('bootstrap')
            ->andReturnSelf();

        $createController = new Create(
            $contextSpy,
            $registryMock,
            $pageFactoryMock,
            $magentoMessageFactoryMock,
            $moduleManagerMock,
            $resourceModelRecipientSpy,
            $recipientSpy,
            $recipientServiceMock
        );


        $this->assertInstanceOf(Page::class, $createController->execute());
    }

    public function testExecuteShouldNotUpdateRecipientStatus()
    {
        $contextSpy = Mockery::spy(Context::class);

        $sellerCollectionMock = Mockery::mock('Webkul\Marketplace\Model\ResourceModel\Seller\Collection');
        $sellerCollectionMock->shouldReceive('getItems')
            ->andReturn([]);

        $sellerFactoryMock = Mockery::mock('Webkul\Marketplace\Model\SellerFactory');
        $sellerFactoryMock->shouldReceive('create')
            ->andReturnSelf();
        $sellerFactoryMock->shouldReceive('getCollection')
            ->andReturnSelf();
        $sellerFactoryMock->shouldReceive('load')
            ->andReturn($sellerCollectionMock);

        $requestMock = Mockery::mock(RequestInterface::class);
        $requestMock->shouldReceive('getParam')
            ->andReturn(1);

        $objectManagerMock = Mockery::mock(ObjectManagerInterface::class);
        $objectManagerMock->shouldReceive('create')
            ->andReturn($sellerFactoryMock);

        $contextSpy->shouldReceive('getObjectManager')
            ->andReturn($objectManagerMock);
        $contextSpy->shouldReceive('getRequest')
            ->andReturn($requestMock);

        $registryMock = Mockery::mock(Registry::class);
        $registryMock->shouldReceive('register')
            ->withArgs(function ($key, $data) {
                return $key === 'sellers' || ($key === 'recipient_data' && strpos($data, '"statusUpdated":false') !== false);
            })
            ->andReturnSelf();

        $pageMock = Mockery::mock(Page::class);
        $pageMock->shouldReceive('getConfig')
            ->andReturnSelf();
        $pageMock->shouldReceive('getTitle')
            ->andReturnSelf();
        $pageMock->shouldReceive('prepend')
            ->andReturnSelf();

        $pageFactoryMock = Mockery::mock(PageFactory::class);
        $pageFactoryMock->shouldReceive('create')
            ->andReturn($pageMock);

        $magentoMessageFactoryMock = Mockery::mock(MagentoMessageFactory::class);

        $moduleManagerMock = Mockery::mock(ModuleManager::class);
        $moduleManagerMock->shouldReceive('isEnabled')
            ->andReturnTrue();

        $resourceModelRecipientSpy = Mockery::spy(ResourceModelRecipient::class);

        $recipientSpy = Mockery::spy(Recipient::class);
        $recipientSpy->shouldReceive('getStatus')
            ->andReturn('active');

        $recipientResponse = new GetRecipientResponse();
        $recipientResponse->id = 'rp_xxxxxxxxxxx';
        $recipientResponse->status = 'active';
        $recipientServiceMock = Mockery::mock(RecipientService::class);
        $recipientServiceMock->shouldReceive('searchRecipient')
            ->andReturn($recipientResponse);

        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('bootstrap')
            ->andReturnSelf();

        $createController = new Create(
            $contextSpy,
            $registryMock,
            $pageFactoryMock,
            $magentoMessageFactoryMock,
            $moduleManagerMock,
            $resourceModelRecipientSpy,
            $recipientSpy,
            $recipientServiceMock
        );


        $this->assertInstanceOf(Page::class, $createController->execute());
    }
}
