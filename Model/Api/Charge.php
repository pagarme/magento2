<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\Webapi\Exception as MagentoException;
use Pagarme\Core\Kernel\Services\ChargeService;
use Pagarme\Pagarme\Model\Api\ResponseMessage as ApiResponseMessage;
use Pagarme\Pagarme\Api\ChargeApiInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class Charge implements ChargeApiInterface
{
    /**
     * @var ChargeService
     */
    private $chargeService;

    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->chargeService = new ChargeService();
    }

    /**
     * @param string $id
     * @return object
     * @throws Exception
     */
    public function cancel($id)
    {
        try {
            $response = $this->chargeService->cancelById($id);

            if (is_string($response)) {
                throw new MagentoException(__($response), 0, 400);
            }

            if ($response->isSuccess()) {
                $message = new ApiResponseMessage($response->getMessage());

                return $message;
            }

            throw new MagentoException(__($response->getMessage()), 0, 400);

        } catch (\Exception $exception) {
            throw new MagentoException(__($exception->getMessage()), 0, 400);
        }
    }
}