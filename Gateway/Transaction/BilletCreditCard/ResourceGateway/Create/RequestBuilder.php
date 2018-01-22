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

namespace MundiPagg\MundiPagg\Gateway\Transaction\BilletCreditCard\ResourceGateway\Create;

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
use MundiPagg\MundiPagg\Gateway\Transaction\CreditCard\Config\Config as ConfigCreditCard;
use MundiPagg\MundiPagg\Helper\ModuleHelper;
use MundiPagg\MundiPagg\Model\CardsFactory;
use MundiPagg\MundiPagg\Model\Source\Bank;
use MundiPagg\MundiPagg\Gateway\Transaction\Billet\ResourceGateway\Create\RequestDataProvider as BilletDataProvider;
use MundiPagg\MundiPagg\Gateway\Transaction\Billet\Config\Config as ConfigBillet;

class RequestBuilder implements BuilderInterface
{

    const MODULE_NAME = 'MundiPagg_MundiPagg';

    protected $request;
    protected $requestDataProviderFactory;
    protected $cartItemRequestDataProviderFactory;
    protected $orderAdapter;
    protected $paymentData;
    protected $cart;
    protected $config;
    protected $configBilletCreditCard;
    protected $moduleHelper;
    protected $cardsFactory;
    protected $bank;
    protected $configBillet;
    protected $configCreditCard;

