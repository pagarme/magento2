<?php

namespace Pagarme\Pagarme\Test\Unit\Service\Marketplace;

use Mockery;
use Magento\Framework\Exception\NoSuchEntityException;
use Pagarme\Pagarme\Model\Recipient as RecipientModel;
use Pagarme\Pagarme\Model\RecipientFactory;
use Pagarme\Pagarme\Model\ResourceModel\Recipients as ResourceModelRecipient;
use Pagarme\Pagarme\Service\Marketplace\KycLinkService;
use Pagarme\Pagarme\Service\Marketplace\RecipientService;
use PagarmeCoreApiLib\Models\CreateKycLinkResponse;
use Pagarme\Pagarme\Test\Unit\BaseTest;

class KycLinkServiceTest extends BaseTest
{
    public function testCreateKycLinkForCustomerShouldReturnLinkWhenRecipientBelongsToCustomer()
    {
        $recipientModelMock = $this->makeRecipientModel('rp_xxxxxxxxxxxxxxxx', '7');

        $recipientServiceMock = Mockery::mock(RecipientService::class);
        $recipientServiceMock->shouldReceive('createKycLink')
            ->with('rp_xxxxxxxxxxxxxxxx')
            ->andReturn(new CreateKycLinkResponse('http://test.test/', 'cXI=', '2024-04-29T09:22:08Z'));

        $service = $this->makeService($recipientModelMock, $recipientServiceMock);

        $result = $service->createKycLinkForCustomer('1', 7);

        $this->assertSame('http://test.test/', $result->url);
    }

    public function testCreateKycLinkForCustomerShouldNotExposeRecipientOfAnotherCustomer()
    {
        $recipientModelMock = $this->makeRecipientModel('rp_xxxxxxxxxxxxxxxx', '8');

        $recipientServiceMock = Mockery::mock(RecipientService::class);
        $recipientServiceMock->shouldNotReceive('createKycLink');

        $service = $this->makeService($recipientModelMock, $recipientServiceMock);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Recipient not founded.');

        $service->createKycLinkForCustomer('1', 7);
    }

    public function testCreateKycLinkShouldNotFoundRecipientWithoutPagarmeId()
    {
        $recipientModelMock = $this->makeRecipientModel(null, '7');

        $recipientServiceMock = Mockery::mock(RecipientService::class);
        $recipientServiceMock->shouldNotReceive('createKycLink');

        $service = $this->makeService($recipientModelMock, $recipientServiceMock);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Recipient not founded.');

        $service->createKycLink('1');
    }

    public function testCreateKycLinkShouldFailWhenNoLinkIsGenerated()
    {
        $recipientModelMock = $this->makeRecipientModel('rp_xxxxxxxxxxxxxxxx', '7');

        $recipientServiceMock = Mockery::mock(RecipientService::class);
        $recipientServiceMock->shouldReceive('createKycLink')
            ->andReturn(new CreateKycLinkResponse());

        $service = $this->makeService($recipientModelMock, $recipientServiceMock);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Failed to generate the security validation link.');

        $service->createKycLink('1');
    }

    private function makeRecipientModel(?string $pagarmeId, string $externalId)
    {
        $recipientModelMock = Mockery::mock(RecipientModel::class);
        $recipientModelMock->shouldReceive('getPagarmeId')->andReturn($pagarmeId);
        $recipientModelMock->shouldReceive('getExternalId')->andReturn($externalId);
        return $recipientModelMock;
    }

    private function makeService($recipientModelMock, $recipientServiceMock): KycLinkService
    {
        $recipientFactoryMock = Mockery::mock(RecipientFactory::class);
        $recipientFactoryMock->shouldReceive('create')->andReturn($recipientModelMock);

        $resourceModelRecipientMock = Mockery::mock(ResourceModelRecipient::class);
        $resourceModelRecipientMock->shouldReceive('load')->andReturnSelf();

        return new KycLinkService($recipientFactoryMock, $resourceModelRecipientMock, $recipientServiceMock);
    }
}
