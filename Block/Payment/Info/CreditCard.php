<?php
/**
 * Class Billet
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Block\Payment\Info;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Info\Cc;
use Mundipagg\Core\Kernel\Aggregates\Charge;
use Mundipagg\Core\Kernel\Aggregates\Order;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Services\OrderService;
use Mundipagg\Core\Kernel\ValueObjects\Id\OrderId;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Concrete\Magento2PlatformOrderDecorator;

class CreditCard extends Cc
{
    const TEMPLATE = 'MundiPagg_MundiPagg::info/creditCard.phtml';

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    public function getCcType()
    {
        return $this->getCcTypeName();
    }

    public function getCardNumber()
    {
        return '**** **** **** ' . $this->getInfo()->getCcLast4();
    }

    public function getCardLast4()
    {
        return '**** **** **** ' . $this->getInfo()->getAdditionalInformation('cc_last_4');
    }

    public function getCcBrand()
    {
        return $this->getInfo()->getAdditionalInformation('cc_type');
    }

    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }

    public function getInstallments()
    {
        return $this->getInfo()->getAdditionalInformation('cc_installments');
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

        $platformOrder = new Magento2PlatformOrderDecorator();
        $platformOrder->loadByIncrementId($orderEntityId);

        $orderMundipaggId = $platformOrder->getMundipaggId();
        if ($orderMundipaggId === null) {
            return [];
        }

        /**
         * @var Order orderObject
         */
        $orderObject = $orderService->getOrderByMundiPaggId(new OrderId($orderMundipaggId));

        return array_merge(
            $orderObject->getCharges()[0]->getAcquirerTidCapturedAndAutorize(),
            ['tid' => $this->getTid($orderObject->getCharges()[0])]
        );
    }

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
