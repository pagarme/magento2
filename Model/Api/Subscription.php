<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Pagarme\Core\Recurrence\Services\SubscriptionService;
use Pagarme\Pagarme\Api\SubscriptionApiInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class Subscription implements SubscriptionApiInterface
{
    /**
     * @var SubscriptionService
     */
    protected $subscriptionService;

    public function __construct(State $state)
    {
        if ($state->getAreaCode() === Area::AREA_WEBAPI_REST) {
            Magento2CoreSetup::bootstrap();
            $this->subscriptionService = new SubscriptionService();
        }
    }

    /**
     * List product subscription
     *
     * @return mixed
     */
    public function list()
    {
        $result = $this->subscriptionService->listAll();
        return json_decode(json_encode($result), true);
    }

    /**
     * Cancel subscription
     *
     * @param int $id
     * @return mixed
     */
    public function cancel($id)
    {
        try {
            $response = $this->subscriptionService->cancel($id);
            return $response;
        } catch (\Exception $exception) {
            return [
                "code" => $exception->getCode(),
                "message" => $exception->getMessage()
            ];
        }
    }
}
