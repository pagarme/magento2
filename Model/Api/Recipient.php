<?php

namespace Pagarme\Pagarme\Model\Api;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Middle\Factory\RecipientFactory as CoreRecipient;
use Pagarme\Pagarme\Api\RecipientInterface;
use Pagarme\Pagarme\Model\Recipient as ModelRecipient;
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
     * @var ModelRecipient
     */
    protected $modelRecipient;

    /**
     * @var ResourceModelRecipient
     */
    protected $resourceModelRecipient;

    /**
     * @var CoreRecipient
     */
    protected $coreRecipient;

    public function __construct(
        Request                $request,
        ModelRecipient         $modelRecipient,
        ResourceModelRecipient $resourceModelRecipient,
        CoreRecipient          $coreRecipient
    )
    {
        $this->request = $request;
        $this->modelRecipient = $modelRecipient;
        $this->resourceModelRecipient = $resourceModelRecipient;
        $this->coreRecipient = $coreRecipient;
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
            if (empty($params['pagarme_id'])) {
                $recipientOnPagarme = $this->createOnPagarme($params);
                $params['pagarme_id'] = $recipientOnPagarme->id;
                $params['status'] = 'registered';
            }
            $this->saveOnPlatform($params);

            return json_encode([
                'code' => 200,
                'message' => __(
                    "<p>Receiver registered successfully!</p><p>He can now sell, but it is necessary to complete "
                    . "the security validation so that he can withdraw the sales amounts in the future.</p>"
                    . "<p><span class='pagarme-alert-text'>Attention!</span> Keep up with the <b>withdrawal "
                    . "permission status</b>. Once this is <i>“validation requested”</i>, a link will be "
                    . "made available for the seller to complete the process.</p>"
                )
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
        $recipientModel = $this->modelRecipient;
        $recipientModel->setId(null);
        $recipientModel->setExternalId($registeredInformation['external_id']);
        $recipientModel->setName(empty($registeredInformation['name']) ? $registeredInformation['company_name'] : $registeredInformation['name']);
        $recipientModel->setEmail($registeredInformation['email']);
        $recipientModel->setDocument($registeredInformation['document']);
        $recipientModel->setPagarmeId($params['pagarme_id']);
        $recipientModel->setType($registeredInformation['type']);
        if (!empty($params['status'])) {
            $recipientModel->setStatus('registered');
        }
        $this->resourceModelRecipient->save($recipientModel);
    }

    /**
     * @param $recipientData
     * @return mixed
     */
    private function createOnPagarme($recipientData)
    {
        $coreRecipient = $this->coreRecipient->createRecipient($recipientData);
        $service = new RecipientService();
        return $service->createRecipient($coreRecipient);
    }

    /**
     * @return string
     */
    public function searchRecipient(): string
    {
        $post = $this->request->getBodyParams();
        $service = new RecipientService();

        try {
            $recipient = $service->searchRecipient($post['recipientId']);
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
}
