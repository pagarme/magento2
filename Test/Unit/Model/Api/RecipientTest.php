<?php

namespace Pagarme\Pagarme\Test\Unit\Model\Api;

use Mockery;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Pagarme\Pagarme\Model\Api\Recipient;
use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Pagarme\Model\RecipientFactory;
use Pagarme\Pagarme\Model\Api\KycLinkResponse;
use PagarmeCoreApiLib\Models\CreateKycLinkResponse;
use Magento\Framework\Exception\NoSuchEntityException;
use Pagarme\Pagarme\Model\Recipient as RecipientModel;
use Pagarme\Pagarme\Api\KycLinkResponseInterfaceFactory;
use Pagarme\Pagarme\Service\Marketplace\RecipientService;
use Pagarme\Core\Middle\Factory\RecipientFactory as CoreRecipient;
use Pagarme\Pagarme\Model\ResourceModel\Recipients as ResourceModelRecipient;

class RecipientTest extends BaseTest
{
    public function testCreateKycLinkShouldReturnQrCodeAndLink()
    {
        $requestMock = Mockery::mock(Request::class);
        
        $recipientModelMock = Mockery::mock(RecipientModel::class);
        $recipientModelMock->shouldReceive('getPagarmeId')
            ->andReturn('rp_xxxxxxxxxxxxxxxx');

        $recipientFactoryMock = Mockery::mock(RecipientFactory::class);
        $recipientFactoryMock->shouldReceive('create')
            ->andReturn($recipientModelMock);

        $resourceModelRecipientMock = Mockery::mock(ResourceModelRecipient::class);
        $resourceModelRecipientMock->shouldReceive('load')
            ->andReturnSelf();

        $coreRecipientMock = Mockery::mock(CoreRecipient::class);

        $base64QrCode = "UGFyYWLDqW5zIHBlbGEgc3VhIGN1cmlvc2lkYWRl";
        $kycUrl = 'http://test.test/';
        $createKyLinkResponseMock = new CreateKycLinkResponse($kycUrl, $base64QrCode, '2024-04-29T09:22:08Z');
        $recipientServiceMock = Mockery::mock(RecipientService::class);
        $recipientServiceMock->shouldReceive('createKycLink')
            ->andReturn($createKyLinkResponseMock);

        $kycLinkResponse = new KycLinkResponse();
        $kycLinkResponseFactoryMock = Mockery::mock(KycLinkResponseInterfaceFactory::class);
        $kycLinkResponseFactoryMock->shouldReceive('create')
            ->andReturn($kycLinkResponse);
        
        $recipientModelApi = new Recipient(
            $requestMock,
            $recipientFactoryMock,
            $resourceModelRecipientMock,
            $coreRecipientMock,
            $recipientServiceMock,
            $kycLinkResponseFactoryMock
        );

        $result = $recipientModelApi->createKycLink(1);

        $this->assertSame($kycUrl, $result->getUrl());
        $this->assertSame($base64QrCode, $result->getQrCode());
    }

    public function testCreateKycLinkShouldNotFoundRecipient()
    {
        $requestMock = Mockery::mock(Request::class);

        $recipientModelMock = Mockery::mock(RecipientModel::class);
        $recipientModelMock->shouldReceive('getPagarmeId')
            ->andReturnNull();

        $recipientFactoryMock = Mockery::mock(RecipientFactory::class);
        $recipientFactoryMock->shouldReceive('create')
            ->andReturn($recipientModelMock);

        $resourceModelRecipientMock = Mockery::mock(ResourceModelRecipient::class);
        $resourceModelRecipientMock->shouldReceive('load')
            ->andReturnSelf();

        $coreRecipientMock = Mockery::mock(CoreRecipient::class);

        $recipientServiceMock = Mockery::mock(RecipientService::class);

        $kycLinkResponseFactoryMock = Mockery::mock(KycLinkResponseInterfaceFactory::class);
        
        $recipientModelApi = new Recipient(
            $requestMock,
            $recipientFactoryMock,
            $resourceModelRecipientMock,
            $coreRecipientMock,
            $recipientServiceMock,
            $kycLinkResponseFactoryMock
        );

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Recipient not founded.');

        $recipientModelApi->createKycLink(1);
    }
}