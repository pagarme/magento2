<?php

namespace Pagarme\Pagarme\Model\Api;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface as CoreRecipientInterface;
use Pagarme\Core\Middle\Factory\RecipientFactory as CoreRecipient;
use Pagarme\Pagarme\Api\KycLinkResponseInterfaceFactory;
use Pagarme\Pagarme\Api\RecipientInterface;
use Pagarme\Pagarme\Model\RecipientFactory as ModelFactoryRecipient;
use Pagarme\Pagarme\Model\ResourceModel\Recipients as ResourceModelRecipient;
use Pagarme\Pagarme\Service\Marketplace\RecipientService;
use Throwable;

class Recipient implements RecipientInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ModelFactoryRecipient
     */
    protected $modelFactoryRecipient;

    /**
     * @var ResourceModelRecipient
     */
    protected $resourceModelRecipient;

    /**
     * @var CoreRecipient
     */
    protected $coreRecipient;

    /**
     * @var RecipientService
     */
    protected $recipientService;

    /**
     * @var KycLinkResponseInterfaceFactory
     */
    protected $kycLinkResponseFactory;

    public function __construct(
        Request $request,
        ModelFactoryRecipient $modelFactoryRecipient,
        ResourceModelRecipient $resourceModelRecipient,
        CoreRecipient $coreRecipient,
        RecipientService $recipientService,
        KycLinkResponseInterfaceFactory $kycLinkResponseFactory
    ) {
        $this->request = $request;
        $this->modelFactoryRecipient = $modelFactoryRecipient;
        $this->resourceModelRecipient = $resourceModelRecipient;
        $this->coreRecipient = $coreRecipient;
        $this->recipientService = $recipientService;
        $this->kycLinkResponseFactory = $kycLinkResponseFactory;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function saveFormData(): string
    {
        $bodyParams = current($this->request->getBodyParams());
        parse_str($bodyParams, $params);
        $params = $params['form'];

        try {
            $message = __(
                "<p>He can now sell, but remember to check his "
                . "<b>status</b>. He can only withdraw his sales amounts once the status is "
                . "<i>“active”</i></p>"
            );

            if (empty($params['pagarme_id'])) {
                $recipientOnPagarme = $this->createOnPagarme($params);
                $params['pagarme_id'] = $recipientOnPagarme->id;
                $params['status'] = CoreRecipientInterface::REGISTERED;
                $message = __(
                    "<p>He can now sell, but it is necessary to complete "
                    . "the security validation so that he can withdraw the sales amounts in the future.</p>"
                    . "<p><span class='pagarme-alert-text'>Attention!</span> Keep up with the <b>status</b>. "
                    . "Once this is <i>“validation requested”</i>, a link will be "
                    . "made available for the seller to complete the process.</p>"
                );
            }
            $this->saveOnPlatform($params);

            return json_encode([
                'code' => 200,
                'message' => $message
            ]);
        } catch (Throwable $th) {
            $logService = new LogService("Recipient Log", true, 1);
            $logService->info($th->getMessage(), [
                'webkul_seller' => $params['register_information']['webkul_seller'],
                'document' => $params['register_information']['document'],
                'external_id' => $params['register_information']['external_id']

            ]);
            return json_encode([
                'code' => 400,
                'message' => __('An error occurred while saving the recipient.')
                    . ' ' . __($th->getMessage())
            ]);
        }
    }

    /**
     * @throws Exception
     */
    private function saveOnPlatform($params)
    {
        $registeredInformation = $params['register_information'];
        $recipientModel = $this->modelFactoryRecipient->create();
        $recipientModel->setId(null);
        $recipientModel->setExternalId($registeredInformation['external_id']);
        $recipientModel->setName(empty($registeredInformation['name']) ? $registeredInformation['company_name'] : $registeredInformation['name']);
        $recipientModel->setEmail($registeredInformation['email']);
        $recipientModel->setDocument($registeredInformation['document']);
        $recipientModel->setPagarmeId($params['pagarme_id']);
        $recipientModel->setType($registeredInformation['type']);
        $this->resourceModelRecipient->save($recipientModel);
    }

    /**
     * @param $recipientData
     * @return mixed
     */
    private function createOnPagarme($recipientData)
    {
        $coreRecipient = $this->coreRecipient->createRecipient($recipientData);
        return $this->recipientService->createRecipient($coreRecipient);
    }

    /**
     * @return string
     */
    public function searchRecipient(): string
    {
        $post = $this->request->getBodyParams();

        try {
            $recipient = $this->recipientService->searchRecipient($post['recipientId']);
            if ($recipient->status !== 'active') {
                throw new InvalidArgumentException(__('Recipient not active.'));
            }
        } catch (Exception $e) {
            return json_encode([
                'code' => 404,
                'message' => __($e->getMessage()),
            ]);
        }

        return json_encode([
            'code' => 200,
            'message' => __('Recipient found.'),
            'recipient' => $recipient,
        ]);
    }

    public function createKycLink(string $id)
    {
        $recipientModel = $this->modelFactoryRecipient->create();
        $this->resourceModelRecipient->load($recipientModel, $id);
        if (empty($recipientModel->getPagarmeId())) {
            throw new NoSuchEntityException(__('Recipient not founded.'));
        }
        $kycLink = $this->recipientService->createKycLink($recipientModel->getPagarmeId());
        if (empty($kycLink->url) || empty($kycLink->base64_qrcode)) {
            throw new NoSuchEntityException(__('Security validation not generated.'));
        }
        $kycResponse = $this->kycLinkResponseFactory->create();
        $kycResponse->setUrl($kycLink->url)
            ->setQrCode('data:image/svg+xml;base64,' . $kycLink->base64_qrcode);
        return $kycResponse;
    }
}
