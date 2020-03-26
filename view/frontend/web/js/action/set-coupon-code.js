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
    'MundiPagg_MundiPagg/js/view/payment/creditcard',
    'MundiPagg_MundiPagg/js/view/payment/boletocreditcard',
    'MundiPagg_MundiPagg/js/view/payment/twocreditcards',
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

    var dataModifiers = [],
        successCallbacks = [],
        failCallbacks = [],
        action;

    action = function (couponCode, isApplied) {
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

                setTimeout(() => {
                    new creditCard();
                    new billetCard();
                    new twoCards();
                }, 800);

            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            errorProcessor.process(response, messageContainer);
        });
    };

    /**
     * Modifying data to be sent.
     *
     * @param {Function} modifier
     */
    action.registerDataModifier = function (modifier) {
        dataModifiers.push(modifier);
    };

    /**
     * When successfully added a coupon.
     *
     * @param {Function} callback
     */
    action.registerSuccessCallback = function (callback) {
        successCallbacks.push(callback);
    };

    /**
     * When failed to add a coupon.
     *
     * @param {Function} callback
     */
    action.registerFailCallback = function (callback) {
        failCallbacks.push(callback);
    };

    return action;
});
