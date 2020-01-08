<?php

namespace MundiPagg\MundiPagg\Model\Api;

use Magento\Framework\Webapi\Rest\Request;


use MundiPagg\MundiPagg\Api\InvoiceApiInterface;

class Invoice implements InvoiceApiInterface
{

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var SubscriptionService
     */
    protected $subscriptionService;

    public function __construct(Request $request)
    {
        $this->request = $request;
        /*Magento2CoreSetup::bootstrap();
        $this->i18n = new LocalizationService();
        $this->moneyService = new MoneyService();
        $this->subscriptionService = new SubscriptionService();*/
    }

    /**
     * List product subscription
     *
     * @return mixed
     */
    public function list($id)
    {
        return;
    }

    /**
     * Cancel subscription
     *
     * @param int $id
     * @return mixed
     */
    public function cancel($id)
    {
        return;
    }
}