
/*browser:true*/
/*global define*/
define(
    [
        "MundiPagg_MundiPagg/js/view/payment/default",
        "MundiPagg_MundiPagg/js/core/checkout/PaymentModuleBootstrap",
        "MundiPagg_MundiPagg/js/core/models/BoletoCreditcardModel",
        "underscore",
        'mage/translate',
        'MundiPagg_MundiPagg/js/action/installments',
        'MundiPagg_MundiPagg/js/action/installmentsByBrand',
        'Magento_Checkout/js/model/full-screen-loader',
        'ko',
        'jquery',
    ],
    function(
        Component,
        MundipaggCore,
        BoletoCreditcardModel,
        _,
        $t,
        installments,
        installmentsByBrand,
        fullScreenLoader,
        ko,
        $,
    ) {
        return Component.extend({

            defaults: {
                template: "MundiPagg_MundiPagg/payment/default",
                allInstallments: ko.observableArray([]),
                creditCardType: '',
            },

            getCode: function() {
                return "mundipagg_billet_creditcard";
            },

            getModel: function() {
                return 'boletoCreditcard';
            },

            isActive: function() {
                return window.checkoutConfig.payment.mundipagg_billet_creditcard.active;
            },

            getTitle: function() {
                return window.checkoutConfig.payment.mundipagg_billet_creditcard.title;
            },

            getBase: function () {
                return "MundiPagg_MundiPagg/payment/boletocreditcard";
            },

            getForm: function () {
                return "MundiPagg_MundiPagg/payment/boleto-form";
            },
            getFormCreditcard: function () {
                return "MundiPagg_MundiPagg/payment/creditcard-form";
            },

            getMultibuyerForm: function () {
                return "MundiPagg_MundiPagg/payment/multibuyer-form";
            },

            getData: function () {
                var paymentModel = window.MundiPaggCore.paymentMethod[this.getModel()].model;
                return paymentModel.getData();
            },

            getText: function () {
                return window.checkoutConfig.payment.mundipagg_billet.text;
            },
        });
    }
);