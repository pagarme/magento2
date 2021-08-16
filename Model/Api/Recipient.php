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
        if (isset($form['internal_id'])) {
            $form['internal_id'] = $form['internal_id'];
        }

        if (isset($form['name'])) {
            $form['name'] = $form['name'];
        }

        if (isset($form['email'])) {
            $form['email'] = $form['email'];
        }

        if (isset($form['document_type'])) {
            $form['document_type'] = $form['document_type'];
        }

        if (isset($form['document_number'])) {
            $form['document_number'] = $form['document_number'];
        }

        if (isset($form['holder_name'])) {
            $form['holder_name'] = $form['holder_name'];
        }

        if (isset($form['holder_document'])) {
            $form['holder_document'] = $form['holder_document'];
        }

        if (isset($form['bank'])) {
            $form['bank'] = $form['bank'];
        }

        if (isset($form['branch_number'])) {
            $form['branch_number'] = $form['branch_number'];
        }

        if (isset($form['branch_check_digit'])) {
            $form['branch_check_digit'] = $form['branch_check_digit'];
        }

        if (isset($form['account_number'])) {
            $form['account_number'] = $form['account_number'];
        }

        if (isset($form['account_check_digit'])) {
            $form['account_check_digit'] = $form['account_check_digit'];
        }

        if (isset($form['account_type'])) {
            $form['account_type'] = $form['account_type'];
        }

        if (isset($form['account_type'])) {
            $form['account_type'] = $form['account_type'];
        }

        if (isset($form['transfer_enabled'])) {
            $form['transfer_enabled'] = $form['transfer_enabled'];
        }

        if (isset($form['transfer_interval'])) {
            $form['transfer_interval'] = $form['transfer_interval'];
        }

        if (isset($form['transfer_day'])) {
            $form['transfer_day'] = $form['transfer_day'];
        }

        return $form;
    }
}
