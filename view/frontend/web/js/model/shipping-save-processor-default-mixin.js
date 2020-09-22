define([
    'mage/utils/wrapper',
    'MundiPagg_MundiPagg/js/view/payment/creditcard',
    'MundiPagg_MundiPagg/js/view/payment/boletocreditcard',
    'MundiPagg_MundiPagg/js/view/payment/twocreditcards'
], function (
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