    /**
     * RequestBuilder constructor.
     * @param Request $request
     * @param BilletCreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory
     * @param CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory
     * @param Cart $cart
     * @param Config $config
     * @param ConfigBilletCreditCard $configBilletCreditCard
     * @param ModuleHelper $moduleHelper
     * @param CardsFactory $cardsFactory
     * @param Bank $bank
     * @param ConfigBillet $configBillet
     */
    public function __construct(
        Request $request,
        BilletCreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory,
        CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory,
        Cart $cart,
        Config $config,
        ConfigBilletCreditCard $configBilletCreditCard,
        ModuleHelper $moduleHelper,
        CardsFactory $cardsFactory,
        Bank $bank,
        ConfigBillet $configBillet,
        ConfigCreditCard $configCreditCard
    )
    {
        $this->setRequest($request);
        $this->setRequestDataProviderFactory($requestDataProviderFactory);
        $this->setCartItemRequestProviderFactory($cartItemRequestDataProviderFactory);
        $this->setCart($cart);
        $this->setConfig($config);
        $this->setConfigBilletCreditCard($configBilletCreditCard);
        $this->setModuleHelper($moduleHelper);
        $this->setCardsFactory($cardsFactory);
        $this->setBank($bank);
        $this->setConfigBillet($configBillet);
        $this->setConfigCreditCard($configCreditCard);
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
     * @return mixed
     */
    public function getConfigCreditCard()
    {
        return $this->configCreditCard;
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
     * @return \MundiAPILib\Models\CreateTokenRequest
     */
    public function getTokenRequest()
    {
        return new \MundiAPILib\Models\CreateTokenRequest();
    }

    protected function createTokenCard($requestDataProvider)
    {
        $request = $this->getTokenRequest();

        $request->card = [
            "type" => "credit",
            "number" => $requestDataProvider->getBilletCreditCardNumber(),
            "holder_name" => $requestDataProvider->getHolderName(),
            "exp_month" => $requestDataProvider->getExpMonth(),
            "exp_year" => $requestDataProvider->getExpYear(),
            "cvv" => $requestDataProvider->getSecurityCode()
        ];

        $request->metadata = [
            'module_name' => self::MODULE_NAME,
            'module_version' => $this->getModuleHelper()->getVersion(self::MODULE_NAME),
        ];

        try {

            $token = $this->getApi()->getTokens()->createToken($this->getConfig()->getPublicKey(), $request);

        } catch (\MundiAPILib\Exceptions\ErrorException $error) {
            throw new \InvalidArgumentException($error);
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException($ex->getMessage());
        }
        

        return $token->id;
    }

    /**
     * @param $requestDataProvider
     * @return mixed
     */
    protected function createNewRequest($requestDataProvider)
    {

        $quote = $this->getCart()->getQuote();

        $payment = $quote->getPayment();

        $order = $this->getOrderRequest();

        $billetConfig = $this->getConfigBillet();
        $bankType = $billetConfig->getTypeBank();

        $statement = $this->getConfigCreditCard()->getSoftDescription();

        $capture = $this->getConfigBilletCreditCard()->getPaymentAction() == '‌authorize_capture' ? true : false;

        $billetAmount = $quote->getPayment()->getCcBilletAmount() * 100;

        if ($payment->getAdditionalInformation('cc_saved_card')) {

            $model = $this->getCardsFactory();
            $cardCollection = $model->getCollection()->addFieldToFilter('id',array('eq' => $payment->getAdditionalInformation('cc_saved_card')))->getFirstItem();


            $order->payments = [
                [
                    'payment_method' => 'credit_card',
                    'amount' => $requestDataProvider->getAmountInCents() - $billetAmount,
                    'credit_card' => [
                        'recurrence' => false,
                        'installments' => $requestDataProvider->getInstallmentCount(),
                        'statement_descriptor' => $statement,
                        'capture' => $capture,
                        'card_id' => $cardCollection->getCardToken(),
                        'card' => [
                            'billing_address' => [
                                'street' => $quote->getBillingAddress()->getStreetLine(1),
                                'number' => $quote->getBillingAddress()->getStreetLine(2) . ' ' . $quote->getBillingAddress()->getStreetLine(3),
                                'zip_code' => trim(str_replace('-','',$quote->getBillingAddress()->getPostCode())),
                                'neighborhood' => $quote->getBillingAddress()->getStreetLine(4),
                                'city' => $quote->getBillingAddress()->getCity(),
                                'state' => $quote->getBillingAddress()->getRegionCode(),
                                'country' => $quote->getBillingAddress()->getCountryId()
                            ]
                        ]
                    ]
                ],
                [
                    'payment_method' => 'boleto',
                    'capture' => $capture,
                    'amount' => $billetAmount,
                    'boleto' => [
                        'bank' => $this->getBank()->getBankNumber($bankType),
                        'instructions' => 'Pagar até o vencimento',
                        'due_at' => date('Y-m-d\TH:i:s\Z')
                    ]
                ]
            ];
        }else{
            $tokenCard = $this->createTokenCard($requestDataProvider);

            $order->payments = [
                [
                    'payment_method' => 'credit_card',
                    'amount' => $requestDataProvider->getAmountInCents() - $billetAmount,
                    'credit_card' => [
                        'recurrence' => false,
                        'installments' => $requestDataProvider->getInstallmentCount(),
                        'statement_descriptor' => $statement,
                        'capture' => $capture,
                        'card_token' => $tokenCard,
                        'card' => [
                            'billing_address' => [
                                'street' => $quote->getBillingAddress()->getStreetLine(1),
                                'number' => $quote->getBillingAddress()->getStreetLine(2) . ' ' . $quote->getBillingAddress()->getStreetLine(3),
                                'zip_code' => trim(str_replace('-','',$quote->getBillingAddress()->getPostCode())),
                                'neighborhood' => $quote->getBillingAddress()->getStreetLine(4),
                                'city' => $quote->getBillingAddress()->getCity(),
                                'state' => $quote->getBillingAddress()->getRegionCode(),
                                'country' => $quote->getBillingAddress()->getCountryId()
                            ]
                        ]
                    ]
                ],
                [
                    'payment_method' => 'boleto',
                    'capture' => $capture,
                    'amount' => $billetAmount,
                    'boleto' => [
                        'bank' => $this->getBank()->getBankNumber($bankType),
                        'instructions' => 'Pagar até o vencimento',
                        'due_at' => date('Y-m-d\TH:i:s\Z')
                    ]
                ]
            ];
        }

        $order->items = [];

        foreach ($requestDataProvider->getCartItems() as $key => $item) {

            $cartItemDataProvider = $this->createCartItemRequestDataProvider($item);

            $itemValues = [
                'amount' => $cartItemDataProvider->getUnitCostInCents(),
                'description' => $cartItemDataProvider->getName(),
                'quantity' => $cartItemDataProvider->getQuantity()
            ];

            array_push($order->items, $itemValues);

        }
        $document = $quote->getCustomerTaxvat() ? $quote->getCustomerTaxvat() : $quote->getShippingAddress()->getVatId() ;

        $order->customer = [
            'name' => !empty($requestDataProvider->getName()) ? $requestDataProvider->getName() :  $quote->getBillingAddress()->getFirstName() . ' ' . $quote->getBillingAddress()->getLastName(),
            'email' => !empty($requestDataProvider->getEmail()) ? $requestDataProvider->getEmail() : $quote->getBillingAddress()->getEmail(),
            'document' => $document,
            'type' => 'individual',
            'address' => [
                'street' => $quote->getShippingAddress()->getStreetLine(1),
                'number' => $quote->getShippingAddress()->getStreetLine(2),
                'zip_code' => trim(str_replace('-','',$quote->getShippingAddress()->getPostCode())),
                'neighborhood' => $quote->getShippingAddress()->getStreetLine(4),
                'city' => $quote->getShippingAddress()->getCity(),
                'state' => $quote->getShippingAddress()->getRegionCode(),
                'country' => $quote->getShippingAddress()->getCountryId()
            ]
        ];

        $order->ip = $requestDataProvider->getIpAddress();

        $order->shipping = [
            'amount' => $quote->getShippingAddress()->getShippingAmount() * 100,
            'description' => $cartItemDataProvider->getName(),
            'address' => [
                'street' => $quote->getShippingAddress()->getStreetLine(1),
                'number' => $quote->getShippingAddress()->getStreetLine(2),
                'zip_code' => trim(str_replace('-','',$quote->getShippingAddress()->getPostCode())),
                'neighborhood' => $quote->getShippingAddress()->getStreetLine(4),
                'city' => $quote->getShippingAddress()->getCity(),
                'state' => $quote->getShippingAddress()->getRegionCode(),
                'country' => $quote->getShippingAddress()->getCountryId()
            ]
        ];

        $order->session_id = $requestDataProvider->getSessionId();

        $order->metadata = [
            'module_name' => self::MODULE_NAME,
            'module_version' => $this->getModuleHelper()->getVersion(self::MODULE_NAME),
        ];

        if ($this->getConfigBilletCreditCard()->getAntifraudActive() && $quote->getGrandTotal() > $this->getConfigBilletCreditCard()->getAntifraudMinAmount()) {
            $order->antifraud_enabled = true;
        }

        try {

            $response = $this->getApi()->getOrders()->createOrder($order);

            if($requestDataProvider->getSaveCard() == '1')
            {
                $customer = $response->customer;

                $this->setCardToken($requestDataProvider, $customer, $quote);
                
            }

        } catch (\MundiAPILib\Exceptions\ErrorException $error) {
            throw new \InvalidArgumentException($error);
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException($ex->getMessage());
        }

        return $response;

    }

    /**
     * @return \MundiAPILib\Models\CreateTokenRequest
     */
    public function getCardRequest()
    {
        return new \MundiAPILib\Models\CreateCardRequest();
    }

    protected function setCardToken($requestDataProvider, $customer, $quote)
    {
        $request = $this->getCardRequest();

        $request->number = $requestDataProvider->getBilletCreditCardNumber();
        $request->holderName = $requestDataProvider->getHolderName();
        $request->expMonth = $requestDataProvider->getExpMonth();
        $request->expYear = $requestDataProvider->getExpYear();
        $request->cvv = $requestDataProvider->getSecurityCode();
        $request->billingAddress = [
            'street' => $quote->getBillingAddress()->getStreetLine(1),
            'number' => $quote->getBillingAddress()->getStreetLine(2) . ' ' . $quote->getBillingAddress()->getStreetLine(3),
            'zip_code' => trim(str_replace('-','',$quote->getBillingAddress()->getPostCode())),
            'neighborhood' => $quote->getBillingAddress()->getStreetLine(4),
            'city' => $quote->getBillingAddress()->getCity(),
            'state' => $quote->getBillingAddress()->getRegionCode(),
            'country' => $quote->getBillingAddress()->getCountryId()
        ];
        $request->options = [
            'verify_card' => true
        ];

        $request->metadata = [
            'module_name' => self::MODULE_NAME,
            'module_version' => $this->getModuleHelper()->getVersion(self::MODULE_NAME),
        ];

        $result = $this->createCard($customer, $request);
        $this->setCard($quote, $customer, $requestDataProvider, $result);
        

        return $this;
    }

    protected function setCard($quote, $customer, $requestDataProvider, $result)
    {
        try {
            $cards = $this->getCardsFactory();
            $cards->setCustomerId($quote->getCustomerId());
            $cards->setCardToken($result->id);
            $cards->setCardId($customer->id);
            $cards->setLastFourNumbers(substr($requestDataProvider->getBilletCreditCardNumber(), -4));
            $cards->setBrand($requestDataProvider->getCreditCardBrand());
            $cards->setCreatedAt(date("Y-m-d H:i:s"));
            $cards->setUpdatedAt(date("Y-m-d H:i:s"));
            $cards->save();
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException($ex->getMessage());
        }

        return $this;
    }

    protected function createCard($customer, $request)
    {
        $id = $customer->id;
        try {
            $result = $this->getApi()->getCustomers()->createCard($customer->id, $request);
        } catch (\MundiAPILib\Exceptions\ErrorException $error) {
            throw new \InvalidArgumentException($error);
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException($ex->getMessage());
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getCardsFactory()
    {
        return $this->cardsFactory->create();
    }

    /**
     * @param mixed $cardsFactory
     *
     * @return self
     */
    public function setCardsFactory($cardsFactory)
    {
        $this->cardsFactory = $cardsFactory;

        return $this;
    }

    /**
     * @return Bank
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param Bank $bank
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
    }



    /**
     * @return mixed
     */
    public function getConfigBillet()
    {
        return $this->configBillet;
    }

    /**
     * @param mixed $configBillet
     */
    public function setConfigBillet($configBillet)
    {
        $this->configBillet = $configBillet;
    }

}
