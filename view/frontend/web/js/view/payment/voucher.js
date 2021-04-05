
/*browser:true*/
/*global define*/
define(
    [
        "Pagarme_Pagarme/js/view/payment/default",
        "Pagarme_Pagarme/js/core/checkout/PaymentModuleBootstrap",
        "Pagarme_Pagarme/js/core/models/VoucherModel",
        "underscore",
        'mage/translate',
        'Pagarme_Pagarme/js/action/installments',
        'Pagarme_Pagarme/js/action/installmentsByBrand',
        'Magento_Checkout/js/model/full-screen-loader',
        'ko',
        'jquery',
    ],
    function(
        Component,
        PagarmeCore,
        VoucherModel,
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
                template: "Pagarme_Pagarme/payment/default",
                allInstallments: ko.observableArray([]),
                creditCardType: '',
            },

            getCode: function() {
                return "pagarme_voucher";
            },

            getModel: function() {
                return 'voucher';
            },

            isActive: function() {
                return window.checkoutConfig.payment.pagarme_voucher.active;
            },

            getTitle: function() {
                return window.checkoutConfig.payment.pagarme_voucher.title;
            },

            getBase: function () {
                return "Pagarme_Pagarme/payment/voucher";
            },

            getForm: function () {
                return "Pagarme_Pagarme/payment/creditcard-form";
            },

            getMultibuyerForm: function () {
                return "Pagarme_Pagarme/payment/multibuyer-form";
            },

            getData: function () {
                var paymentMethod = window.PagarmeCore.paymentMethod[this.getModel()];
                if (paymentMethod == undefined) {
                    return paymentMethod;
                }
                var paymentModel = paymentMethod.model;
                return paymentModel.getData();
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
            }
        });
    }
);
