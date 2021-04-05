<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Core\Recurrence\Services\InvoiceService;
use Pagarme\Pagarme\Api\InvoiceApiInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class Invoice implements InvoiceApiInterface
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var SubscriptionService
     */
    protected $invoiceService;

    public function __construct(Request $request)
    {
        $this->request = $request;
        Magento2CoreSetup::bootstrap();
        $this->invoiceService = new InvoiceService();
    }

    /**
     * Get invoice
     *
     * @return mixed
     */
    public function getByInvoiceId($id)
    {
        $invoiceService = new InvoiceService();
        $invoice = $invoiceService->getById($id);

        return json_decode(json_encode($invoice), true);
    }

    /**
     * Cancel subscription
     *
     * @param int $id
     * @return mixed
     */
    public function cancelByInvoiceId($id)
    {
        try {
            $this->invoiceService->cancel($id);

        } catch (\Exception $exception) {
            return [
                "code" => $exception->getCode(),
                "message" => $exception->getMessage()
            ];
        }
    }
}
