<?php
/**
 * Class Pix
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Block\Payment;

use Exception;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Asset\Repository;
use Magento\Sales\Api\Data\OrderInterface as Order;
use Magento\Sales\Api\Data\OrderPaymentInterface as Payment;
use Pagarme\Pagarme\Helper\Payment\Pix as PixHelper;

class Pix extends Template
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var array
     */
    private $pixInfo;

    /**
     * @var PixHelper
     */
    private $pixHelper;

    /**
     * @var Repository
     */
    private $assetRepository;

    /**
     * Pix constructor
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param PixHelper $pixHelper
     * @param Repository $assetRepository
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        PixHelper $pixHelper,
        Repository $assetRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->pixHelper = $pixHelper;
        $this->assetRepository = $assetRepository;
        parent::__construct($context, []);
    }

    /**
     * @return CheckoutSession
     */
    protected function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return Order
     */
    protected function getLastOrder()
    {
        if (! ($this->checkoutSession->getLastRealOrder()) instanceof Order) {
            throw new \InvalidArgumentException;
        }
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * @return Payment
     */
    protected function getPayment()
    {
        if (! ($this->getLastOrder()->getPayment()) instanceof Payment) {
            throw new \InvalidArgumentException;
        }
        return $this->getLastOrder()->getPayment();
    }

    /**
     * @return bool
     */
    public function showPixInformation()
    {
        return !empty($this->getPixInfo());
    }

    /**
     * @return string
     */
    public function getLogoSrc()
    {
        return $this->assetRepository->getUrl(PixHelper::LOGO_URL);
    }

    /**
     * @return string
     */
    public function getErrorCopyMessage()
    {
        return __('Failed to copy! Please, manually copy the code using the field bellow the button.');
    }

    /**
     * @return Phrase
     */
    public function getSuccessMessage()
    {
        return __('PIX code copied!');
    }

    /**
     * @return PixHelper
     * @throws Exception
     */
    public function getPixInfo()
    {
        $pagarmeTransaction = $this->checkoutSession->getPixOrBilletTransaction();
        if (empty($this->pixInfo)) {
            $this->pixInfo = $this->pixHelper->getInfo($this->getPayment(), $pagarmeTransaction);
        }

        return $this->pixInfo;
    }
}
