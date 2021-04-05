/*browser:true*/
/*global define*/
define(
    [
        "Pagarme_Pagarme/js/view/payment/default",
        "Pagarme_Pagarme/js/core/models/TwoCreditcardsModel"
    ],
    function (Component, $t) {

        return Component.extend({
            defaults: {
                template: "Pagarme_Pagarme/payment/default"
            },
            getCode: function() {
                return "pagarme_two_creditcard";
            },
            isActive: function() {
                return window.checkoutConfig.payment.pagarme_two_creditcard.active;
            },
            getTitle: function() {
                return window.checkoutConfig.payment.pagarme_two_creditcard.title;
            },
            getBase: function() {
                return "Pagarme_Pagarme/payment/twocreditcards";
            },
            getForm: function() {
                return "Pagarme_Pagarme/payment/creditcard-form";
            },
            getModel: function() {
                return 'twocreditcards';
            },
            getMonthsValues: function () {
                return '';
            },
            getYearsValues: function () {
                return '';
            },
            getCvvImageHtml: function () {

            },
            getData: function () {
                var paymentMethod = window.PagarmeCore.paymentMethod[this.getModel()];
                if (paymentMethod == undefined) {
                    return paymentMethod;
                }
                var paymentModel = paymentMethod.model;
                return paymentModel.getData();
            },
            getMultibuyerForm: function () {
                return "Pagarme_Pagarme/payment/multibuyer-form";
            }
        });
    }
);
