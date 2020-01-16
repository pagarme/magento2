<?php

namespace MundiPagg\MundiPagg\Model\Api;

use Magento\Framework\Webapi\Rest\Request;
use Mundipagg\Core\Recurrence\Services\InvoiceService;
use MundiPagg\MundiPagg\Api\InvoiceApiInterface;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

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