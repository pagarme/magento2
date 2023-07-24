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

use Magento\Framework\DataObject;
use Magento\Payment\Block\Info;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Concrete\Magento2PlatformOrderDecorator;
use Pagarme\Pagarme\Helper\Payment\Billet as BilletHelper;

class Billet extends Info
{
    const TEMPLATE = 'Pagarme_Pagarme::info/billet.phtml';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = new DataObject([
            (string)__('Print Billet') => $this->getBilletUrl()
        ]);

        $transport = parent::_prepareSpecificInformation($transport);
        return $transport;
    }

    /**
     * @return string|null
     */
    public function getBilletUrl()
    {
        $billetHelper = new BilletHelper();
        return $billetHelper->getBilletUrl($this->getInfo());
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    public function showBilletUrl()
    {
        if($this->getInfo()->getOrder()->getStatus() !== 'pending'){
            return false;
        }
        return true;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
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

        if ($orderObject === null) {
            return [];
        }
        
        return current($orderObject->getCharges())->getLastTransaction();
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
}
