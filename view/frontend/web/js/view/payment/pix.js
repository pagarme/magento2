/*browser:true*/
/*global define*/
define(
    [
        "MundiPagg_MundiPagg/js/view/payment/default",
        "MundiPagg_MundiPagg/js/core/models/PixModel"
    ],
    function (Component, $t) {

        return Component.extend({
            defaults: {
                template: "MundiPagg_MundiPagg/payment/default"
            },
            getCode: function () {
                return "mundipagg_pix";
            },
            isActive: function () {
                return window.checkoutConfig.payment.mundipagg_pix.active;
            },
            getTitle: function () {
                return window.checkoutConfig.payment.mundipagg_pix.title;
            },
            getBase: function () {
                return "MundiPagg_MundiPagg/payment/pix";
            },
            getForm: function () {
                return "MundiPagg_MundiPagg/payment/pix-form";
            },
            getMultibuyerForm: function () {
                return "MundiPagg_MundiPagg/payment/multibuyer-form";
            },
            getText: function () {
                return window.checkoutConfig.payment.mundipagg_pix.text;
            },

            getModel: function () {
                return 'pix';
            },

            getData: function () {
                var paymentMethod = window.MundiPaggCore.paymentMethod[this.getModel()];
                if (paymentMethod == undefined) {
                    return paymentMethod;
                }
                var paymentModel = paymentMethod.model;
                return paymentModel.getData();
            },
        });
    }
);