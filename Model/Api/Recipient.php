<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Core\Kernel\ValueObjects\Id\RecipientId;
use Pagarme\Pagarme\Model\ResourceModel\Recipients as ResourceModelRecipient;
use Pagarme\Core\Marketplace\Services\RecipientService;
use Pagarme\Core\Middle\Factory\RecipientFactory as CoreRecipient;
use Pagarme\Pagarme\Api\RecipientInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Service\Marketplace\RecipientService as RecipientMiddleProxy;
use Pagarme\Pagarme\Model\Recipient as ModelReciepient;

class Recipient implements RecipientInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var RecipientService
     */
    protected $recipientService;
    protected $modelReciepient;
    protected $resourceModelRecipient;
    protected $coreRecipient;

    public function __construct(
        Request $request,
        ModelReciepient $modelReciepient,
        ResourceModelRecipient $resourceModelRecipient,
        CoreRecipient $coreRecipient
    ) {
        Magento2CoreSetup::bootstrap();
        $this->request = $request;
        $this->modelReciepient = $modelReciepient;
        $this->resourceModelRecipient = $resourceModelRecipient;
        $this->coreRecipient = $coreRecipient;
        $this->recipientService = new RecipientService();
    }

    /**
     * @return mixed
     */
    public function saveFormData(): string
    {
        $post = $this->request->getBodyParams();
        parse_str($post[0], $params);
        /**
         * @todo Remove after tests
         */
        $randNumber = rand(10000, 20000);
        $params['form']['register_information']['external_id'] = $randNumber . $params['form']['register_information']['external_id'];

        try {
            $coreRecipient = $this->coreRecipient->createRecipient($params['form']);
            $proxy = new RecipientMiddleProxy();
            $dataResponse = $proxy->createRecipient($coreRecipient);
        } catch (\Throwable $th) {
            return json_encode([
                'code' => 400,
                'message' => $th->getMessage()
            ]);
        }

        try {
            $recipientModel = $this->modelReciepient;
            $recipientModel->setId(null);
            $recipientModel->setExternalId($params['form']['register_information']['external_id']);
            $recipientModel->setName($params['form']['register_information']['name']);
            $recipientModel->setEmail($params['form']['register_information']['email']);
            $recipientModel->setDocument($params['form']['register_information']['document']);
            $recipientModel->setPagarmeId($dataResponse->id);
            $recipientModel->setType($params['form']['register_information']['type']);
            $this->resourceModelRecipient->save($recipientModel);
            return json_encode([
                'code' => 200,
                'message' => 'Recipient saved'
            ]);
        } catch (\Throwable $th) {
            return json_encode([
                'code' => 400,
                'message' => $th->getMessage()
            ]);
        }
    }


    public function searchRecipient(): string
    {
        $post = $this->request->getBodyParams();

        try {
            $recipientId = new RecipientId($post['recipientId']);
        } catch (\Exception $e) {
            return json_encode([
                'code' => 400,
                'message' => 'Invalid Pagar.me ID'
            ]);
        }

        try {
            $recipient = $this->recipientService->findByPagarmeId($recipientId);

            if ($recipient->status != 'active') {
                throw new \Exception('Recipient not active');
            }
        } catch (\Exception $e) {
            return json_encode([
                'code' => 404,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'code' => 200,
            'message' => 'Recipient finded',
            'recipient' => $recipient,
        ]);
    }
}
