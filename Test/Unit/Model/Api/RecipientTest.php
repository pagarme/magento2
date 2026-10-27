<?php

namespace Pagarme\Pagarme\Test\Unit\Model\Api;

use Mockery;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Pagarme\Pagarme\Model\Api\Recipient;
use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Pagarme\Model\RecipientFactory;
use Pagarme\Pagarme\Model\Api\KycLinkResponse;
use Pagarme\Pagarme\Service\Marketplace\KycLinkService;
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
        $recipientModelMock = Mockery::mock(RecipientModel::class);
        $recipientModelMock->shouldReceive('getPagarmeId')
            ->andReturn('rp_xxxxxxxxxxxxxxxx');

        $base64QrCode = "UGFyYWLDqW5zIHBlbGEgc3VhIGN1cmlvc2lkYWRl";
        $kycUrl = 'http://test.test/';
        $createKyLinkResponseMock = new CreateKycLinkResponse($kycUrl, $base64QrCode, '2024-04-29T09:22:08Z');
        $recipientServiceMock = Mockery::mock(RecipientService::class);
        $recipientServiceMock->shouldReceive('createKycLink')
            ->andReturn($createKyLinkResponseMock);

        $recipientModelApi = $this->makeRecipientApi($recipientModelMock, $recipientServiceMock);

        $result = $recipientModelApi->createKycLink(1);

        $expectedBase64QrCode = "data:image/svg+xml;base64,$base64QrCode";
        $this->assertSame($kycUrl, $result->getUrl());
        $this->assertSame($expectedBase64QrCode, $result->getQrCode());
    }

    public function testCreateKycLinkShouldNotFoundRecipient()
    {
        $recipientModelMock = Mockery::mock(RecipientModel::class);
        $recipientModelMock->shouldReceive('getPagarmeId')
            ->andReturnNull();

        $recipientServiceMock = Mockery::mock(RecipientService::class);

        $recipientModelApi = $this->makeRecipientApi($recipientModelMock, $recipientServiceMock);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Recipient not founded.');

        $recipientModelApi->createKycLink(1);
    }

    public function testCreateKycLinkShouldNotGenerateSecurityValidation()
    {
        $recipientModelMock = Mockery::mock(RecipientModel::class);
        $recipientModelMock->shouldReceive('getPagarmeId')
            ->andReturn('rp_xxxxxxxxxxxxxxxx');

        $createKyLinkResponseMock = new CreateKycLinkResponse();
        $recipientServiceMock = Mockery::mock(RecipientService::class);
        $recipientServiceMock->shouldReceive('createKycLink')
            ->andReturn($createKyLinkResponseMock);

        $recipientModelApi = $this->makeRecipientApi($recipientModelMock, $recipientServiceMock);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Failed to generate the security validation link.');

        $recipientModelApi->createKycLink(1);
    }

    private function makeRecipientApi($recipientModelMock, $recipientServiceMock): Recipient
    {
        $recipientFactoryMock = Mockery::mock(RecipientFactory::class);
        $recipientFactoryMock->shouldReceive('create')
            ->andReturn($recipientModelMock);

        $resourceModelRecipientMock = Mockery::mock(ResourceModelRecipient::class);
        $resourceModelRecipientMock->shouldReceive('load')
            ->andReturnSelf();

        $kycLinkResponseFactoryMock = Mockery::mock(KycLinkResponseInterfaceFactory::class);
        $kycLinkResponseFactoryMock->shouldReceive('create')
            ->andReturn(new KycLinkResponse());

        $kycLinkService = new KycLinkService($recipientFactoryMock, $resourceModelRecipientMock, $recipientServiceMock);

        return new Recipient(
            Mockery::mock(Request::class),
            $recipientFactoryMock,
            $resourceModelRecipientMock,
            Mockery::mock(CoreRecipient::class),
            $recipientServiceMock,
            $kycLinkResponseFactoryMock,
            $kycLinkService
        );
    }
}
