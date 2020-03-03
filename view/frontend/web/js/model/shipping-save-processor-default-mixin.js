define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-billing-address',
    'mage/utils/wrapper',
    'MundiPagg_MundiPagg/js/view/payment/creditcard',
    'MundiPagg_MundiPagg/js/view/payment/boletocreditcard',
    'MundiPagg_MundiPagg/js/view/payment/twocreditcards'
], function (
    ko,
    quote,
    resourceUrlManager,
    storage,
    paymentService,
    methodConverter,
    errorProcessor,
    fullScreenLoader,
    selectBillingAddressAction,
    wrapper,
    creditCard,
    billetCard,
    twoCards
) {
    'use strict';

    return function (defaultJS) {

        defaultJS.saveShippingInformation = wrapper.wrapSuper(defaultJS.saveShippingInformation, function (hash) {

            var observerMutation = new MutationObserver((mutationsList, observer) => {
                new creditCard();
                new billetCard();
                new twoCards();

                observer.disconnect();
            });

            observerMutation.observe(
                document.getElementById('maincontent'),
                {
                    attributes: true,
                    childList: true,
                    subtree: true
                }
            );

            return this._super(hash);
        });

        return defaultJS;
    };
});