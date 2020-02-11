/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'MundiPagg_MundiPagg/js/view/payment/creditcard',
    'MundiPagg_MundiPagg/js/view/payment/boletocreditcard',
    'MundiPagg_MundiPagg/js/view/payment/twocreditcards'
], function (
    $,
    quote,
    urlManager,
    errorProcessor,
    messageContainer,
    storage,
    getPaymentInformationAction,
    totals,
    $t,
    fullScreenLoader,
    creditCard,
    billetCard,
    twoCards
) {
    'use strict';

    return function (isApplied) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.getCancelCouponUrl(quoteId),
            message = $t('Your coupon was successfully removed.');

        messageContainer.clear();
        fullScreenLoader.startLoader();

        return storage.delete(
            url,
            false
        ).done(function () {
            var deferred = $.Deferred();

            totals.isLoading(true);
            getPaymentInformationAction(deferred);
            $.when(deferred).done(function () {
                isApplied(false);
                totals.isLoading(false);
                fullScreenLoader.stopLoader();
            });
            messageContainer.addSuccessMessage({
                'message': message
            });

            var wasValidCreditCard = window.checkoutConfig.payment.mundipagg_creditcard.brandIsValid;
            var wasValidBilletCreditCard = window.checkoutConfig.payment.mundipagg_billet_creditcard.brandIsValid;
            var wasValidTwoCreditCardFirst = window.checkoutConfig.payment.mundipagg_two_creditcard.brandFirstCardIsValid;
            var wasValidTwoCreditCardSecond = window.checkoutConfig.payment.mundipagg_two_creditcard.brandSecondCardIsValid;

            setTimeout(() => {
                new creditCard();
                new billetCard();
                new twoCards();
            }, 800);

        }).fail(function (response) {
            totals.isLoading(false);
            fullScreenLoader.stopLoader();
            errorProcessor.process(response, messageContainer);
        });
    };
});
