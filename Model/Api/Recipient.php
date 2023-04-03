<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Core\Kernel\ValueObjects\Id\RecipientId;
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

    /**
     * @var RecipientService
     */
    protected $recipientService;

    public function __construct(Request $request)
    {
        Magento2CoreSetup::bootstrap();
        $this->request = $request;
        $this->recipientService = new RecipientService();
    }

    /**
     * @return mixed
     */
    public function saveFormData(): string
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
            $this->recipientService->saveFormRecipient($form);
        } catch (\Exception $exception) {
            return json_encode([
                'code' => 400,
                'message' => $exception->getMessage()
            ]);
        }

        return json_encode([
            'code' => 200,
            'message' => 'Recipient saved'
        ]);
    }

    public function getFormattedForm(array $form): array
    {
        if (isset($form['type'])) {
            $form['holder_type'] = $form['type'];
        }

        if (isset($form['pagarme_id'])) {
            $form['recipient_id'] = $form['pagarme_id'];
        }

        return $form;
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
