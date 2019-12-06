/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customer store credit(balance) application
 */
define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'MundiPagg_MundiPagg/js/view/payment/method-renderer/creditcard',
    'MundiPagg_MundiPagg/js/view/payment/method-renderer/billet_creditcard',
    'MundiPagg_MundiPagg/js/view/payment/method-renderer/two_creditcard',
    'MundiPagg_MundiPagg/js/model/credit-card-validation/credit-card-number-validator'
], function (
    ko,
    $,
    quote,
    urlManager,
    errorProcessor,
    messageContainer,
    storage,
    $t,
    getPaymentInformationAction,
    totals,
    fullScreenLoader,
    creditCard,
    billetCard,
    twoCards,
    cardNumberValidator
) {
    'use strict';

    return function (couponCode, isApplied) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.getApplyCouponUrl(couponCode, quoteId),
            message = $t('Your coupon was successfully applied.');

        fullScreenLoader.startLoader();

        return storage.put(
            url,
            {},
            false
        ).done(function (response) {
            var deferred;

            if (response) {
                deferred = $.Deferred();

                isApplied(true);
                totals.isLoading(true);
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
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

                document.getElementsByName('payment[cc_billet_amount]')[0].value = ''
                document.getElementsByName('payment[cc_cc_amount]')[0].value = ''

                var billetCardObject = new billetCard();
                billetCardObject.bindCreditCardBilletAmountBcc();

                document.getElementsByName('payment[first-card-amount]')[0].value = ''
                document.getElementsByName('payment[second-card-amount]')[0].value = ''

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
            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            errorProcessor.process(response, messageContainer);
        });
    };
});
