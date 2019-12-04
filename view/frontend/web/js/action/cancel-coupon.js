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

            var wasValidCreditCard = window.checkoutConfig.payment.mundipagg_creditcard.brandIsValid;
            var wasValidBilletCreditCard = window.checkoutConfig.payment.mundipagg_billet_creditcard.brandIsValid;
            var wasValidTwoCreditCardFirst = window.checkoutConfig.payment.mundipagg_two_creditcard.brandFirstCardIsValid;
            var wasValidTwoCreditCardSecond = window.checkoutConfig.payment.mundipagg_two_creditcard.brandSecondCardIsValid;

            var creditCardObject = new creditCard();
            creditCardObject.onInstallmentItemChange();
            creditCardObject.getCcInstallments();

            document.getElementsByName('payment[cc_billet_amount]')[0].value = '';
            document.getElementsByName('payment[cc_cc_amount]')[0].value = '';

            var billetCardObject = new billetCard();
            billetCardObject.bindCreditCardBilletAmountBcc();

            document.getElementsByName('payment[first-card-amount]')[0].value = '';
            document.getElementsByName('payment[second-card-amount]')[0].value = '';

            var twoCardsObject = new twoCards();
            twoCardsObject.bindFirstCreditCardAmount();
            twoCardsObject.bindSecondCreditCardAmount();
            twoCardsObject.bindInstallmentsByBlurFirst();
            twoCardsObject.bindInstallmentsByBlurSecond();

            if (wasValidCreditCard) {
                window.checkoutConfig.payment.mundipagg_creditcard.brandIsValid = true;
            }

            if (wasValidBilletCreditCard) {
                window.checkoutConfig.payment.mundipagg_billet_creditcard.brandIsValid = true;
            }

            if (wasValidTwoCreditCardFirst) {
                window.checkoutConfig.payment.mundipagg_two_creditcard.brandFirstCardIsValid = true;
            }

            if (wasValidTwoCreditCardSecond) {
                window.checkoutConfig.payment.mundipagg_two_creditcard.brandSecondCardIsValid = true;
            }

        }).fail(function (response) {
            totals.isLoading(false);
            fullScreenLoader.stopLoader();
            errorProcessor.process(response, messageContainer);
        });
    };
});
