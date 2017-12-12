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
        'MundiPagg_MundiPagg/js/view/payment/cc-form',
        'ko',
        'MundiPagg_MundiPagg/js/action/installments',
        'MundiPagg_MundiPagg/js/action/installmentsByBrand',
        'MundiPagg_MundiPagg/js/action/installmentsByBrandAndAmount',
        'jquery',
        'jquerymask',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (Component,
              ko,
              installments,
              installmentsByBrand,
              installmentsByBrandAndAmount,
              $,
              jquerymask,
              quote,
              priceUtils,
              totals,
              fullScreenLoader) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MundiPagg_MundiPagg/payment/billet-creditcard',
                creditCardType: '',
                creditCardCcAmount: '',
                creditCardBilletAmount: '',
                creditCardInstallments: '',
                creditCardOwner: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardsavecard: 0,
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardSsIssue: '',
                creditCardVerificationNumber: '',
                creditSavedCard: window.checkoutConfig.payment.mundipagg_billet_creditcard.selected_card,
                selectedCardType: null,
                allInstallments: ko.observableArray([]),
                teste: ''
            },

            totals: quote.getTotals(),

            initialize: function () {
                this._super();

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

                    if (Math.abs(parseFloat(self.creditCardBilletAmount())) + Math.abs(parseFloat(self.creditCardCcAmount())) > totalQuote) {
                        self.bindCreditCardBilletAmount(null);
                        self.bindCreditCardCcAmount(null);
                    }

                }

                this.bindCreditCardBilletAmount = ko.computed({
                    read: function () {
                        var value = this.creditCardBilletAmount();
                        value = parseFloat(value.replace(/[^\d]/g, ""));
                        return this.formatPrice(value);
                    },
                    write: function (value) {
                        if (value != 'null') {
                            value = this.formatPrice(value);
                            value = value.replace(/[^,\d]/g, "");
                            value = value.replace(",", ".");
                            this.creditCardBilletAmount(value);
                            this.creditCardCcAmount((totalQuote - parseFloat(value)).toFixed(2));
                            this.validateTotalQuote();
                            this.getInstallmentsByApi();
                        }

                    },
                    owner: self
                });

                this.bindCreditCardCcAmount = ko.computed({
                    read: function () {
                        var value = this.creditCardCcAmount();
                        value = parseFloat(value.replace(/[^\d]/g, ""));
                        return this.formatPrice(value);
                    },
                    write: function (value) {
                        if (value != 'null') {
                            value = this.formatPrice(value);
                            value = value.replace(/[^,\d]/g, "");
                            value = value.replace(",", ".");
                            this.creditCardCcAmount(value);
                            this.creditCardBilletAmount((totalQuote - parseFloat(value)).toFixed(2));
                            this.validateTotalQuote();
                            this.getInstallmentsByApi();
                        }

                    },
                    owner: self
                });

                this.getInstallmentsByApi = function () {
                    if (!isNaN(this.creditCardCcAmount()) && this.creditCardCcAmount() != '') {

                        $.when(
                            installmentsByBrandAndAmount(self.selectedCardType(), this.creditCardCcAmount())
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

                this.selectedCardType.subscribe(function (newValue) {

                    if (newValue) {

                        fullScreenLoader.startLoader();

                        var creditCardAmount = self.creditCardCcAmount() ? self.creditCardCcAmount() : 0;
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
                            fullScreenLoader.stopLoader();
                        });

                    }

                });

            },

            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardCcAmount',
                        'creditCardBilletAmount',
                        'creditCardOwner',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear',
                        'creditCardsavecard',
                        'creditCardSsIssue',
                        'creditSavedCard',
                        'selectedCardType',
                        'creditCardInstallments',
                        'teste'
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

                fullScreenLoader.startLoader();
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
                    fullScreenLoader.stopLoader();
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
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'cc_owner': this.creditCardOwner(),
                        'cc_savecard': this.creditCardsavecard() ? 1 : 0,
                        'cc_installments': this.creditCardInstallments(),
                        'cc_cc_amount': this.creditCardCcAmount(),
                        'cc_saved_card': this.creditSavedCard(),
                        'cc_billet_amount': this.creditCardBilletAmount(),
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
                total.base_tax_amount = parseFloat(newTax);
                this.oldInstallmentTax = newTax;
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
