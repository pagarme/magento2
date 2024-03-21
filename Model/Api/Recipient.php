<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Pagarme\Api\RecipientInterface;
use Pagarme\Pagarme\Model\Recipient as ModelReciepient;
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
     * @var ModelReciepient
     */
    protected $modelReciepient;

     /**
     * @var ResourceModelRecipient
     */
    protected $resourceModelRecipient;
    
    /**
     * @var CoreRecipient
     */
    protected $coreRecipient;

    public function __construct(
        Request $request,
        ModelReciepient $modelReciepient,
        ResourceModelRecipient $resourceModelRecipient,
        CoreRecipient $coreRecipient
    ) {
        $this->request = $request;
        $this->modelReciepient = $modelReciepient;
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
            if(!empty($params['pagarme_id'])) {
                $this->saveOnPlatform($params['register_information'], $params['pagarme_id']);
            } else {
                $recipientOnPagarme = $this->createOnPagarme($params);
                $this->saveOnPlatform($params['register_information'], $recipientOnPagarme->id);
            }
            return json_encode([
                'code' => 200,
                'message' => 'Recipient saved!'
            ]);
            } catch (\Throwable $th) {
                return json_encode([
                    'code' => 400,
                    'message' => $th->getMessage()
                ]);
            } catch (\Exception $e) {
                return json_encode([
                    'code' => 400,
                    'message' => $e->getMessage()
                ]);
            }
    }


    private function saveOnPlatform($params, $pagarmeId) 
    {
        try {
            $recipientModel = $this->modelReciepient;
            $recipientModel->setId(null);
            $recipientModel->setExternalId($params['external_id']);
            $recipientModel->setName($params['name']);
            $recipientModel->setEmail($params['email']);
            $recipientModel->setDocument($params['document']);
            $recipientModel->setPagarmeId($pagarmeId);
            $recipientModel->setType($params['type']);
            $this->resourceModelRecipient->save($recipientModel);
        } catch (\Exception $e) {
            throw new \Exception("Ocorreu um erro ao salvar no Magento");
        }
    }

    private function createOnPagarme($recipientData) 
    {
        $coreRecipient = $this->coreRecipient->createRecipient($recipientData);
        $service = new RecipientService();
        return $service->createRecipient($coreRecipient);
    }
    public function searchRecipient(): string
    {
        $post = $this->request->getBodyParams();
        $service = new RecipientService();

        try {
            $recipient = $service->searchRecipient($post['recipientId']);
            if ($recipient->status != 'active') {
                throw new \Exception('Recipient not active');
            }
        } catch (\Exception $e) {
            return json_encode([
                'code' => 404,
                'message' => __($e->getMessage()),
            ]);
        }

        return json_encode([
            'code' => 200,
            'message' => __('Recipient finded'),
            'recipient' => $recipient,
        ]);
    }
}
