<?php
/**
 * Class RequestBuilder
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\BilletCreditCard\ResourceGateway\Capture;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Item;
use MundiPagg\MundiPagg\Api\CartItemRequestDataProviderInterface;
use MundiPagg\MundiPagg\Api\BilletCreditCardRequestDataProviderInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use MundiAPILib\Models\CreateOrderRequest as Request;
use MundiPagg\MundiPagg\Api\BilletCreditCardRequestDataProviderInterfaceFactory;
use MundiPagg\MundiPagg\Api\CartItemRequestDataProviderInterfaceFactory;
use Magento\Checkout\Model\Cart;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\Config;
use MundiPagg\MundiPagg\Gateway\Transaction\BilletCreditCard\Config\Config as ConfigBilletCreditCard;
use MundiPagg\MundiPagg\Model\ChargesFactory;
use MundiPagg\MundiPagg\Helper\Logger;

class RequestBuilder implements BuilderInterface
{
    protected $request;
    /** @var  BilletCreditCardTransaction */
    protected $creditCardTransaction;
    protected $requestDataProviderFactory;
    protected $cartItemRequestDataProviderFactory;
    protected $orderAdapter;
    protected $paymentData;
    protected $cart;
    protected $config;
    protected $configBilletCreditCard;

    /**
     * @var \MundiPagg\MundiPagg\Helper\Logger
     */
    protected $logger;

    /**
     * \MundiPagg\MundiPagg\Model\ChargesFactory
     */
    protected $modelCharges;

    /**
     * @param Request $request
     * @param BilletCreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory
     * @param CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory
     */
    public function __construct(
        Request $request,
        BilletCreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory,
        CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory,
        Cart $cart,
        Config $config,
        ConfigBilletCreditCard $configBilletCreditCard,
        ChargesFactory $modelCharges,
        Logger $logger
    )
    {
        $this->setRequest($request);
        $this->setRequestDataProviderFactory($requestDataProviderFactory);
        $this->setCartItemRequestProviderFactory($cartItemRequestDataProviderFactory);
        $this->setCart($cart);
        $this->setConfig($config);
        $this->setConfigBilletCreditCard($configBilletCreditCard);
        $this->modelCharges = $modelCharges;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment']) || !$buildSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDataObject */
        $paymentDataObject = $buildSubject['payment'];

        $this->setOrderAdapter($paymentDataObject->getOrder());
        $this->setPaymentData($paymentDataObject->getPayment());

        $requestDataProvider = $this->createRequestDataProvider();

        return $this->createCaptureChargeRequest($requestDataProvider);
    }

    /**
     * @param Request $request
     * @return $this
     */
    protected function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @return BilletCreditCardRequestDataProviderInterface
     */
    protected function createRequestDataProvider()
    {
        return $this->getRequestDataProviderFactory()->create([
            'orderAdapter' => $this->getOrderAdapter(),
            'payment' => $this->getPaymentData()
        ]);
    }

    /**
     * @param Item $item
     * @return CartItemRequestDataProviderInterface
     */
    protected function createCartItemRequestDataProvider(Item $item)
    {
        return $this->getCartItemRequestProviderFactory()->create([
            'item' => $item
        ]);
    }

    /**
     * @return BilletCreditCardTransaction
     */
    protected function getBilletCreditCardTransaction()
    {
        return $this->creditCardTransaction;
    }

    /**
     * @param BilletCreditCardTransaction $creditCardTransaction
     * @return RequestBuilder
     */
    protected function setBilletCreditCardTransaction($creditCardTransaction)
    {
        $this->creditCardTransaction = $creditCardTransaction;
        return $this;
    }

    /**
     * @return RequestDataProviderFactory
     */
    protected function getRequestDataProviderFactory()
    {
        return $this->requestDataProviderFactory;
    }

    /**
     * @param BilletCreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory
     * @return RequestBuilder
     */
    protected function setRequestDataProviderFactory(BilletCreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory)
    {
        $this->requestDataProviderFactory = $requestDataProviderFactory;
        return $this;
    }

    /**
     * @return CartItemRequestDataProviderInterfaceFactory
     */
    protected function getCartItemRequestProviderFactory()
    {
        return $this->cartItemRequestDataProviderFactory;
    }

    /**
     * @param CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory
     * @return self
     */
    protected function setCartItemRequestProviderFactory(CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory)
    {
        $this->cartItemRequestDataProviderFactory = $cartItemRequestDataProviderFactory;
        return $this;
    }


    /**
     * @return OrderAdapterInterface
     */
    protected function getOrderAdapter()
    {
        return $this->orderAdapter;
    }

    /**
     * @param OrderAdapterInterface $orderAdapter
     * @return $this
     */
    protected function setOrderAdapter(OrderAdapterInterface $orderAdapter)
    {
        $this->orderAdapter = $orderAdapter;
        return $this;
    }

    /**
     * @return InfoInterface
     */
    public function getPaymentData()
    {
        return $this->paymentData;
    }

    /**
     * @param InfoInterface $paymentData
     * @return $this
     */
    protected function setPaymentData(InfoInterface $paymentData)
    {
        $this->paymentData = $paymentData;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param $cart
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return mixed
     */
    public function setConfig($config)
    {
        $this->config = $config;
        
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfigBilletCreditCard()
    {
        return $this->configBilletCreditCard;
    }

    /**
     * @return mixed
     */
    public function setConfigBilletCreditCard($configBilletCreditCard)
    {
        $this->configBilletCreditCard = $configBilletCreditCard;

        return $this;
    }

    /**
     * @return \MundiAPILib\MundiAPIClient
     */
    public function getApi()
    {
        return new \MundiAPILib\MundiAPIClient($this->getConfig()->getSecretKey(), '');
    }

    /**
     * @return \MundiAPILib\Models\CreateOrderRequest
     */
    public function getCaptureRequest()
    {
        return new \MundiAPILib\Models\CreateCaptureChargeRequest();
    }

    /**
     * @param $requestDataProvider
     * @return mixed
     */
    protected function createCaptureChargeRequest($requestDataProvider)
    {
        $capture = $this->getCaptureRequest();
        $incrementId = $this->getPaymentData()->getOrder()->getIncrementId();

        $model = $this->modelCharges->create();
        $collection = $model->getCollection()->addFieldToFilter('order_id',array('eq' => $incrementId));

        if(count($collection) == 1){
            $charge = $collection->getFirstItem();
            try {
                $capture->amount = $this->getInvoiceTotalInCents();
                $capture->code = $charge->getCode();
                $this->logger->logger($capture->jsonSerialize());
                $response = $this->getApi()->getCharges()->captureCharge($charge->getChargeId(), $capture);
    
            } catch (\MundiAPILib\Exceptions\ErrorException $error) {
                $this->logger->logger($error);
                throw new \InvalidArgumentException($error->message);
            } catch (\Exception $ex) {
                $this->logger->logger($ex);
                throw new \InvalidArgumentException($ex->getMessage());
            }

            return $response;
        }else{
            $responseArray = [];
            foreach ($collection as $charge) {
                try {
                    $capture->amount = $charge->getAmount();
                    $capture->code = $charge->getCode();
                    $this->logger->logger($capture->jsonSerialize());
                    $responseArray[] = $this->getApi()->getCharges()->captureCharge($charge->getChargeId(), $capture);
                } catch (\MundiAPILib\Exceptions\ErrorException $error) {
                    $this->logger->logger($error);
                    throw new \InvalidArgumentException($error->message);
                } catch (\Exception $ex) {
                    $this->logger->logger($ex);
                    throw new \InvalidArgumentException($ex->getMessage());
                }
            }
        }

        return $responseArray;
    }

    /**
     * @return int
     */
    protected function getInvoiceTotalInCents()
    {
        $invoiceDetails = $this->getPaymentData()->getOrder()->getInvoiceCollection();

        foreach ($invoiceDetails as $invoice) {
            $invoiceBaseTotalInCents = $invoice->getBaseGrandTotal() * 100;
        }
        return $invoiceBaseTotalInCents;
    }

}
