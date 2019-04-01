/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'MundiPagg_MundiPagg/js/model/credit-card-validation/credit-card-number-validator',
        'mage/translate'
    ],
    function (_, Component, creditCardData, cardNumberValidator, $t) {

        return Component.extend({
            defaults: {
                creditCardTypeBcc: '',
                creditCardExpYearBcc: '',
                creditCardExpMonthBcc: '',
                creditCardsavecardBcc: 0,
                creditCardNumberBcc: '',
                creditCardSsStartMonthBcc: '',
                creditCardSsStartYearBcc: '',
                creditCardSsIssueBcc: '',
                creditSavedCardBcc: window.checkoutConfig.payment.mundipagg_billet_creditcard.selected_card,
                creditCardVerificationNumberBcc: '',
                selectedCardTypeBcc: null,
                haveBccCard: null
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardTypeBcc',
                        'creditCardExpYearBcc',
                        'creditCardExpMonthBcc',
                        'creditCardNumberBcc',
                        'creditCardVerificationNumberBcc',
                        'creditCardSsStartMonthBcc',
                        'creditCardSsStartYearBcc',
                        'creditCardsavecardBcc',
                        'creditCardSsIssueBcc',
                        'creditSavedCardBcc',
                        'selectedCardTypeBcc',
                        'haveBccCard'
                    ]);

                return this;
            },

            /**
             * Init component
             */
            initialize: function () {

                window.checkoutConfig.payment.mundipagg_billet_creditcard.brandIsValid = false;

                var self = this;

                this._super();

                //Set credit card number to credit card data object
                this.creditCardNumberBcc.subscribe(function (value) {

                    window.checkoutConfig.payment.mundipagg_billet_creditcard.brandIsValid = false;

                    var result;

                    self.selectedCardTypeBcc(null);

                    if (value === '' || value === null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }

                    if (result.card !== null) {
                        self.selectedCardTypeBcc(result.card.type);
                        creditCardData.creditCard = result.card;
                    }


                    if (result.isValid) {
                        creditCardData.creditCardNumber = value;
                        self.creditCardTypeBcc(result.card.type);
                    }

                    var cardsAvailables = window.checkoutConfig.payment.ccform.availableTypes.mundipagg_creditcard;

                    if(cardsAvailables[result.card.type]){
                        window.checkoutConfig.payment.mundipagg_billet_creditcard.brandIsValid = true;
                    }
                });

                //Set expiration year to credit card data object
                this.creditCardExpYearBcc.subscribe(function (value) {
                    creditCardData.expirationYear = value;
                });

                //Set expiration month to credit card data object
                this.creditCardExpMonthBcc.subscribe(function (value) {
                    creditCardData.expirationMonth = value;
                });

                //Set cvv code to credit card data object
                this.creditCardVerificationNumberBcc.subscribe(function (value) {
                    creditCardData.cvvCode = value;
                });
            },

            /**
             * Get code
             * @returns {String}
             */
            getCode: function () {
                return 'cc';
            },

            /**
             * Get data
             * @returns {Object}
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumberBcc(),
                        'cc_ss_start_month': this.creditCardSsStartMonthBcc(),
                        'cc_ss_start_year': this.creditCardSsStartYearBcc(),
                        'cc_ss_issue': this.creditCardSsIssueBcc(),
                        'cc_type': this.creditCardTypeBcc(),
                        'cc_savecard': this.creditCardsavecardBcc() ? 1 : 0,
                        'cc_exp_year': this.creditCardExpYearBcc(),
                        'cc_exp_month': this.creditCardExpMonthBcc(),
                        'cc_saved_card': this.creditSavedCardBcc(),
                        'cc_number': this.creditCardNumberBcc()
                    }
                };
            },

            isEnabledSavedCards: function() {
                return (window.checkoutConfig.payment.mundipagg_creditcard.enabled_saved_cards);
            },

            isSaveCardActive: function() {
                return (window.isCustomerLoggedIn);
            },

            getSaveCardHelpHtml: function () {
                return '<span>' + $t('Save cards for future purchases') + '</span>';
            },

            isSaveCardHave: function() {
                this.haveBccCard = window.checkoutConfig.payment.mundipagg_billet_creditcard.is_saved_card;
                return this;
            },

            isSaveCardStyle: function() {
                if (window.checkoutConfig.payment.mundipagg_billet_creditcard.selected_card) {
                    return 'display: none;';
                }

                return 'display: block;';
            },

            installmentsStyle: function() {
                return 'display: none;';
            },

            getCardsCustomer:function () {
                return _.map(window.checkoutConfig.payment.mundipagg_billet_creditcard.cards, function (value, key) {
                    var text = 'xxxx.xxxx.xxxx.' + value.last_four_numbers;
                    if (typeof value.first_six_digits !== "undefined") {
                        text = (value.first_six_digits / 100).toFixed(2) +
                            'xx.xxxx.' + value.last_four_numbers;
                    }

                    return {
                        'value': value.id,
                        text
                    };
                });
            },
            /**
             * Get list of available credit card types
             * @returns {Object}
             */
            getCcAvailableTypes: function () {
                return window.checkoutConfig.payment.ccform.availableTypes['mundipagg_creditcard'];
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

            /**
             * Get list of months
             * @returns {Object}
             */
            getCcMonths: function () {
                return window.checkoutConfig.payment.ccform.months[this.getCode()];
            },

            /**
             * Get list of years
             * @returns {Object}
             */
            getCcYears: function () {
                return window.checkoutConfig.payment.ccform.years[this.getCode()];
            },

            /**
             * Check if current payment has verification
             * @returns {Boolean}
             */
            hasVerification: function () {
                return window.checkoutConfig.payment.ccform.hasVerification[this.getCode()];
            },

            /**
             * @deprecated
             * @returns {Boolean}
             */
            hasSsCardType: function () {
                return window.checkoutConfig.payment.ccform.hasSsCardType[this.getCode()];
            },

            /**
             * Get image url for CVV
             * @returns {String}
             */
            getCvvImageUrl: function () {
                return window.checkoutConfig.payment.ccform.cvvImageUrl[this.getCode()];
            },

            /**
             * Get image for CVV
             * @returns {String}
             */
            getCvvImageHtml: function () {
                return '<img src="' + this.getCvvImageUrl() +
                    '" alt="' + $t('Card Verification Number Visual Reference') +
                    '" title="' + $t('Card Verification Number Visual Reference') +
                    '" />';
            },

            /**
             * @deprecated
             * @returns {Object}
             */
            getSsStartYears: function () {
                return window.checkoutConfig.payment.ccform.ssStartYears[this.getCode()];
            },

            /**
             * Get list of available credit card types values
             * @returns {Object}
             */
            getCcAvailableTypesValues: function () {
                return _.map(this.getCcAvailableTypes(), function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    };
                });
            },

            /**
             * Get list of available month values
             * @returns {Object}
             */
            getCcMonthsValues: function () {
                return _.map(this.getCcMonths(), function (value, key) {
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
            getCcYearsValues: function () {
                return _.map(this.getCcYears(), function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    };
                });
            },

            /**
             * @deprecated
             * @returns {Object}
             */
            getSsStartYearsValues: function () {
                return _.map(this.getSsStartYears(), function (value, key) {
                    return {
                        'value': key,
                        'year': value
                    };
                });
            },

            /**
             * Is legend available to display
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * Get available credit card type by code
             * @param {String} code
             * @returns {String}
             */
            getCcTypeTitleByCode: function (code) {
                var title = '',
                    keyValue = 'value',
                    keyType = 'type';

                _.each(this.getCcAvailableTypesValues(), function (value) {
                    if (value[keyValue] === code) {
                        title = value[keyType];
                    }
                });

                return title;
            },

            /**
             * Prepare credit card number to output
             * @param {String} number
             * @returns {String}
             */
            formatDisplayCcNumber: function (number) {
                return 'xxxx-' + number.substr(-4);
            },

            /**
             * Get credit card details
             * @returns {Array}
             */
            getInfo: function () {
                return [
                    {
                        'name': 'Credit Card Type', value: this.getCcTypeTitleByCode(this.creditCardType())
                    },
                    {
                        'name': 'Credit Card Number', value: this.formatDisplayCcNumber(this.creditCardNumber())
                    }
                ];
            }
        });
    }
);
