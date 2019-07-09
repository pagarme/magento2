/*browser:true*/
/*global define*/
define(
    [
        "MundiPagg_MundiPagg/js/view/payment/default",
        "MundiPagg_MundiPagg/js/core/models/BoletoModel"
    ],
    function (Component, $t) {

        return Component.extend({
            defaults: {
                template: "MundiPagg_MundiPagg/payment/default"
            },
            getCode: function() {
                return "mundipagg_billet";
            },
            isActive: function() {
                return window.checkoutConfig.payment.mundipagg_billet.active;
            },
            getTitle: function() {
                return window.checkoutConfig.payment.mundipagg_billet.title;
            },
            getBase: function() {
                return "MundiPagg_MundiPagg/payment/boleto";
            },
            getForm: function() {
                return "MundiPagg_MundiPagg/payment/boleto-form";
            },
            getText: function () {
                return window.checkoutConfig.payment.mundipagg_billet.text;
            },
            getModel: function() {
                return 'boleto';
            },
        });
    }
);