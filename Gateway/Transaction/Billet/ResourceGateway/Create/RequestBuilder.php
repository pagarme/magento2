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

namespace MundiPagg\MundiPagg\Gateway\Transaction\Billet\ResourceGateway\Create;

use function Couchbase\defaultDecoder;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Item;
use MundiPagg\MundiPagg\Api\BilletRequestDataProviderInterface;
use MundiPagg\MundiPagg\Api\BilletRequestDataProviderInterfaceFactory;
use MundiPagg\MundiPagg\Api\CartItemRequestDataProviderInterface;
use MundiPagg\MundiPagg\Api\CartItemRequestDataProviderInterfaceFactory;
use Magento\Checkout\Model\Cart;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\Config;
use MundiPagg\MundiPagg\Helper\ModuleHelper;
use MundiPagg\MundiPagg\Model\Source\Bank;
use MundiPagg\MundiPagg\Helper\CustomerCustomAttributesHelper;
use Magento\Customer\Model\Session;
use MundiPagg\MundiPagg\Model\Payment;
use Magento\Customer\Api\AddressRepositoryInterface;


class RequestBuilder implements BuilderInterface
{

    const MODULE_NAME = 'MundiPagg_MundiPagg';
    const NAME_METADATA = 'Magento 2';
    const SHIPPING = 1;
    const BILLING = 0;
    
    protected $request;
    /** @var  BoletoTransaction */
    protected $transaction;
    protected $requestDataProviderFactory;
    protected $cartItemRequestDataProviderFactory;
    protected $orderAdapter;
    protected $cart;
    protected $config;
    protected $moduleHelper;
    protected $bank;
    protected $paymentData;
    protected $customerCustomAttributesHelper;
    protected $customerSession;
    protected $payment;
    protected $addressRepositoryInterface;


