
/*browser:true*/
/*global define*/
define(
    [
        'MundiPagg_MundiPagg/js/view/payment/default'
    ],
    function (Component, $t) {

        return Component.extend({
            defaults: {
                template: 'MundiPagg_MundiPagg/payment/default'
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
            getForm: function () {
                return 'MundiPagg_MundiPagg/payment/creditcard-form'
            },

        });
    }
);