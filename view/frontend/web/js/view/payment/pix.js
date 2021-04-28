/*browser:true*/
/*global define*/
define(
    [
        "Pagarme_Pagarme/js/view/payment/default",
        "Pagarme_Pagarme/js/core/models/PixModel"
    ],
    function (Component, $t) {

        return Component.extend({
            defaults: {
                template: "Pagarme_Pagarme/payment/default"
            },
            getCode: function () {
                return "pagarme_pix";
            },
            isActive: function () {
                return window.checkoutConfig.payment.pagarme_pix.active;
            },
            getTitle: function () {
                return window.checkoutConfig.payment.pagarme_pix.title;
            },
            getBase: function () {
                return "Pagarme_Pagarme/payment/pix";
            },
            getForm: function () {
                return "Pagarme_Pagarme/payment/pix-form";
            },
            getMultibuyerForm: function () {
                return "Pagarme_Pagarme/payment/multibuyer-form";
            },
            getText: function () {
                return window.checkoutConfig.payment.pagarme_pix.text;
            },

            getModel: function () {
                return 'pix';
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
