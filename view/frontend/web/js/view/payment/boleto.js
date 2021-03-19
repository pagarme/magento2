/*browser:true*/
/*global define*/
define(
    [
        "Pagarme_Pagarme/js/view/payment/default",
        "Pagarme_Pagarme/js/core/models/BoletoModel"
    ],
    function (Component, $t) {

        return Component.extend({
            defaults: {
                template: "Pagarme_Pagarme/payment/default"
            },
            getCode: function() {
                return "pagarme_billet";
            },
            isActive: function() {
                return window.checkoutConfig.payment.pagarme_billet.active;
            },
            getTitle: function() {
                return window.checkoutConfig.payment.pagarme_billet.title;
            },
            getBase: function() {
                return "Pagarme_Pagarme/payment/boleto";
            },
            getForm: function() {
                return "Pagarme_Pagarme/payment/boleto-form";
            },
            getMultibuyerForm: function () {
                return "Pagarme_Pagarme/payment/multibuyer-form";
            },
            getText: function () {
                return window.checkoutConfig.payment.pagarme_billet.text;
            },
            getModel: function() {
                return 'boleto';
            },

            getData: function () {
                var paymentMethod = window.PagarmeCore.paymentMethod[this.getModel()];
                if (paymentMethod == undefined) {
                    return paymentMethod;
                }
                var paymentModel = paymentMethod.model;
                return paymentModel.getData();
            },
        });
    }
);
