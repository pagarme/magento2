
/*browser:true*/
/*global define*/
define(
    [
        "MundiPagg_MundiPagg/js/view/payment/default",
        "MundiPagg_MundiPagg/js/core/checkout/PaymentModuleBootstrap",
        "MundiPagg_MundiPagg/js/core/models/CreditCardModel",
        "underscore",
        'mage/translate',
        'MundiPagg_MundiPagg/js/action/installments',
        'MundiPagg_MundiPagg/js/action/installmentsByBrand',
        'Magento_Checkout/js/model/full-screen-loader',
        'ko',
        'jquery'
    ],
    function(
        Component,
        MundipaggCore,
        CreditCardModel,
        _,
        $t,
        installments,
        installmentsByBrand,
        fullScreenLoader,
        ko,
        $
    ) {
        return Component.extend({

            defaults: {
                template: "MundiPagg_MundiPagg/payment/default",
                allInstallments: ko.observableArray([]),
                creditCardType: '',
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'creditCardSsIssue',
                        'creditSavedCard',
                        'creditCardsavecard',
                        'selectedCardType'
                    ]);

                return this;
            },

            getInstallmentsByBrand: function (brand, success) {
                if (brand == "") {
                    return;
                }

                var formObject = FormObject.creditCardInit()

                $.when(
                    installmentsByBrand(brand)
                ).done(
                    success.bind(null, formObject.creditCardInstallments)
                )
            },

            getCode: function() {
                return "mundipagg_creditcard";
            },

            getModel: function() {
                return 'creditcard';
            },

            isActive: function() {
                return window.checkoutConfig.payment.mundipagg_creditcard.active;
            },

            getTitle: function() {
                return window.checkoutConfig.payment.mundipagg_creditcard.title;
            },

            getBase: function () {
                return "MundiPagg_MundiPagg/payment/creditcard";
            },

            getForm: function () {
                return "MundiPagg_MundiPagg/payment/creditcard-form";
            },

            getData: function () {
                var formObject = FormObject.creditCardInit();

                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_type': formObject.creditCardBrand.val(),
                        'cc_last_4': '1111',
                        'cc_exp_year': formObject.creditCardExpYear.val(),
                        'cc_exp_month': formObject.creditExpMonth.val(),
                        'cc_owner': formObject.creditCardHolderName.val(),
                        'cc_savecard': 0,
                        'cc_saved_card': 0,
                        'cc_installments': formObject.creditCardInstallments.val(),
                        'cc_token_credit_card': formObject.creditCardToken.val(),
                    }
                };
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
                var types = window.checkoutConfig.payment.ccform.availableTypes[this.getCode()];
                return _.map(types, function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    };
                });
            },

            /**
             * Get payment icons
             * @param {String} type
             * @returns {Boolean}
             */
            getIcons: function (type) {
                return window.checkoutConfig.payment.ccform.icons.hasOwnProperty(type) ?
                    window.checkoutConfig.payment.ccform.icons[type]
                    : false;
            },
            getAmountText: function () {
                return 'Amount for this card'
            }

        });
    }
);