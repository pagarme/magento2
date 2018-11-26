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
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/view/payment/default'
    ],
    function (
        quote, 
        checkoutData, 
        selectPaymentMethodAction, 
        Component
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MundiPagg_MundiPagg/payment/billet'
            },
            getText: function () {
                return window.checkoutConfig.payment.mundipagg_billet.text;
            },
            /**
             * Select current payment token
             */
            selectPaymentMethod: function () {
                this.oldInstallmentTax = window.checkoutConfig.payment.ccform.installments.value;
                var newTax = 0;

                var total = quote.getTotals()();
                var subTotalIndex = null;
                for (var i = 0, len = total.total_segments.length; i < len; i++) {
                    if (total.total_segments[i].code == "grand_total") {
                        subTotalIndex = i;
                        continue;
                    }
                    if (total.total_segments[i].code != "tax") continue;
                    total.total_segments[i].value = newTax;
                }

                total.total_segments[subTotalIndex].value = +total.total_segments[subTotalIndex].value - this.oldInstallmentTax;
                total.total_segments[subTotalIndex].value = +total.total_segments[subTotalIndex].value + parseFloat(newTax);
                total.tax_amount = parseFloat(newTax);
                total.base_tax_amount = parseFloat(newTax);
                this.oldInstallmentTax = newTax;
                window.checkoutConfig.payment.ccform.installments.value = newTax;
                quote.setTotals(total);

                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);

                return true;
            },

            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'po_number': null,
                    'additional_data': null
                };
            },
            getTitle: function () {
                return window.checkoutConfig.payment.mundipagg_billet.title;
            },
        });
    }
);