    /**
     * RequestBuilder constructor.
     * @param BilletRequestDataProviderInterfaceFactory $requestDataProviderFactory
     * @param CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory
     * @param Cart $cart
     * @param Config $config
     * @param ModuleHelper $moduleHelper
     * @param Bank $bank
     * @param CustomerCustomAttributesHelper $customerCustomAttributesHelper
     * @param Payment $payment
     * @param CustomerCustomAttributesHelper $customerCustomAttributesHelper*
     */
    public function __construct(
        BilletRequestDataProviderInterfaceFactory $requestDataProviderFactory,
        CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory,
        Cart $cart,
        Config $config,
        ModuleHelper $moduleHelper,
        Bank $bank,
        CustomerCustomAttributesHelper $customerCustomAttributesHelper,
        Session $customerSession,
        Payment $payment,
        AddressRepositoryInterface $addressRepositoryInterface
    )
    {
        $this->setRequestDataProviderFactory($requestDataProviderFactory);
        $this->setCartItemRequestProviderFactory($cartItemRequestDataProviderFactory);
        $this->setCart($cart);
        $this->setConfig($config);
        $this->setModuleHelper($moduleHelper);
        $this->setBank($bank);
        $this->customerCustomAttributesHelper = $customerCustomAttributesHelper;
        $this->customerSession = $customerSession;
        $this->payment = $payment;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
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

        return $this->createNewRequest($requestDataProvider);

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
     * @return BilletRequestDataProviderInterface
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
     * @param $requestDataProvider
     * @return mixed
     */
    protected function createNewRequest($requestDataProvider)
    {

        $quote = $this->getCart()->getQuote();
        $order = $this->getOrderRequest();
        $quote->reserveOrderId()->save();
        $order->code = $this->paymentData->getOrder()->getIncrementId();
        $order->payments = [
            [
                'amount' => $quote->getGrandTotal() * 100,
                'payment_method' => 'boleto',
                'capture' => false,
                'boleto' => [
                    'bank' => $this->getBank()->getBankNumber($requestDataProvider->getBankType()),
                    'instructions' => $requestDataProvider->getInstructions(),
                    'due_at' => $this->calcBoletoDays($requestDataProvider->getDaysToAddInBoletoExpirationDate())
                ]
            ]
        ];

        $document = $quote->getCustomerTaxvat() ? $quote->getCustomerTaxvat() : '';
        $this->getModuleHelper()->setTaxVat($document,true);
        
        $order->customer = [
            'name' => !empty($requestDataProvider->getName()) ? $requestDataProvider->getName() :  $quote->getBillingAddress()->getFirstName() . ' ' . $quote->getBillingAddress()->getLastName(),
            'email' => !empty($requestDataProvider->getEmail()) ? $requestDataProvider->getEmail() : $quote->getBillingAddress()->getEmail(),
            'document' => $this->getModuleHelper()->getTaxVat(),
            'type' => 'individual',
            'address' => [
                'street' => $requestDataProvider->getCustomerAddressStreet(self::BILLING),
                'number' => $requestDataProvider->getCustomerAddressNumber(self::BILLING),
                'complement' => $requestDataProvider->getCustomerAddressComplement(self::BILLING),
                'zip_code' => trim(str_replace('-','',$quote->getBillingAddress()->getPostCode())),
                'neighborhood' => $requestDataProvider->getCustomerAddressDistrict(self::BILLING),
                'city' => $quote->getBillingAddress()->getCity(),
                'state' => $quote->getBillingAddress()->getRegionCode(),
                'country' => $quote->getBillingAddress()->getCountryId()
            ]
        ];

        $this->payment->addPhonesToCustomer($order,$quote->getBillingAddress()->getTelephone(),$quote->getBillingAddress()->getFax());

        $order->ip = $requestDataProvider->getIpAddress();

        $order->shipping = [
            'amount' => $quote->getShippingAddress()->getShippingAmount() * 100,
            'address' => [
                'street' => $requestDataProvider->getCustomerAddressStreet(self::SHIPPING),
                'number' => $requestDataProvider->getCustomerAddressNumber(self::SHIPPING),
                'complement' => $requestDataProvider->getCustomerAddressComplement(self::SHIPPING),
                'zip_code' => trim(str_replace('-','',$quote->getShippingAddress()->getPostCode())),
                'neighborhood' => $requestDataProvider->getCustomerAddressDistrict(self::SHIPPING),
                'city' => $quote->getShippingAddress()->getCity(),
                'state' => $quote->getShippingAddress()->getRegionCode(),
                'country' => $quote->getShippingAddress()->getCountryId()
            ]
        ];

        $order->session_id = $requestDataProvider->getSessionId();

        $order->metadata = [
            'module_name' => self::NAME_METADATA,
            'module_version' => $this->getModuleHelper()->getVersion(self::MODULE_NAME),
        ];

        $order->items = [];
        $hasOnlyVirtual = true;
        $productInfo = $this->cart->getQuote()->getItemsCollection();
        foreach ($productInfo as $item) {
            if($item->getPrice() != 0) {
                $itemValues = [
                    'amount' => $item->getPrice() * 100,
                    'description' => $item->getName(),
                    'quantity' => $item->getTotalQty(),
                    'code' => substr($item->getSku(), 0, 50)
                ];

                array_push($order->items, $itemValues);
                $hasOnlyVirtual = $item->getIsVirtual() && $hasOnlyVirtual === true ?: false;
            }
        }

        if($hasOnlyVirtual){

            $order->shipping['address'] = $order->customer['address'];
            $order->shipping['description'] = __('Product_Virtual');

        }else{
            $order->shipping['description'] = '.';
        }

        try {
            $response = $this->getApi()->getOrders()->createOrder($order);

            $this->customerCustomAttributesHelper->setCustomerCustomAttribute($quote->getCustomer(),$response, $quote->getCustomerIsGuest());

        } catch (\MundiAPILib\Exceptions\ErrorException $error) {
            throw new \InvalidArgumentException($error);

            return $error;

        } catch (\Exception $ex) {
            throw new \InvalidArgumentException($ex->getMessage());

            return $ex;
        }

        return $response;

    }

    /**
     * @return BoletoTransaction
     */
    protected function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param BoletoTransaction $transaction
     * @return RequestBuilder
     */
    protected function setTransaction(BoletoTransaction $transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }

    /**
     * @return BilletRequestDataProviderInterfaceFactory
     */
    protected function getRequestDataProviderFactory()
    {
        return $this->requestDataProviderFactory;
    }

    /**
     * @param BilletRequestDataProviderInterfaceFactory $requestDataProviderFactory
     * @return self13
     */
    protected function setRequestDataProviderFactory(BilletRequestDataProviderInterfaceFactory $requestDataProviderFactory)
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

    public function calcBoletoDays($days)
    {

        $pattern = 'T00:00:00Z';

        return date('Y-m-d', strtotime(date('Y-m-d') . ' + ' . $days . ' days')) . $pattern;

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
     * @param $config
     */
    public function setConfig($config)
    {
        $this->config = $config;

    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
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
    public function getOrderRequest()
    {
        return new \MundiAPILib\Models\CreateOrderRequest();
    }

    /**
     * @return mixed
     */
    public function setConfigCreditCard($configCreditCard)
    {
        $this->configCreditCard = $configCreditCard;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getModuleHelper()
    {
        return $this->moduleHelper;
    }

    /**
     * @return mixed
     */
    public function setModuleHelper($moduleHelper)
    {
        $this->moduleHelper = $moduleHelper;

        return $this;
    }

    /**
     * @return Bank
     */
    protected function getBank()
    {
        return $this->bank;
    }

    /**
     * @param Bank $bank
     */
    protected function setBank($bank)
    {
        $this->bank = $bank;
    }



    /**
     * @return mixed
     */
    public function getCartItemRequestDataProviderFactory()
    {
        return $this->cartItemRequestDataProviderFactory;
    }

    /**
     * @param mixed $cartItemRequestDataProviderFactory
     *
     * @return self
     */
    public function setCartItemRequestDataProviderFactory($cartItemRequestDataProviderFactory)
    {
        $this->cartItemRequestDataProviderFactory = $cartItemRequestDataProviderFactory;

        return $this;
    }
}
