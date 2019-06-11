
/*browser:true*/
/*global define*/
define(
    [
        "MundiPagg_MundiPagg/js/view/payment/default"
    ],
    function(Component, $t) {
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

            /*getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_type': 'visa',
                        'cc_last_4': '1111',
                        'cc_exp_year': 19,
                        'cc_exp_month': 22,
                        'cc_owner': 'Holder name',
                        'cc_savecard': 0,
                        'cc_saved_card': 0,
                        'cc_installments': 1,
                        'cc_token_credit_card': "token_n2pmbDMfJ0SEgWrw",
                    }
                };
            },*/

        });
    }
);