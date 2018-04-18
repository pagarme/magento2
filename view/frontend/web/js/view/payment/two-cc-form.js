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
        'use strict';

        return Component.extend({
            defaults: {
                creditCardTypeFirst: '',
                creditCardExpYearFirst: '',
                creditCardExpMonthFirst: '',
                creditCardsavecardFirst: 0,
                creditCardNumberFirst: '',
                creditCardSsStartMonthFirst: '',
                creditCardSsStartYearFirst: '',
                creditCardSsIssueFirst: '',
                creditSavedCardFirst: window.checkoutConfig.payment.mundipagg_two_creditcard.selected_card,
                creditCardVerificationNumberFirst: '',
                selectedCardTypeFirst: null,
                creditCardTypeSecond: '',
                creditCardExpYearSecond: '',
                creditCardExpMonthSecond: '',
                creditCardsavecardSecond: 0,
                creditCardNumberSecond: '',
                creditCardSsStartMonthSecond: '',
                creditCardSsStartYearSecond: '',
                creditCardSsIssueSecond: '',
                creditSavedCardSecond: window.checkoutConfig.payment.mundipagg_two_creditcard.selected_card,
                creditCardVerificationNumberSecond: '',
                selectedCardTypeSecond: null
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardTypeFirst',
                        'creditCardExpYearFirst',
                        'creditCardExpMonthFirst',
                        'creditCardNumberFirst',
                        'creditCardVerificationNumberFirst',
                        'creditCardSsStartMonthFirst',
                        'creditCardSsStartYearFirst',
                        'creditCardSsIssueFirst',
                        'creditSavedCardFirst',
                        'creditCardsavecardFirst',
                        'selectedCardTypeFirst',
                        'creditCardTypeSecond',
                        'creditCardExpYearSecond',
                        'creditCardExpMonthSecond',
                        'creditCardNumberSecond',
                        'creditCardVerificationNumberSecond',
                        'creditCardSsStartMonthSecond',
                        'creditCardSsStartYearSecond',
                        'creditCardSsIssueSecond',
                        'creditSavedCardSecond',
                        'creditCardsavecardSecond',
                        'selectedCardTypeSecond'
                    ]);

                return this;
            },

            /**
             * Init component
             */
            initialize: function () {

                window.checkoutConfig.payment.mundipagg_two_creditcard.brandFirstCardIsValid = false;
                window.checkoutConfig.payment.mundipagg_two_creditcard.brandSecondCardIsValid = false;

                var self = this;

                this._super();

                // FIRST CREDIT CARD - Set credit card number to credit card data object
                this.creditCardNumberFirst.subscribe(function (value) {

                    window.checkoutConfig.payment.mundipagg_two_creditcard.brandFirstCardIsValid = false;

                    var result;

                    self.selectedCardTypeFirst(null);

                    if (value === '' || value === null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }

                    if (result.card !== null) {
                        self.selectedCardTypeFirst(result.card.type);
                        creditCardData.creditCardFirst = result.card;
                    }

                    var cardsAvailables = window.checkoutConfig.payment.ccform.availableTypes.mundipagg_creditcard;

                    if(cardsAvailables[result.card.type]){
                        window.checkoutConfig.payment.mundipagg_two_creditcard.brandFirstCardIsValid = true;
                        return false;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumberFirst = value;
                        self.creditCardTypeFirst(result.card.type);
                    }
                });

                // SECOND CREDIT CARD - Set credit card number to credit card data object
                this.creditCardNumberSecond.subscribe(function (value) {

                    window.checkoutConfig.payment.mundipagg_two_creditcard.brandSecondCardIsValid = false;

                    var result;

                    self.selectedCardTypeSecond(null);

                    if (value === '' || value === null) {
                        return false;
                    }
                    result = cardNumberValidator(value);

                    if (!result.isPotentiallyValid && !result.isValid) {
                        return false;
                    }

                    if (result.card !== null) {
                        self.selectedCardTypeSecond(result.card.type);
                        creditCardData.creditCardSecond = result.card;
                    }

                    var cardsAvailables = window.checkoutConfig.payment.ccform.availableTypes.mundipagg_creditcard;

                    if(cardsAvailables[result.card.type]){
                        window.checkoutConfig.payment.mundipagg_two_creditcard.brandSecondCardIsValid = true;
                        return false;
                    }

                    if (result.isValid) {
                        creditCardData.creditCardNumberSecond = value;
                        self.creditCardTypeSecond(result.card.type);
                    }
                });

                // FIRST CREDIT CARD

                //Set expiration year to credit card data object
                this.creditCardExpYearFirst.subscribe(function (value) {
                    creditCardData.expirationYear = value;
                });

                //Set expiration month to credit card data object
                this.creditCardExpMonthFirst.subscribe(function (value) {
                    creditCardData.expirationMonthFirst = value;
                });

                //Set cvv code to credit card data object
                this.creditCardVerificationNumberFirst.subscribe(function (value) {
                    creditCardData.cvvCode = value;
                });

                // SECOND CREDIT CARD

                //Set expiration year to credit card data object
                this.creditCardExpYearFirst.subscribe(function (value) {
                    creditCardData.expirationYear = value;
                });

                //Set expiration month to credit card data object
                this.creditCardExpMonthFirst.subscribe(function (value) {
                    creditCardData.expirationMonth = value;
                });

                //Set cvv code to credit card data object
                this.creditCardVerificationNumberFirst.subscribe(function (value) {
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
                console.log('two-cc-form');
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_cid_first': this.creditCardVerificationNumberFirst(),
                        'cc_ss_start_month_first': this.creditCardSsStartMonthFirst(),
                        'cc_ss_start_year_first': this.creditCardSsStartYearFirst(),
                        'cc_ss_issue_first': this.creditCardSsIssueFirst(),
                        'cc_type_first': this.creditCardTypeFirst(),
                        'cc_savecard_first': this.creditCardsavecardFirst() ? 1 : 0,
                        'cc_exp_year_first': this.creditCardExpYearFirst(),
                        'cc_exp_month_first': this.creditCardExpMonthFirst(),
                        'cc_saved_card_first': this.creditSavedCardFirst(),
                        'cc_number_first': this.creditCardNumberFirst(),
                        'cc_cid_second': this.creditCardVerificationNumberSecond(),
                        'cc_ss_start_month_second': this.creditCardSsStartMonthSecond(),
                        'cc_ss_start_year_second': this.creditCardSsStartYearSecond(),
                        'cc_ss_issue_second': this.creditCardSsIssueSecond(),
                        'cc_type_second': this.creditCardTypeSecond(),
                        'cc_savecard_second': this.creditCardsavecardSecond() ? 1 : 0,
                        'cc_exp_year_second': this.creditCardExpYearSecond(),
                        'cc_exp_month_second': this.creditCardExpMonthSecond(),
                        'cc_saved_card_second': this.creditSavedCardSecond(),
                        'cc_number_second': this.creditCardNumberSecond()
                    }
                };
            },

            isSaveCardActive: function() {
                return (window.isCustomerLoggedIn && window.checkoutConfig.payment.mundipagg_two_creditcard.is_saved_card);
            },

            getSaveCardHelpHtml: function () {
                return '<span>' + $t('Save cards for future purchases') + '</span>';
            },

            isSaveCardHave: function() {
                return window.checkoutConfig.payment.mundipagg_two_creditcard.is_saved_card;
            },

            isSaveCardStyle: function() {
                if (window.checkoutConfig.payment.mundipagg_two_creditcard.selected_card) {
                    return 'display: none;';
                }

                return 'display: block;';
            },

            installmentsStyle: function() {
                return 'display: none;';
            },

            getCardsCustomer:function () {
                return _.map(window.checkoutConfig.payment.mundipagg_two_creditcard.cards, function (value, key) {
                    return {
                        'key': value.id,
                        'value': value.id,
                        'text': 'xxxx.xxxx.xxxx.' + value.last_four_numbers
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
