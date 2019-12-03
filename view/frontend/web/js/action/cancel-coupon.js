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
    'MundiPagg_MundiPagg/js/view/payment/method-renderer/creditcard',
    'MundiPagg_MundiPagg/js/view/payment/method-renderer/billet_creditcard',
    'MundiPagg_MundiPagg/js/view/payment/method-renderer/two_creditcard'
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

            var creditCardObject = new creditCard();
            creditCardObject.onInstallmentItemChange();
            creditCardObject.getCcInstallments();

            var billetCardObject = new billetCard();
            billetCardObject.bindCreditCardBilletAmountBcc();

            var twoCardsObject = new twoCards();
            twoCardsObject.bindFirstCreditCardAmount();
            twoCardsObject.bindSecondCreditCardAmount();

        }).fail(function (response) {
            totals.isLoading(false);
            fullScreenLoader.stopLoader();
            errorProcessor.process(response, messageContainer);
        });
    };
});
