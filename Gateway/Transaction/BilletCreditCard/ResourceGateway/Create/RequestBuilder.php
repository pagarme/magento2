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
use MundiPagg\MundiPagg\Helper\Cards\CreateCard;
use MundiPagg\MundiPagg\Model\Source\Bank;
use MundiPagg\MundiPagg\Gateway\Transaction\Billet\ResourceGateway\Create\RequestDataProvider as BilletDataProvider;
use MundiPagg\MundiPagg\Gateway\Transaction\Billet\Config\Config as ConfigBillet;
use MundiPagg\MundiPagg\Helper\Logger;
use MundiPagg\MundiPagg\Model\Payment;
use MundiPagg\MundiPagg\Helper\CustomerCustomAttributesHelper;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;

class RequestBuilder implements BuilderInterface
{

    const MODULE_NAME = 'MundiPagg_MundiPagg';
    const NAME_METADATA = 'Magento 2';
    const SHIPPING = 1;
    const BILLING = 0;
    
    protected $request;
    protected $requestDataProviderFactory;
    protected $cartItemRequestDataProviderFactory;
    protected $orderAdapter;
    protected $paymentData;
    protected $cart;
    protected $config;
    protected $configBilletCreditCard;
    protected $moduleHelper;
    protected $createCrad;
    protected $bank;
    protected $configBillet;
    protected $configCreditCard;
    protected $customerCustomAttributesHelper;
    protected $customerSession;
    protected $payment;
    protected $addressRepositoryInterface;

    /**
     * @var \MundiPagg\MundiPagg\Helper\Logger
     */
    protected $logger;

