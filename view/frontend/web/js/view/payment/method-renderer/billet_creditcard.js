/**
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com  Copyright
 *
 * @link        http://www.mundipagg.com
 */
/*browser:true*/
/*global define*/
define(
    [
        'MundiPagg_MundiPagg/js/view/payment/bcc-form',
        'ko',
        'MundiPagg_MundiPagg/js/action/installments',
        'MundiPagg_MundiPagg/js/action/installmentsByBrand',
        'MundiPagg_MundiPagg/js/action/installmentsByBrandAndAmount',
        'jquery',
        'jquerymask',
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
        jquerymask,
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
                template: 'MundiPagg_MundiPagg/payment/billet-creditcard',
                creditCardTypeBcc: '',
                creditCardSavedNumberBcc: '',
                creditCardCcAmountBcc: '',
                creditCardCcTaxAmountBcc: '',
                creditCardBilletAmountBcc: '',
                creditCardInstallmentsBcc: '',
                creditCardOwnerBcc: '',
                creditCardExpYearBcc: '',
                creditCardExpMonthBcc: '',
                creditCardsavecardBcc: 0,
                creditCardNumberBcc: '',
                creditCardSsStartMonthBcc: '',
                creditCardSsStartYearBcc: '',
                creditCardSsIssueBcc: '',
                creditCardVerificationNumberBcc: '',
                creditSavedCardBcc: window.checkoutConfig.payment.mundipagg_billet_creditcard.selected_card,
                selectedCardTypeBcc: null,
                allInstallments: ko.observableArray([])
            },

            totals: quote.getTotals(),

            initialize: function () {
                this._super();

                this.getCcInstallments();

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

                    if (Math.abs(parseFloat(self.creditCardBilletAmountBcc())) + Math.abs(parseFloat(self.creditCardCcAmountBcc())) > totalQuote) {
                        self.bindCreditCardBilletAmountBcc(null);
                        self.bindCreditCardCcAmountBcc(null);
                        jQuery('#mundipagg_billet_creditcard_cc_installments').css('display','none');
                    }

                }

                this.bindCreditCardBilletAmountBcc = ko.computed({
                    read: function () {
                        var value = this.creditCardBilletAmountBcc();
                        value = parseFloat(value.replace(/[^\d]/g, ""));
                        return this.formatPrice(value);
                    },
                    write: function (value) {
                        if (value != 'null') {
                            value = this.formatPrice(value);
                            value = value.replace(/[^,\d]/g, "");
                            value = value.replace(",", ".");
                            this.creditCardBilletAmountBcc(value);
                            this.creditCardCcAmountBcc((totalQuote - parseFloat(value)).toFixed(2));
                            jQuery('#mundipagg_billet_creditcard_cc_installments').css('display','block');
                            this.validateTotalQuote();
                        }

                    },
                    owner: self
                });

                this.bindCreditCardCcAmountBcc = ko.computed({
                    read: function () {
                        var value = this.creditCardCcAmountBcc();
                        value = parseFloat(value.replace(/[^\d]/g, ""));
                        return this.formatPrice(value);
                    },
                    write: function (value) {
                        if (value != 'null') {
                            value = this.formatPrice(value);
                            value = value.replace(/[^,\d]/g, "");
                            value = value.replace(",", ".");
                            this.creditCardCcAmountBcc(value);
                            this.creditCardBilletAmountBcc((totalQuote - parseFloat(value)).toFixed(2));
                            jQuery('#mundipagg_billet_creditcard_cc_installments').css('display','block');
                            this.validateTotalQuote();
                        }

                    },
                    owner: self
                });

                this.bindInstallmentsByBlurBcc = function (){

                    var cards = window.checkoutConfig.payment.mundipagg_billet_creditcard.cards;
                    cards.find(function(value, index) {
                        if(value.id == self.creditSavedCardBcc()){
                            self.selectedCardTypeBcc(value.brand);
                        }
                    });
                    this.getInstallmentsByApi();
                };

                this.getInstallmentsByApi = function () {
                    if (!isNaN(this.creditCardCcAmountBcc()) && this.creditCardCcAmountBcc() != '') {

                        $.when(
                            installmentsByBrandAndAmount(self.selectedCardTypeBcc(), this.creditCardCcAmountBcc())
                        ).done(function (data) {
                            self.allInstallments.removeAll();

                            _.map(data, function (value, key) {
                                self.allInstallments.push({
                                    'value': value.id,
                                    'interest': value.interest,
                                    'installments': value.label
                                });
                            });

                        }).always(function () {
                            // fullScreenLoader.stopLoader();
                        });

                    }

                };

                this.selectedCardTypeBcc.subscribe(function (newValue) {

                    if (newValue) {

                        //fullScreenLoader.startLoader();

                        var creditCardAmount = self.creditCardCcAmountBcc() ? self.creditCardCcAmountBcc() : 0;
                        $.when(
                            installmentsByBrandAndAmount(newValue, creditCardAmount)
                        ).done(function (data) {
                            self.allInstallments.removeAll();

                            _.map(data, function (value, key) {
                                self.allInstallments.push({
                                    'value': value.id,
                                    'interest': value.interest,
                                    'installments': value.label
                                });
                            });

                        }).always(function () {
                            //fullScreenLoader.stopLoader();
                        });

                    }

                });

                this.creditSavedCardBcc.subscribe(function(value){
                    if (typeof value != 'undefined') {
                        var cards = window.checkoutConfig.payment.mundipagg_billet_creditcard.cards;
                        for (var i = 0, len = cards.length; i < len; i++) {
                            if(cards[i].id == value){
                                self.creditCardSavedNumberBcc(window.checkoutConfig.payment.mundipagg_billet_creditcard.cards[i].last_four_numbers);
                                self.selectedCardTypeBcc(window.checkoutConfig.payment.mundipagg_billet_creditcard.cards[i].brand);
                            }
                        }
                    }
                });

            },

            /**
             * Select current payment token
             */
            selectPaymentMethod: function () {
                this.oldInstallmentTax = window.checkoutConfig.payment.ccform.installments.value;
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
                window.checkoutConfig.payment.ccform.installments.value = newTax;
                quote.setTotals(total);

                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                $("#mundipagg_billet_creditcard_installments").val('');

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
                        'creditCardTypeBcc',
                        'creditCardSavedNumberBcc',
                        'creditCardCcAmountBcc',
                        'creditCardCcTaxAmountBcc',
                        'creditCardBilletAmountBcc',
                        'creditCardOwnerBcc',
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
                        'creditCardInstallmentsBcc'
                    ]);

                return this;
            },

            getCode: function () {
                return 'mundipagg_billet_creditcard';
            },

            isActive: function () {
                return window.checkoutConfig.payment.mundipagg_billet_creditcard.active;
            },

            isInstallmentsActive: function () {
                return window.checkoutConfig.payment.ccform.installments.active['mundipagg_creditcard'];
            },

            getCcInstallments: function () {
                var self = this;

                //fullScreenLoader.startLoader();
                $.when(
                    installments()
                ).done(function (transport) {
                    self.allInstallments.removeAll();

                    _.map(transport, function (value, key) {
                        self.allInstallments.push({
                            'value': value.id,
                            'interest': value.interest,
                            'installments': value.label
                        });
                    });

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

            getCcInstallmentsValues: function () {
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
                        'cc_cid': this.creditCardVerificationNumberBcc(),
                        'cc_type': this.creditCardTypeBcc(),
                        'cc_last_4': this.creditCardSavedNumberBcc() ? this.creditCardSavedNumberBcc() : this.creditCardNumberBcc(),
                        'cc_exp_year': this.creditCardExpYearBcc(),
                        'cc_exp_month': this.creditCardExpMonthBcc(),
                        'cc_number': this.creditCardNumberBcc(),
                        'cc_owner': this.creditCardOwnerBcc(),
                        'cc_savecard': this.creditCardsavecardBcc() ? 1 : 0,
                        'cc_installments': this.creditCardInstallmentsBcc(),
                        'cc_cc_amount': this.creditCardCcAmountBcc(),
                        'cc_cc_tax_amount': this.creditCardCcTaxAmountBcc(),
                        'cc_saved_card': this.creditSavedCardBcc(),
                        'cc_billet_amount': this.creditCardBilletAmountBcc(),
                    }
                };
            },

            onInstallmentItemChange: function () {
                this.updateTotalWithTax(jQuery('#mundipagg_billet_creditcard_installments option:selected').attr('interest'));
            },

            updateTotalWithTax: function (newTax) {
                if (typeof this.oldInstallmentTax == 'undefined') {
                    this.oldInstallmentTax = 0;
                }
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
                this.creditCardCcTaxAmountBcc(newTax * 100);
                total.base_tax_amount = parseFloat(newTax);
                this.oldInstallmentTax = newTax;
                window.checkoutConfig.payment.ccform.installments.value = newTax;
                quote.setTotals(total);
            },

            onSavedCardChange: function() {
                if (jQuery('#mundipagg_billet_creditcard_card').val()) {
                    jQuery('#mundipagg_billet_creditcard_cc_icons').css('display','none');
                    jQuery('#mundipagg_billet_creditcard_cc_savecard').css('display','none');
                    jQuery('#mundipagg_billet_creditcard_cc_number_div').css('display','none');
                    jQuery('#mundipagg_billet_creditcard_cc_owner_div').css('display','none');
                    jQuery('#mundipagg_billet_creditcard_cc_type_exp_div').css('display','none');
                    jQuery('#mundipagg_billet_creditcard_cc_type_cvv_div').css('display','none');
                }else{
                    jQuery('#mundipagg_billet_creditcard_cc_icons').css('display','block');
                    jQuery('#mundipagg_billet_creditcard_cc_savecard').css('display','block');
                    jQuery('#mundipagg_billet_creditcard_cc_number_div').css('display','block');
                    jQuery('#mundipagg_billet_creditcard_cc_owner_div').css('display','block');
                    jQuery('#mundipagg_billet_creditcard_cc_type_exp_div').css('display','block');
                    jQuery('#mundipagg_billet_creditcard_cc_type_cvv_div').css('display','block');
                }
            },
        })
    }
);
