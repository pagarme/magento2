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
        "Magento_Checkout/js/view/payment/default",
        "ko",
        "jquery",
        "Magento_Checkout/js/model/quote",
        "Magento_Catalog/js/price-utils",
        "Magento_Checkout/js/model/totals",
        "Magento_Checkout/js/checkout-data",
        "Magento_Checkout/js/action/select-payment-method",
        "Magento_Checkout/js/model/full-screen-loader",
        "Magento_Checkout/js/model/payment/additional-validators",
        "Magento_Checkout/js/action/redirect-on-success",
        "mage/translate",
        "Magento_Ui/js/model/messageList",
        'Magento_Checkout/js/model/url-builder',
        "MundiPagg_MundiPagg/js/core/checkout/PaymentModuleBootstrap",
        "MundiPagg_MundiPagg/js/core/checkout/PaymentMethodController",
        "MundiPagg_MundiPagg/js/core/checkout/Bin",
        "MundiPagg_MundiPagg/js/core/checkout/PlatformFormBiding",
        "MundiPagg_MundiPagg/js/core/checkout/PlatformFormHandler",
        "MundiPagg_MundiPagg/js/core/checkout/CreditCardToken",
        "MundiPagg_MundiPagg/js/core/checkout/Installments",
    ],
    function(
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
        redirectOnSuccessAction,
        $t,
        globalMessageList,
        urlBuilder,
        MundiPaggCore,
        PaymentController
    ) {

        window.MundiPaggCore.quote = quote;

        return Component.extend({

            initialize: function() {
                this.initPaymentMethod();

                this._super().observe([
                    "mundipagg-content"
                ]);
            },

            initPaymentMethod: function() {
                var _self = this;
                platFormConfig = window.checkoutConfig;
                baseUrl = window.BASE_URL;
                platFormConfig.moduleUrls = {};
                installmentsUrl = '';

                platFormConfig.moduleUrls.installments = installmentsUrl;

                window.MundiPaggCore.initPaymentMethod(
                    this.getModel(),
                    platFormConfig
                );
            },

            getData: function() {
                return {
                    "method": this.item.method
                };
            },

            getKey : function() {
                return window.checkoutConfig.payment.ccform.pk_token
            },

            /**
             * Place order.
             */
            beforeplaceOrder: function(data, event){

                var _self = this;
                /*
                globalMessageList.addErrorMessage({
                    message: $t("Error message.")
                });
                $("html, body").animate({scrollTop: 0}, 600);
                return false;*/

                //@todo Validar dados inclusive de endereço pelo MundiPaggCore.quote setado acima
                /*window.MundiPaggCore.initPaymentMethod(this.getModel());
                window.MundiPaggCore.placeOrder(_self, data, event);*/
            },


            /**
             * Select current payment token
             */
            selectPaymentMethod: function() {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                return true;
            },

            updateTotalWithTax: function(newTax) {
                //Interest
                if (typeof this.oldInstallmentTax == "undefined") {
                    this.oldInstallmentTax = 0;
                }
                
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
                quote.setTotals(total);
            },
        })
    }
);