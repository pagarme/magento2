/**
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */
/*browser:true*/
/*global define*/
define(
    [
        "Magento_Checkout/js/view/payment/default",
        "ko",
        "jquery",
        'Pagarme_Pagarme/js/action/installmentsByBrand',
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
        "Pagarme_Pagarme/js/core/checkout/PaymentModuleBootstrap",
        "Pagarme_Pagarme/js/core/checkout/PaymentMethodController",
        "Pagarme_Pagarme/js/core/checkout/PlatformPlaceOrder",
        "Pagarme_Pagarme/js/core/checkout/Bin",
        "Pagarme_Pagarme/js/core/checkout/PlatformFormBiding",
        "Pagarme_Pagarme/js/core/checkout/PlatformFormHandler",
        "Pagarme_Pagarme/js/core/checkout/CreditCardToken",
        "Pagarme_Pagarme/js/core/checkout/Installments",
        "Pagarme_Pagarme/js/core/validators/CreditCardValidator",
        "Pagarme_Pagarme/js/core/validators/CustomerValidator",
        "Pagarme_Pagarme/js/core/validators/MultibuyerValidator",
        "Pagarme_Pagarme/js/core/validators/VoucherCardValidator",
    ],
    function(
        Component,
        ko,
        $,
        installmentsAction,
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
        PagarmeCore,
        PaymentController,
        PlatformPlaceOrder
    ) {

        window.PagarmeCore.messageList = globalMessageList;
        return Component.extend({
            initPaymentMethod: function() {
                var _self = this;

                platFormConfig = window.checkoutConfig;
                platFormConfig.moduleUrls = {};
                installmentsUrl = installmentsAction();
                platFormConfig.grand_total = quote.getTotals()().grand_total;

                var baseUrl = platFormConfig.payment.ccform.base_url;

                if (
                    quote.billingAddress() &&
                    typeof quote.billingAddress() != "undefined" &&
                    quote.billingAddress().vatId == ""
                ) {
                    quote.billingAddress().vatId = platFormConfig.customerData.taxvat
                }

                platFormConfig.base_url = baseUrl;
                platFormConfig.moduleUrls.installments =
                    baseUrl + installmentsUrl;

                platFormConfig.addresses = {
                    billingAddress: quote.billingAddress()
                };

                platFormConfig.loader = fullScreenLoader;

                /** @fixme Update total should be moved to platformFormBinging **/
                platFormConfig.updateTotals = quote;

                window.PagarmeCore.platFormConfig = platFormConfig;
                window.PagarmeCore.initPaymentMethod(
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

                window.PagarmeCore.platFormConfig.addresses.billingAddress = quote.billingAddress();

                var PlatformPlaceOrder = {
                    obj : _self,
                    data: data,
                    event: event
                };

                window.PagarmeCore.placeOrder(
                    PlatformPlaceOrder,
                    this.getModel()
                );
            },

            /**
             * Select current payment token
             */
            selectPaymentMethod: function() {
                var data = this.getData();
                if (data == undefined) {
                    var platFormConfig = window.PagarmeCore.platFormConfig;
                    window.PagarmeCore.init(this.getModel(), platFormConfig);
                }
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
