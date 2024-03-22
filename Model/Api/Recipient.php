<?php

namespace Pagarme\Pagarme\Model\Api;

use Exception;
use Throwable;
use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Pagarme\Api\RecipientInterface;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Pagarme\Model\Recipient as ModelRecipient;
use Pagarme\Pagarme\Service\Marketplace\RecipientService;
use Pagarme\Core\Middle\Factory\RecipientFactory as CoreRecipient;
use Pagarme\Pagarme\Model\ResourceModel\Recipients as ResourceModelRecipient;

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
     */
    public function saveFormData(): string
    {
        $bodyParams = current($this->request->getBodyParams());
        parse_str($bodyParams, $params);
        $params = $params['form'];
        /**
         * @todo Remove after tests
         */
        $randNumber = rand(10000, 20000);
        $params['register_information']['external_id'] = $randNumber . $params['register_information']['external_id'];

        try {
            if (!empty($params['pagarme_id'])) {
                $this->saveOnPlatform($params['register_information'], $params['pagarme_id']);
            } else {
                $recipientOnPagarme = $this->createOnPagarme($params);
                $this->saveOnPlatform($params['register_information'], $recipientOnPagarme->id);
            }
            return json_encode([
                'code' => 200,
                'message' => __('Recipient saved successfully!')
            ]);
        } catch (Throwable $th) {
            $logService = new LogService("Recipient Log", true, 1);
            $logService->info($th->getMessage(), [
                'webkul_seller' => $params['register_information']['webkul_seller'],
                'document'      => $params['register_information']['document'],
                'external_id'   => $params['register_information']['external_id']
                
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
    private function saveOnPlatform($params, $pagarmeId)
    {
            $recipientModel = $this->modelRecipient;
            $recipientModel->setId(null);
            $recipientModel->setExternalId($params['external_id']);
            $recipientModel->setName(empty($params['name']) ? $params['company_name'] : $params['name']);
            $recipientModel->setEmail($params['email']);
            $recipientModel->setDocument($params['document']);
            $recipientModel->setPagarmeId($pagarmeId);
            $recipientModel->setType($params['type']);
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
            if ($recipient->status != 'active') {
                throw new Exception(__('Recipient not active.'));
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
