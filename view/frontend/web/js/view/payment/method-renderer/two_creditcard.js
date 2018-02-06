/**
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */
/*browser:true*/
/*global define*/
define(
    [
        'MundiPagg_MundiPagg/js/view/payment/two-cc-form',
        'ko',
        'MundiPagg_MundiPagg/js/action/installments',
        'MundiPagg_MundiPagg/js/action/installmentsByBrand',
        'MundiPagg_MundiPagg/js/action/installmentsByBrandAndAmount',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        Component,
        ko,
        installments,
        installmentsByBrand,
        installmentsByBrandAndAmount,
        $,
        quote,
        priceUtils,
        totals,
        checkoutData, 
        selectPaymentMethodAction, 
        fullScreenLoader
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MundiPagg_MundiPagg/payment/two-creditcard',
                firstCreditCardAmount: '',
                firstCreditCardTaxAmount: '',
                creditCardSavedNumberFirst: '',
                creditCardTypeFirst: '',
                creditCardInstallmentsFirst: '',
                creditCardOwnerFirst: '',
                creditCardExpYearFirst: '',
                creditCardExpMonthFirst: '',
                creditCardsavecardFirst: 0,
                creditCardNumberFirst: '',
                creditCardSsStartMonthFirst: '',
                creditCardSsStartYearFirst: '',
                creditCardSsIssueFirst: '',
                creditCardVerificationNumbTerFirst: '',
                creditSavedCardFirst: window.checkoutConfig.payment.mundipagg_two_creditcard.selected_card,
                selectedCardTypeFirst: null,
                allInstallmentsFirst: ko.observableArray([]),
                secondCreditCardAmount: '',
                secondCreditCardTaxAmount: '',
                creditCardSavedNumberSecond: '',
                creditCardTypeSecond: '',
                creditCardInstallmentsSecond: '',
                creditCardOwnerSecond: '',
                creditCardExpYearSecond: '',
                creditCardExpMonthSecond: '',
                creditCardsavecardSecond: 0,
                creditCardNumberSecond: '',
                creditCardSsStartMonthSecond: '',
                creditCardSsStartYearSecond: '',
                creditCardSsIssueSecond: '',
                creditCardVerificationNumberSecond: '',
                creditSavedCardSecond: window.checkoutConfig.payment.mundipagg_two_creditcard.selected_card,
                selectedCardTypeSecond: null,
                allInstallmentsSecond: ko.observableArray([])
            },

            totals: quote.getTotals(),

            initialize: function () {
                this._super();

                this.getCcInstallmentsFirst();
                this.getCcInstallmentsSecond();

                var self = this;

                this.updateInstallmentsFirstCard = function(newValue){

                    self.creditCardTypeFirst(newValue);

                    var amountFirst = self.firstCreditCardAmount() != '' ? self.firstCreditCardAmount() : 0;
                    //fullScreenLoader.startLoader();

                    $.when(
                        installmentsByBrandAndAmount(newValue, amountFirst)
                    ).done(function (data) {
                        self.allInstallmentsFirst.removeAll();

                        _.map(data, function (value, key) {
                            self.allInstallmentsFirst.push({
                                'value': value.id,
                                'interest': value.interest,
                                'installments': value.label
                            });
                            // self.creditCardInstallmentsFirst(data.length);
                        });

                    }).always(function () {
                        //fullScreenLoader.stopLoader();
                    });


                }

                this.updateInstallmentsSecondCard = function(newValue){

                    self.creditCardTypeSecond(newValue);

                    var amountSecond = self.secondCreditCardAmount() != '' ? self.secondCreditCardAmount() : 0;
                    //fullScreenLoader.startLoader();

                    $.when(
                        installmentsByBrandAndAmount(newValue, amountSecond)
                    ).done(function (data) {
                        self.allInstallmentsSecond.removeAll();

                        _.map(data, function (value, key) {
                            self.allInstallmentsSecond.push({
                                'value': value.id,
                                'interest': value.interest,
                                'installments': value.label
                            });
                            // self.creditCardInstallmentsSecond(data.length);
                        });

                    }).always(function () {
                        //fullScreenLoader.stopLoader();
                    });

                }

                this.selectedCardTypeFirst.subscribe(function(newValue){
                    if(newValue){
                       self.updateInstallmentsFirstCard(newValue);
                    }
                });

                this.selectedCardTypeSecond.subscribe(function(newValue){
                    if(newValue){
                        self.updateInstallmentsSecondCard(newValue);
                    }
                });

                this.creditSavedCardFirst.subscribe(function(value){
                    if (typeof value != 'undefined') {
                        var cards = window.checkoutConfig.payment.mundipagg_two_creditcard.cards;
                        for (var i = 0, len = cards.length; i < len; i++) {
                            if(cards[i].id == value){
                                self.creditCardSavedNumberFirst(window.checkoutConfig.payment.mundipagg_two_creditcard.cards[i].last_four_numbers);
                                self.selectedCardTypeFirst(window.checkoutConfig.payment.mundipagg_two_creditcard.cards[i].brand);
                            }
                        }
                    }
                });

                this.creditSavedCardSecond.subscribe(function(value){
                    if (typeof value != 'undefined') {
                        var cards = window.checkoutConfig.payment.mundipagg_two_creditcard.cards;
                        for (var i = 0, len = cards.length; i < len; i++) {
                            if(cards[i].id == value){
                                self.creditCardSavedNumberSecond(window.checkoutConfig.payment.mundipagg_two_creditcard.cards[i].last_four_numbers);
                                self.selectedCardTypeSecond(window.checkoutConfig.payment.mundipagg_two_creditcard.cards[i].brand);
                            }
                        }
                    }
                });

                var self = this;

                var totalQuote = quote.getTotals()().grand_total;

                $('.money').mask('000.000.000.000.000,00', {reverse: true});

                this.formatPrice = function (value) {

                    var input = document.createElement("input");
                    input.type = "hidden";
                    document.body.appendChild(input);
                    input.value = value;
                    $(input).mask('000.000.000.000.000,00', {reverse: true});
                    value = input.value;
                    document.body.removeChild(input);
                    return value;

                }

                this.validateTotalQuote = function () {

                    if (Math.abs(parseFloat(self.firstCreditCardAmount())) + Math.abs(parseFloat(self.secondCreditCardAmount())) > totalQuote) {
                        self.bindFirstCreditCardAmount(null);
                        self.bindSecondCreditCardAmount(null);
                        jQuery('#mundipagg_two_creditcard_cc_installments_second').css('display','none');
                        jQuery('#mundipagg_two_creditcard_cc_installments_first').css('display','none');
                    }

                }

                this.bindFirstCreditCardAmount = ko.computed({
                    read: function () {
                        var value = this.firstCreditCardAmount();
                        value = parseFloat(value.replace(/[^\d]/g, ""));
                        return this.formatPrice(value);
                    },
                    write: function (value) {
                        if (value != 'null') {
                            value = this.formatPrice(value);
                            value = value.replace(/[^,\d]/g, "");
                            value = value.replace(",", ".");
                            this.firstCreditCardAmount(value);
                            this.secondCreditCardAmount((totalQuote - parseFloat(value)).toFixed(2));
                            jQuery('#mundipagg_two_creditcard_cc_installments_second').css('display','block');
                            jQuery('#mundipagg_two_creditcard_cc_installments_first').css('display','block');
                            this.validateTotalQuote();
                        }

                    },
                    owner: self
                });

                this.bindSecondCreditCardAmount = ko.computed({
                    read: function () {
                        var value = this.secondCreditCardAmount();
                        value = parseFloat(value.replace(/[^\d]/g, ""));
                        return this.formatPrice(value);
                    },
                    write: function (value) {
                        if (value != 'null') {
                            value = this.formatPrice(value);
                            value = value.replace(/[^,\d]/g, "");
                            value = value.replace(",", ".");
                            this.secondCreditCardAmount(value);
                            this.firstCreditCardAmount((totalQuote - parseFloat(value)).toFixed(2));
                            jQuery('#mundipagg_two_creditcard_cc_installments_second').css('display','block');
                            jQuery('#mundipagg_two_creditcard_cc_installments_first').css('display','block');
                            this.validateTotalQuote();
                        }
                    },
                    owner: self
                });

                this.bindInstallmentsByBlurFirst = function (){
                    var cards = window.checkoutConfig.payment.mundipagg_two_creditcard.cards;
                    cards.find(function(value, index) {
                        if(value.id == self.creditSavedCardFirst()){
                            self.selectedCardTypeFirst(value.brand);
                        }
                        if(value.id == self.creditSavedCardSecond()){
                            self.selectedCardTypeSecond(value.brand);
                        }
                    });
                    this.getInstallmentsByApiForTwoCards(self.selectedCardTypeFirst(), self.selectedCardTypeSecond(), this.firstCreditCardAmount(), this.secondCreditCardAmount());
                };

                this.bindInstallmentsByBlurSecond = function (){
                    var cards = window.checkoutConfig.payment.mundipagg_two_creditcard.cards;
                    cards.find(function(value, index) {
                        if(value.id == self.creditSavedCardSecond()){
                            self.selectedCardTypeSecond(value.brand);
                        }
                        if(value.id == self.creditSavedCardFirst()){
                            self.selectedCardTypeFirst(value.brand);
                        }
                    });
                    this.getInstallmentsByApiForTwoCards(self.selectedCardTypeFirst(), self.selectedCardTypeSecond(), this.firstCreditCardAmount(), this.secondCreditCardAmount());
                };

                this.getInstallmentsByApiForTwoCards = function (brandFirst, brandSecond, firstCreditCardAmount, secondCreditCardAmount) {

                    if (!isNaN(firstCreditCardAmount) && secondCreditCardAmount != '') {

                        self.creditCardTypeFirst(brandFirst);
                        firstCreditCardAmount = firstCreditCardAmount != '' ? firstCreditCardAmount : 0;

                        $.when(

                            installmentsByBrandAndAmount(brandFirst, firstCreditCardAmount)
                        ).done(function (data) {
                            self.allInstallmentsFirst.removeAll();

                            _.map(data, function (value, key) {
                                self.allInstallmentsFirst.push({
                                    'value': value.id,
                                    'interest': value.interest,
                                    'installments': value.label
                                });
                            });
                            // self.creditCardInstallmentsFirst(data.length);

                        }).always(function () {
                            // fullScreenLoader.stopLoader();
                        });

                        self.creditCardTypeSecond(brandSecond);
                        secondCreditCardAmount = secondCreditCardAmount != '' ? secondCreditCardAmount : 0;

                        $.when(

                            installmentsByBrandAndAmount(brandSecond, secondCreditCardAmount)
                        ).done(function (data) {
                            self.allInstallmentsSecond.removeAll();

                            _.map(data, function (value, key) {
                                self.allInstallmentsSecond.push({
                                    'value': value.id,
                                    'interest': value.interest,
                                    'installments': value.label
                                });
                                // self.creditCardInstallmentsSecond(data.length);
                            });

                        }).always(function () {
                            // fullScreenLoader.stopLoader();
                        });

                    }

                };

            },

            /**
             * Select current payment token
             */
            selectPaymentMethod: function () {
                this.oldInstallmentTax = 0;
                var newTax = 0;

                var total = quote.getTotals()();
                var subTotalIndex = null;
                for (var i = 0, len = total.total_segments.length; i < len; i++) {
                    if (total.total_segments[i].code == "grand_total") {
                        subTotalIndex = i;
                        continue;
                    }
                    if (total.total_segments[i].code != "tax") continue;
                    total.total_segments[i].value = newTax;
                }

                total.total_segments[subTotalIndex].value = +total.total_segments[subTotalIndex].value - this.oldInstallmentTax;
                total.total_segments[subTotalIndex].value = +total.total_segments[subTotalIndex].value + parseFloat(newTax);
                total.tax_amount = parseFloat(newTax);
                total.base_tax_amount = parseFloat(newTax);
                this.oldInstallmentTax = newTax;
                quote.setTotals(total);

                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                $("#mundipagg_two_creditcard_installments_second").val('');
                $("#mundipagg_two_creditcard_installments_first").val('');

                return true;
            },

            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'po_number': null,
                    'additional_data': null
                };
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'firstCreditCardAmount',
                        'firstCreditCardTaxAmount',
                        'creditCardTypeFirst',
                        'creditCardSavedNumberFirst',
                        'creditCardOwnerFirst',
                        'creditCardExpYearFirst',
                        'creditCardExpMonthFirst',
                        'creditCardNumberFirst',
                        'creditCardVerificationNumberFirst',
                        'creditCardSsStartMonthFirst',
                        'creditCardSsStartYearFirst',
                        'creditCardsavecardFirst',
                        'creditCardSsIssueFirst',
                        'creditSavedCardFirst',
                        'selectedCardTypeFirst',
                        'creditCardInstallmentsFirst',
                        'secondCreditCardAmount',
                        'secondCreditCardTaxAmount',
                        'creditCardSavedNumberSecond',
                        'creditCardTypeSecond',
                        'creditCardOwnerSecond',
                        'creditCardExpYearSecond',
                        'creditCardExpMonthSecond',
                        'creditCardNumberSecond',
                        'creditCardVerificationNumberSecond',
                        'creditCardSsStartMonthSecond',
                        'creditCardSsStartYearSecond',
                        'creditCardsavecardSecond',
                        'creditCardSsIssueSecond',
                        'creditSavedCardSecond',
                        'selectedCardTypeSecond',
                        'creditCardInstallmentsSecond'
                    ]);

                return this;
            },

            getCode: function () {
                return 'mundipagg_two_creditcard';
            },

            isActive: function () {
                return window.checkoutConfig.payment.mundipagg_two_creditcard.active;
            },

            isInstallmentsActive: function () {
                return window.checkoutConfig.payment.ccform.installments.active['mundipagg_creditcard'];
            },
            getCcInstallmentsFirst: function() {
                var self = this;

                //fullScreenLoader.startLoader();
                $.when(
                    installments()
                ).done(function (transport) {
                    self.allInstallmentsFirst.removeAll();

                    _.map(transport, function (value, key) {
                        self.allInstallmentsFirst.push({
                            'value': value.id,
                            'interest': value.interest,
                            'installments': value.label
                        });
                    });
                    // self.creditCardInstallmentsFirst(transport.length);

                }).always(function () {
                    //fullScreenLoader.stopLoader();
                });
            },

            getCcInstallmentsSecond: function() {
                var self = this;

                //fullScreenLoader.startLoader();
                $.when(
                    installments()
                ).done(function (transport) {
                    self.allInstallmentsSecond.removeAll();

                    _.map(transport, function (value, key) {
                        self.allInstallmentsSecond.push({
                            'value': value.id,
                            'interest': value.interest,
                            'installments': value.label
                        });
                    });
                    self.creditCardInstallmentsSecond(transport.length);

                }).always(function () {
                    //fullScreenLoader.stopLoader();
                });
            },

            setInterest: function (option, item) {
                if (typeof item != 'undefined') {
                    ko.applyBindingsToNode(option, {
                        attr: {
                            interest: item.interest
                        }
                    }, item);
                }

            },

            getCcInstallmentsValues: function() {
                return _.map(this.getCcInstallments(), function (value, key) {
                    return {
                        'value': key,
                        'installments': value
                    };
                });
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_first_card_amount': this.firstCreditCardAmount(),
                        'cc_first_card_tax_amount': this.firstCreditCardTaxAmount(),
                        'cc_last_4_first': this.creditCardSavedNumberFirst(),
                        'cc_cid_first': this.creditCardVerificationNumberFirst(),
                        'cc_type_first': this.creditCardTypeFirst(),
                        'cc_exp_year_first': this.creditCardExpYearFirst(),
                        'cc_exp_month_first': this.creditCardExpMonthFirst(),
                        'cc_number_first': this.creditCardNumberFirst(),
                        'cc_owner_first': this.creditCardOwnerFirst(),
                        'cc_savecard_first': this.creditCardsavecardFirst() ? 1 : 0,
                        'cc_saved_card_first': this.creditSavedCardFirst(),
                        'cc_installments_first': this.creditCardInstallmentsFirst(),
                        'cc_second_card_amount': this.secondCreditCardAmount(),
                        'cc_second_card_tax_amount': this.secondCreditCardTaxAmount(),
                        'cc_last_4_second': this.creditCardSavedNumberSecond(),
                        'cc_cid_second': this.creditCardVerificationNumberSecond(),
                        'cc_type_second': this.creditCardTypeSecond(),
                        'cc_exp_year_second': this.creditCardExpYearSecond(),
                        'cc_exp_month_second': this.creditCardExpMonthSecond(),
                        'cc_number_second': this.creditCardNumberSecond(),
                        'cc_owner_second': this.creditCardOwnerSecond(),
                        'cc_savecard_second': this.creditCardsavecardSecond() ? 1 : 0,
                        'cc_saved_card_second': this.creditSavedCardSecond(),
                        'cc_installments_second': this.creditCardInstallmentsSecond()
                    }
                };
            },

            onInstallmentItemChange: function() {
                this.updateTotalWithTax(jQuery('#mundipagg_two_creditcard_installments_first option:selected').attr('interest'), jQuery('#mundipagg_two_creditcard_installments_second option:selected').attr('interest'));
            },

            updateTotalWithTax: function(newTaxFirst, newTaxSecond) {
                if (typeof this.oldFirstInstallmentTax == 'undefined') {
                    this.oldFirstInstallmentTax = 0;
                }
                if (typeof newTaxFirst == 'undefined') {
                    newTaxFirst = 0;
                }
                if (typeof this.oldSecondInstallmentTax == 'undefined') {
                    this.oldSecondInstallmentTax = 0;
                }
                if (typeof newTaxSecond == 'undefined') {
                    newTaxSecond = 0;
                }

                this.firstCreditCardTaxAmount(newTaxFirst);
                this.secondCreditCardTaxAmount(newTaxSecond);

                var sumTax = parseFloat(newTaxFirst) + parseFloat(newTaxSecond);
                var sumOldTax = parseFloat(this.oldFirstInstallmentTax) + parseFloat(this.oldSecondInstallmentTax);

                var total = quote.getTotals()();
                var subTotalIndex = null;
                for(var i = 0, len = total.total_segments.length; i < len; i++) {
                    if(total.total_segments[i].code == "grand_total") {
                        subTotalIndex = i;
                        continue;
                    }
                    if(total.total_segments[i].code != "tax") continue;
                    total.total_segments[i].value = sumTax;
                }


                total.total_segments[subTotalIndex].value = +total.total_segments[subTotalIndex].value - sumOldTax;
                total.total_segments[subTotalIndex].value = +total.total_segments[subTotalIndex].value + sumTax;
                total.tax_amount = parseFloat(sumTax);
                total.base_tax_amount = parseFloat(sumTax);
                this.oldFirstInstallmentTax = newTaxFirst;
                this.oldSecondInstallmentTax = newTaxSecond;
                quote.setTotals(total);
            },

            onSavedCardChange: function(idValue) {
                if (jQuery('#mundipagg_two_creditcard_card_' + idValue).val()) {
                    jQuery('#mundipagg_two_creditcard_cc_icons_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_savecard_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_number_div_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_owner_div_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_type_exp_div_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_type_cvv_div_' + idValue).css('display','none');
                }else{
                    jQuery('#mundipagg_two_creditcard_cc_icons_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_savecard_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_number_div_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_owner_div_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_type_exp_div_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_type_cvv_div_' + idValue).css('display','block');
                }
            },
        })
    }
);
