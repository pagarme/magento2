/**
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com  Copyright
 *
 * @link        http://www.mundipagg.com
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'MundiPagg_MundiPagg/js/action/creditcard/token',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/translate'
    ],
    function (
        Component,
        ko,
        $,
        quote,
        priceUtils,
        totals,
        checkoutData,
        selectPaymentMethodAction,
        fullScreenLoader,
        additionalValidators,
        token,
        redirectOnSuccessAction,
        $t
    ) {
        return Component.extend({
            defaults: {
                template: 'MundiPagg_MundiPagg/payment/creditcard'

            },
            initialize: function () {
                this._super();

            },
            /**
             * Place order.
             */
            beforeplaceOrder: function (data, event) {
                this.placeOrder(data, event);
            },

            createAndSendTokenCreditCard: function (data, event) {

                /*var dataJson = {
                    "type": "card",
                    "card": {
                        "type": "credit",
                        "number": this.creditCardNumber(),
                        "holder_name": this.creditCardOwner(),
                        "exp_month": this.creditCardExpMonth(),
                        "exp_year": this.creditCardExpYear(),
                        "cvv": this.creditCardVerificationNumber(),
                        "billing_address": {
                            "street": address.street[0],
                            "number": address.street[1],
                            "zip_code": address.postcode,
                            "neighborhood": address.street[2],
                            "complement": address.street[3],
                            "city": address.region,
                            "state": address.regionCode,
                            "country": address.countryId
                        }
                    }*/


            },

            /**
             * Select current payment token
             */
            selectPaymentMethod: function () {

                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);

                return true;
            },

            getCode: function () {
                return 'mundipagg_creditcard';
            },
            isActive: function () {
                return window.checkoutConfig.payment.mundipagg_creditcard.active;
            },
            getTitle: function () {
                return window.checkoutConfig.payment.mundipagg_creditcard.title;
            },


            updateTotalWithTax: function (newTax) {
                //Interest
                /*if (typeof this.oldInstallmentTax == 'undefined') {
                    this.oldInstallmentTax = 0;
                }
                // console.log(newTax);
                var total = quote.getTotals()();
                var subTotalIndex = null;
                for (var i = 0, len = total.total_segments.length; i < len; i++) {
                    if (total.total_segments[i].code == "grand_total") {
                        subTotalIndex = i;
                        continue;
                    }
                    if (total.total_segments[i].code != "tax")
                        continue;
                    total.total_segments[i].value = newTax;
                }
                total.total_segments[subTotalIndex].value = +total.total_segments[subTotalIndex].value - this.oldInstallmentTax;
                total.total_segments[subTotalIndex].value = +total.total_segments[subTotalIndex].value + parseFloat(newTax);
                total.tax_amount = parseFloat(newTax);
                total.base_tax_amount = parseFloat(newTax);
                this.oldInstallmentTax = newTax;
                window.checkoutConfig.payment.ccform.installments.value = newTax;
                quote.setTotals(total);*/
            },

        })
    }
);