    /**
     * RequestBuilder constructor.
     * @param Request $request
     * @param BilletCreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory
     * @param CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory
     * @param Cart $cart
     * @param Config $config
     * @param ConfigBilletCreditCard $configBilletCreditCard
     * @param ModuleHelper $moduleHelper
     * @param CreateCard $createCrad
     * @param Bank $bank
     * @param ConfigBillet $configBillet
     * @param ConfigCreditCard $configCreditCard
     * @param Logger $logger
     * @param Payment $payment
     * @param CustomerCustomAttributesHelper $customerCustomAttributesHelper
     */
    public function __construct(
        Request $request,
        BilletCreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory,
        CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory,
        Cart $cart,
        Config $config,
        ConfigBilletCreditCard $configBilletCreditCard,
        ModuleHelper $moduleHelper,
        CreateCard $createCrad,
        Bank $bank,
        ConfigBillet $configBillet,
        ConfigCreditCard $configCreditCard,
        Logger $logger,
        CustomerCustomAttributesHelper $customerCustomAttributesHelper,
        Session $customerSession,
        Payment $payment,
        AddressRepositoryInterface $addressRepositoryInterface
    )
    {
        $this->setRequest($request);
        $this->setRequestDataProviderFactory($requestDataProviderFactory);
        $this->setCartItemRequestProviderFactory($cartItemRequestDataProviderFactory);
        $this->setCart($cart);
        $this->setConfig($config);
        $this->setConfigBilletCreditCard($configBilletCreditCard);
        $this->setModuleHelper($moduleHelper);
        $this->setCreateCardHelper($createCrad);
        $this->setBank($bank);
        $this->setConfigBillet($configBillet);
        $this->setConfigCreditCard($configCreditCard);
        $this->setLogger($logger);
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

        if($this->getConfigBilletCreditCard()->getPaymentAction() == 'authorize_capture'){
            $capture = true;
        }else{
            $capture = false;
        }
        
        $billetAmount = $quote->getPayment()->getCcBilletAmount() * 100;

        if ($payment->getAdditionalInformation('cc_saved_card')) {

            $model = $this->getCreateCardHelper();
            $card = $model->getById($payment->getAdditionalInformation('cc_saved_card'));


            $order->payments = [
                [
                    'payment_method' => 'credit_card',
                    'amount' => $requestDataProvider->getAmountInCents() - $billetAmount,
                    'credit_card' => [
                        'recurrence' => false,
                        'installments' => $requestDataProvider->getInstallmentCount(),
                        'statement_descriptor' => $statement,
                        'capture' => $capture,
                        'card_id' => $card->getCardToken(),
                        'card' => [
                            'billing_address' => [
                                'street' => $requestDataProvider->getCustomerAddressStreet(self::BILLING),
                                'number' => $requestDataProvider->getCustomerAddressNumber(self::BILLING),
                                'complement' => $requestDataProvider->getCustomerAddressComplement(self::BILLING),
                                'zip_code' => trim(str_replace('-','',$quote->getBillingAddress()->getPostCode())),
                                'neighborhood' => $requestDataProvider->getCustomerAddressDistrict(self::BILLING),
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

            $this->payment->addCustomerOnPaymentMethodWithSavedCard($order, $card, 1);

            $this->setMultiBuyerToPaymentBilletAndCreditCard($payment, $order);

        }else{
            $tokenCard = $requestDataProvider->getCcTokenCreditCard();

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
                                'street' => $requestDataProvider->getCustomerAddressStreet(self::BILLING),
                                'number' => $requestDataProvider->getCustomerAddressNumber(self::BILLING),
                                'complement' => $requestDataProvider->getCustomerAddressComplement(self::BILLING),
                                'zip_code' => trim(str_replace('-','',$quote->getBillingAddress()->getPostCode())),
                                'neighborhood' => $requestDataProvider->getCustomerAddressDistrict(self::BILLING),
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

            $this->setMultiBuyerToPaymentBilletAndCreditCard($payment, $order);

        }

        $quote->reserveOrderId()->save();
        $order->code = $this->paymentData->getOrder()->getIncrementId();

        $document = $quote->getCustomerTaxvat() ? $quote->getCustomerTaxvat() : '';
        $this->getModuleHelper()->setTaxVat($document,true);

        $order->customer = [
            'name' => !empty($requestDataProvider->getName()) ? $requestDataProvider->getName() :  $quote->getBillingAddress()->getFirstName() . ' ' . $quote->getBillingAddress()->getLastName(),
            'email' => !empty($requestDataProvider->getEmail()) ? $requestDataProvider->getEmail() : $quote->getBillingAddress()->getEmail(),
            'document' => $this->getModuleHelper()->getTaxVat(),
            'type' => 'individual',
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

        $order->antifraudEnabled = false;

        if ($this->getConfigBilletCreditCard()->getAntifraudActive() && $quote->getGrandTotal() > $this->getConfigBilletCreditCard()->getAntifraudMinAmount()) {
            $order->antifraudEnabled = true;
        }

        try {
            $this->logger->logger($order->jsonSerialize());
            $response = $this->getApi()->getOrders()->createOrder($order);

            if($response->charges[0]->status == 'failed'){

                $messageError =  __('Your transaction was processed with failure');
                throw new \InvalidArgumentException($messageError);

            }

            if($response->charges[1]->status == 'failed'){

                $messageError =  __('Your transaction was processed with failure');
                throw new \InvalidArgumentException($messageError);

            }


            if($requestDataProvider->getSaveCard() == '1')
            {
                $this->getCreateCardHelper()->createCard($response->charges[0]->lastTransaction->card, $response->charges[0]->customer, $quote);
            }

            $this->customerCustomAttributesHelper->setCustomerCustomAttribute($quote->getCustomer(),$response, $quote->getCustomerIsGuest());

        } catch (\MundiAPILib\Exceptions\ErrorException $error) {
            $this->logger->logger($error);
            throw new \InvalidArgumentException($error);
        } catch (\Exception $ex) {
            $this->logger->logger($ex);
            $acquirerMessage = $ex->getMessage();

            if(empty($acquirerMessage)){
                throw new \InvalidArgumentException($ex->getMessage());
            }

            throw new \Magento\Framework\Exception\LocalizedException(
                __($acquirerMessage)
            );
        }

        return $response;

    }

    /**
     * @return mixed
     */
    public function getCreateCardHelper()
    {
        return $this->createCrad;
    }

    /**
     * @param mixed $createCrad
     *
     * @return self
     */
    public function setCreateCardHelper($createCrad)
    {
        $this->createCrad = $createCrad;

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

    /**
     * @return \MundiPagg\MundiPagg\Helper\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \MundiPagg\MundiPagg\Helper\Logger $logger
     *
     * @return self
     */
    public function setLogger(\MundiPagg\MundiPagg\Helper\Logger $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param $payment
     * @param $order
     */
    protected function setMultiBuyerToPaymentBilletAndCreditCard($payment, $order)
    {

        if($payment->getAdditionalInformation('cc_buyer_checkbox')){

            $dataCustomerCreditCard = array(
                'name' => $payment->getAdditionalInformation('cc_buyer_name'),
                'email' => $payment->getAdditionalInformation('cc_buyer_email'),
                'document' => $payment->getAdditionalInformation('cc_buyer_document') ?? null
            );

            $dataAddressCreditCard = array(
                'street' => $payment->getAdditionalInformation('cc_buyer_street_title'),
                'number' => $payment->getAdditionalInformation('cc_buyer_street_number'),
                'complement' => $payment->getAdditionalInformation('cc_buyer_street_complement'),
                'zip_code' => $payment->getAdditionalInformation('cc_buyer_zipcode'),
                'neighborhood' => $payment->getAdditionalInformation('cc_buyer_neighborhood'),
                'city' => $payment->getAdditionalInformation('cc_buyer_city'),
                'state' => $payment->getAdditionalInformation('cc_buyer_state')
            );

            $this->payment->addCustomersOnMultiPager($order, $dataCustomerCreditCard, $dataAddressCreditCard, 1);

        }

        if($payment->getAdditionalInformation('billet_buyer_checkbox')) {

            $dataCustomerBillet = array(
                'name' => $payment->getAdditionalInformation('billet_buyer_name'),
                'email' => $payment->getAdditionalInformation('billet_buyer_email'),
                'document' => $payment->getAdditionalInformation('billet_buyer_document') ?? null
            );

            $dataAddressBillet = array(
                'street' => $payment->getAdditionalInformation('billet_buyer_street_title'),
                'number' => $payment->getAdditionalInformation('billet_buyer_street_number'),
                'complement' => $payment->getAdditionalInformation('billet_buyer_street_complement'),
                'zip_code' => $payment->getAdditionalInformation('billet_buyer_zipcode'),
                'neighborhood' => $payment->getAdditionalInformation('billet_buyer_neighborhood'),
                'city' => $payment->getAdditionalInformation('billet_buyer_city'),
                'state' => $payment->getAdditionalInformation('billet_buyer_state')
            );

            $this->payment->addCustomersOnMultiPager($order, $dataCustomerBillet, $dataAddressBillet, 2);

        }

    }

}
