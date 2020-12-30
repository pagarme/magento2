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

namespace MundiPagg\MundiPagg\Gateway\Transaction\TwoCreditCard\ResourceGateway\Create;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Item;
use MundiPagg\MundiPagg\Api\CartItemRequestDataProviderInterface;
use MundiPagg\MundiPagg\Api\CreditCardRequestDataProviderInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use MundiAPILib\Models\CreateOrderRequest as Request;
use MundiPagg\MundiPagg\Api\CreditCardRequestDataProviderInterfaceFactory;
use MundiPagg\MundiPagg\Api\CartItemRequestDataProviderInterfaceFactory;
use Magento\Checkout\Model\Cart;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\Config;
use MundiPagg\MundiPagg\Gateway\Transaction\CreditCard\Config\Config as ConfigCreditCard;
use MundiPagg\MundiPagg\Helper\ModuleHelper;
use MundiPagg\MundiPagg\Helper\Cards\CreateCard;
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
    protected $requestDataProviderFactory;
    protected $cartItemRequestDataProviderFactory;
    protected $orderAdapter;
    protected $paymentData;
    protected $cart;
    protected $config;
    protected $configCreditCard;
    protected $moduleHelper;
    protected $createCrad;
    protected $payment;
    protected $customerCustomAttributesHelper;
    protected $customerSession;


    protected $addressRepositoryInterface;
    /**
     * RequestBuilder constructor.
     * @param Request $request
     * @param CreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory
     * @param CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory
     * @param Cart $cart
     * @param Config $config
     * @param ConfigCreditCard $configCreditCard
     * @param CreateCard $createCrad
     * @param ModuleHelper $moduleHelper
     * @param Payment $payment
     * @param CustomerCustomAttributesHelper $customerCustomAttributesHelper
     */
    public function __construct(
        Request $request,
        CreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory,
        CartItemRequestDataProviderInterfaceFactory $cartItemRequestDataProviderFactory,
        Cart $cart,
        Config $config,
        ConfigCreditCard $configCreditCard,
        CreateCard $createCrad,
        ModuleHelper $moduleHelper,
        Payment $payment,
        CustomerCustomAttributesHelper $customerCustomAttributesHelper,
        Session $customerSession,
        AddressRepositoryInterface $addressRepositoryInterface
    )
    {
        $this->setRequest($request);
        $this->setRequestDataProviderFactory($requestDataProviderFactory);
        $this->setCartItemRequestProviderFactory($cartItemRequestDataProviderFactory);
        $this->setCart($cart);
        $this->setConfig($config);
        $this->setConfigCreditCard($configCreditCard);
        $this->setCreateCardHelper($createCrad);
        $this->setModuleHelper($moduleHelper);
        $this->payment = $payment;
        $this->customerCustomAttributesHelper = $customerCustomAttributesHelper;
        $this->customerSession = $customerSession;
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
     * @return CreditCardRequestDataProviderInterface
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
     * @param CreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory
     * @return RequestBuilder
     */
    protected function setRequestDataProviderFactory(CreditCardRequestDataProviderInterfaceFactory $requestDataProviderFactory)
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

        $statement = $this->getConfigCreditCard()->getSoftDescription();

        if($this->getConfigCreditCard()->getPaymentAction() == 'authorize_capture'){
            $capture = true;
        }else{
            $capture = false;
        }

        $model = $this->getCreateCardHelper();

        $order->payments = [
            [
                'payment_method' => 'credit_card',
                'amount' => str_replace('.', '', ($payment->getCcFirstCardAmount() + $payment->getAdditionalInformation('cc_first_card_tax_amount')) * 100),
                'credit_card' => [
                    'recurrence' => false,
                    'capture' => $capture,
                    'statement_descriptor' => $statement,
                    'installments' => $payment->getAdditionalInformation('cc_installments_first'),
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
                'amount' => str_replace('.', '', ($payment->getCcSecondCardAmount() + $payment->getAdditionalInformation('cc_second_card_tax_amount')) * 100),
                'payment_method' => 'credit_card',
                'credit_card' => [
                    'recurrence' => false,
                    'capture' => $capture,
                    'statement_descriptor' => $statement,
                    'installments' => $payment->getAdditionalInformation('cc_installments_second'),
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
            ]
        ];


        $this->setMultiBuyerToPaymentTwoCreditCard($payment, $order, 'first');

        if (!empty($payment->getAdditionalInformation('cc_saved_card_first'))) {
            $cardCollection = $model->getById($payment->getAdditionalInformation('cc_saved_card_first'));
            $order->payments[0]['credit_card']['card_id'] = $cardCollection->getCardToken();
            $this->payment->addCustomerOnPaymentMethodWithSavedCard($order, $cardCollection, 1);
            $this->setMultiBuyerToPaymentTwoCreditCard($payment, $order, 'first');
        } else {
            $order->payments[0]['credit_card']['card_token'] = $requestDataProvider->getTokenCreditCardFirst();
            $this->setMultiBuyerToPaymentTwoCreditCard($payment, $order, 'first');
        }

        if (!empty($payment->getAdditionalInformation('cc_saved_card_second'))) {
            $cardCollection = $model->getById($payment->getAdditionalInformation('cc_saved_card_second'));
            $order->payments[1]['credit_card']['card_id'] = $cardCollection->getCardToken();
            $this->payment->addCustomerOnPaymentMethodWithSavedCard($order, $cardCollection, 2);
            $this->setMultiBuyerToPaymentTwoCreditCard($payment, $order, 'second');
        } else {
            $order->payments[1]['credit_card']['card_token'] = $requestDataProvider->getTokenCreditCardSecond();
            $this->setMultiBuyerToPaymentTwoCreditCard($payment, $order, 'second');
        }

        $document = $quote->getCustomerTaxvat() ? $quote->getCustomerTaxvat() : '';
        $this->getModuleHelper()->setTaxVat($document,true);

        $order->customer = [
            'name' => !empty($requestDataProvider->getName()) ? $requestDataProvider->getName() : $quote->getBillingAddress()->getFirstName() . ' ' . $quote->getBillingAddress()->getLastName(),
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

            $address = $order->payments[1]['credit_card']['card']['billing_address'];
            $order->shipping['address'] = $address;
            $order->customer['address'] = $address;
            $order->shipping['description'] = __('Product_Virtual');

        }else{
            $order->shipping['description'] = '.';
        }

        $quote->reserveOrderId()->save();
        $order->code = $this->paymentData->getOrder()->getIncrementId();

        if ($this->getConfigCreditCard()->getAntifraudActive() && $quote->getGrandTotal() > $this->getConfigCreditCard()->getAntifraudMinAmount()) {
            $order->antifraudEnabled = true;
        }

        try {

            $response = $this->getApi()->getOrders()->createOrder($order);

            if(($response->charges[0]->status == 'failed')) {

                if (!empty($response->charges[0]->lastTransaction->acquirerMessage)) {
                    $responseCancel = $this->getApi()->getCharges()->cancelCharge($response->charges[1]->id);
                    $messageError = __('Your transaction was processed with failure') . __(' first card');
                    throw new \InvalidArgumentException($messageError);


                }
            }

            if(($response->charges[1]->status == 'failed')){
                if(!empty($response->charges[1]->lastTransaction->acquirerMessage)){
                    $responseCancel = $this->getApi()->getCharges()->cancelCharge($response->charges[0]->id);
                    $messageError = __('Your transaction was processed with failure').__(' second card');
                    throw new \InvalidArgumentException($messageError);

                }

            }

            if ($payment->getAdditionalInformation('cc_savecard_first') == '1' && empty($payment->getAdditionalInformation('cc_saved_card_first'))) {
                $this->getCreateCardHelper()->createCard($response->charges[0]->lastTransaction->card, $response->charges[0]->customer, $quote);
            }

            if ($payment->getAdditionalInformation('cc_savecard_second') == '1' && empty($payment->getAdditionalInformation('cc_saved_card_second'))) {
                $this->getCreateCardHelper()->createCard($response->charges[1]->lastTransaction->card, $response->charges[1]->customer, $quote);
            }

            $this->customerCustomAttributesHelper->setCustomerCustomAttribute($quote->getCustomer(),$response, $quote->getCustomerIsGuest());

        } catch (\MundiAPILib\Exceptions\ErrorException $error) {
            throw new \InvalidArgumentException($error);
        } catch (\Exception $ex) {
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
     * @param $payment
     * @param $order
     */
    protected function setMultiBuyerToPaymentTwoCreditCard($payment, $order, $card)
    {

        if($payment->getAdditionalInformation('cc_buyer_checkbox_first')){

            $dataCustomerCreditCardFirst = array(
                'name' => $payment->getAdditionalInformation('cc_buyer_name_first'),
                'email' => $payment->getAdditionalInformation('cc_buyer_email_first'),
                'document' => $payment->getAdditionalInformation('cc_buyer_document_first') ?? null
            );

            $dataAddressCreditCardFirst = array(
                'street' => $payment->getAdditionalInformation('cc_buyer_street_title_first'),
                'number' => $payment->getAdditionalInformation('cc_buyer_street_number_first'),
                'complement' => $payment->getAdditionalInformation('cc_buyer_street_complement_first'),
                'zip_code' => $payment->getAdditionalInformation('cc_buyer_zipcode_first'),
                'neighborhood' => $payment->getAdditionalInformation('cc_buyer_neighborhood_first'),
                'city' => $payment->getAdditionalInformation('cc_buyer_city_first'),
                'state' => $payment->getAdditionalInformation('cc_buyer_state_first')
            );

            $this->payment->addCustomersOnMultiPager($order, $dataCustomerCreditCardFirst, $dataAddressCreditCardFirst, 1);
        }

        if($payment->getAdditionalInformation('cc_buyer_checkbox_second')){

            $dataCustomerCreditCardSecond = array(
                'name' => $payment->getAdditionalInformation('cc_buyer_name_second'),
                'email' => $payment->getAdditionalInformation('cc_buyer_email_second'),
                'document' => $payment->getAdditionalInformation('cc_buyer_document_second') ?? null
            );

            $dataAddressCreditCardSecond = array(
                'street' => $payment->getAdditionalInformation('cc_buyer_street_title_second'),
                'number' => $payment->getAdditionalInformation('cc_buyer_street_number_second'),
                'complement' => $payment->getAdditionalInformation('cc_buyer_street_complement_second'),
                'zip_code' => $payment->getAdditionalInformation('cc_buyer_zipcode_second'),
                'neighborhood' => $payment->getAdditionalInformation('cc_buyer_neighborhood_second'),
                'city' => $payment->getAdditionalInformation('cc_buyer_city_second'),
                'state' => $payment->getAdditionalInformation('cc_buyer_state_second')
            );

            $this->payment->addCustomersOnMultiPager($order, $dataCustomerCreditCardSecond, $dataAddressCreditCardSecond, 2);
        }
    }
}
