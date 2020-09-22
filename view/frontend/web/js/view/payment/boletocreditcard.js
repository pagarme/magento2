
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
                var paymentMethod = window.MundiPaggCore.paymentMethod[this.getModel()];
                if (paymentMethod == undefined) {
                    return paymentMethod;
                }
                var paymentModel = paymentMethod.model;
                return paymentModel.getData();
            },

            getText: function () {
                return window.checkoutConfig.payment.mundipagg_billet.text;
            },

            /**
             * Get list of available month values
             * @returns {Object}
             */
            getMonthsValues: function () {
                var months = window.checkoutConfig.payment.ccform.months[this.getCode()];
                return _.map(months, function (value, key) {
                    return {
                        'value': key,
                        'month': value
                    };
                });
            },

            /**
             * Get list of available year values
             * @returns {Object}
             */
            getYearsValues: function () {
                var year = window.checkoutConfig.payment.ccform.years[this.getCode()];
                return _.map(year, function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    };
                });
            },

            /**
             * Get image for CVV
             * @returns {String}
             */
            getCvvImageHtml: function () {
                var cvvImgUrl = window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()];

                return '<img src="' + cvvImgUrl +
                    '" alt="' + $t('Card Verification Number Visual Reference') +
                    '" title="' + $t('Card Verification Number Visual Reference') +
                    '" />';
            },

            /**
             * Get list of available credit card types values
             * @returns {Object}
             */
            getAvailableTypesValues: function () {
                /*var types = window.checkoutConfig.payment.ccform.availableTypes[this.getCode()];
                return _.map(types, function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    };
                });*/
            },

            /**
             * Get payment icons
             * @param {String} type
             * @returns {Boolean}
             */
            getIcons: function (type) {
                /*return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type) ?
                    window.checkoutConfig.payment.ccform.icons[type]
                    : false;*/
            },
            getAmountText: function () {
                return 'Amount for this card'
            },
        });
    }
);