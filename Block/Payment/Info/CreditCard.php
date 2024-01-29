<?php
/**
 * Class Billet
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Block\Payment\Info;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Info\Cc;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Payment\Aggregates\Payments\Authentication\AuthenticationStatusEnum;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;

class CreditCard extends Cc
{
    const TEMPLATE = 'Pagarme_Pagarme::info/creditCard.phtml';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * @return string
     */
    public function getCcType()
    {
        return $this->getCcTypeName();
    }

    /**
     * @return string
     */
    public function getCardLast4()
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4');
    }

    /**
     * @return mixed
     */
    public function getCcBrand()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type');
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    /**
     * @return mixed
     */
    public function getInstallments()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments');
    }

    public function getThreeDSStatus()
    {
        $authenticationAdditionalInformation = $this->getInfo()->getAdditionalInformation('authentication');
        if (empty($authenticationAdditionalInformation)) {
            return ''; 
        }
        
        $authentication = json_decode($authenticationAdditionalInformation, true);
        return AuthenticationStatusEnum::statusMessage(
            $authentication['trans_status'] ?? ''
        );
    }

    /**
     * @return array
     * @throws InvalidParamException
     * @throws LocalizedException
     * @throws Exception
     */
    public function getTransactionInfo()
    {
        Magento2CoreSetup::bootstrap();
        $orderService = new OrderService();

        $orderEntityId = $this->getInfo()->getOrder()->getIncrementId();
        $platformOrder = $this->loadPlatformOrderByIncrementId($orderEntityId);

        $orderPagarmeId = $platformOrder->getPagarmeId();

        if ($orderPagarmeId === null) {
            return [];
        }

        $orderObject = $this->getOrderObjectByPagarmeId($orderService, $orderPagarmeId);

        if ($orderObject === null || !is_object($orderObject)) {
            return [];
        }

        $charge = current($orderObject->getCharges());

        return array_merge(
            $charge->getAcquirerTidCapturedAndAutorize(),
            ['tid' => $this->getTid($charge)]
        );
    }

    /**
     * @param mixed $incrementId
     * @return Magento2PlatformOrderDecorator
     */
    private function loadPlatformOrderByIncrementId($incrementId)
    {
        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($incrementId);
        return $platformOrder;
    }

    /**
     * @param mixed $orderService
     * @param mixed $pagarmeId
     * @return mixed
     */
    private function getOrderObjectByPagarmeId($orderService, $pagarmeId)
    {
        $orderId = new OrderId($pagarmeId);
        return $orderService->getOrderByPagarmeId($orderId);
    }

    /**
     * @param \Pagarme\Core\Kernel\Aggregates\Charge $charge
     * @return string|null
     */
    private function getTid(Charge $charge)
    {
        $transaction = $charge->getLastTransaction();

        $tid = null;
        if ($transaction !== null) {
            $tid = $transaction->getAcquirerTid();
        }

        return $tid;
    }
}
