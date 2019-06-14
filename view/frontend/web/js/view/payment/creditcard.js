
/*browser:true*/
/*global define*/
define(
    [
        "MundiPagg_MundiPagg/js/view/payment/default",
        "MundiPagg_MundiPagg/js/core/checkout/PaymentModuleBootstrap"
    ],
    function(Component, MundipaggCore, $t) {
        return Component.extend({
            defaults: {
                template: "MundiPagg_MundiPagg/payment/default"
            },

            getCode: function() {
                return "mundipagg_creditcard";
            },

            isActive: function() {
                return window.checkoutConfig.payment.mundipagg_creditcard.active;
            },

            getTitle: function() {
                return window.checkoutConfig.payment.mundipagg_creditcard.title;
            },

            getForm: function () {
                return "MundiPagg_MundiPagg/payment/creditcard-form";
            },

            getData: function () {
                var formObject = FormObject.creditCardInit();


                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_type': formObject.creditCardBrand.val(),
                        'cc_last_4': '1111',
                        'cc_exp_year': formObject.creditCardExpYear.val(),
                        'cc_exp_month': formObject.creditExpMonth.val(),
                        'cc_owner': formObject.creditCardHolderName.val(),
                        'cc_savecard': 0,
                        'cc_saved_card': 0,
                        'cc_installments': formObject.creditCardInstallments.val(),
                        'cc_token_credit_card': formObject.creditCardToken.val(),
                    }
                };
            },

        });
    }
);