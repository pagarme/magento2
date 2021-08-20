<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Core\Marketplace\Services\RecipientService;
use Pagarme\Core\Recurrence\Services\PlanService;
use Pagarme\Pagarme\Api\RecipientInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class Recipient implements RecipientInterface
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        Magento2CoreSetup::bootstrap();
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function saveFormData()
    {
        $post = $this->request->getBodyParams();
        parse_str($post[0], $params);

        $form = $this->getFormattedForm($params['form']);

        if (empty($form)) {
            return json_encode([
                'code' => 400,
                'message' => 'Error on save recipient'
            ]);
        }

        try {
            $recipientService = new RecipientService();
            $recipient = $recipientService->saveFormRecipient($form);
        } catch (\Exception $exception) {
            return json_encode([
                'code' => 404,
                'message' => $exception->getMessage()
            ]);
        }

        return json_encode([
            'code' => 200,
            'message' => 'Recipient saved'
        ]);
    }

    public function getFormattedForm($form)
    {
        if (isset($form['type'])) {
            $form['holder_type'] = $form['type'];
        }

        return $form;
    }
}
