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
        'MundiPagg_MundiPagg/js/helper/address-helper',
        'MundiPagg_MundiPagg/js/action/installments',
        'MundiPagg_MundiPagg/js/action/installmentsByBrand',
        'MundiPagg_MundiPagg/js/action/installmentsByBrandAndAmount',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-payment-method',
        'MundiPagg_MundiPagg/js/action/creditcard/token',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate',
        'jquerymask'
    ],
    function (
        Component,
        ko,
        addressHelper,
        installments,
        installmentsByBrand,
        installmentsByBrandAndAmount,
        $,
        quote,
        priceUtils,
        totals,
        checkoutData, 
        selectPaymentMethodAction, 
        token,
        fullScreenLoader,
        $t
    ) {
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
                tokenCreditCardFirst: '',
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
                tokenCreditCardSecond: '',
                creditSavedCardSecond: window.checkoutConfig.payment.mundipagg_two_creditcard.selected_card,
                selectedCardTypeSecond: null,
                allInstallmentsSecond: ko.observableArray([]),
                creditCardBuyerCheckboxFirst: '',
                creditCardBuyerNameFirst: '',
                creditCardBuyerEmailFirst: '',
                creditCardBuyerDocumentFirst: '',
                creditCardBuyerStreetTitleFirst: '',
                creditCardBuyerStreetNumberFirst: '',
                creditCardBuyerStreetComplementFirst: '',
                creditCardBuyerNeighborhoodFirst: '',
                creditCardBuyerCityFirst: '',
                creditCardBuyerStateFirst: '',
                creditCardBuyerZipCodeFirst: '',
                creditCardBuyerCheckboxSecond: '',
                creditCardBuyerNameSecond: '',
                creditCardBuyerEmailSecond: '',
                creditCardBuyerDocumentSecond: '',
                creditCardBuyerStreetTitleSecond: '',
                creditCardBuyerStreetNumberSecond: '',
                creditCardBuyerStreetComplementSecond: '',
                creditCardBuyerNeighborhoodSecond: '',
                creditCardBuyerCitySecond: '',
                creditCardBuyerStateSecond: '',
                creditCardBuyerZipCodeSecond: '',
                quoteBilling: quote.billingAddress(),
                stateOptions: addressHelper.getStateOptions()
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

                    var currentAmount = jQuery("select[name='payment[cc_installments_first]']").attr('amount');
                    var currentBrand = jQuery("select[name='payment[cc_installments_first]']").attr('brand');

                    if(
                        amountFirst != parseFloat(currentAmount) ||
                        (currentBrand != "" && newValue != currentBrand)
                    ) {
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

                            jQuery("select[name='payment[cc_installments_first]']").attr('amount', amountFirst)
                            jQuery("select[name='payment[cc_installments_first]']").attr('brand', newValue)

                        }).always(function () {
                            fullScreenLoader.stopLoader();
                        });
                    }
                }

                this.updateInstallmentsSecondCard = function(newValue){

                    self.creditCardTypeSecond(newValue);

                    var amountSecond = self.secondCreditCardAmount() != '' ? self.secondCreditCardAmount() : 0;

                    var currentAmount = jQuery("select[name='payment[cc_installments_second]']").attr('amount');
                    var currentBrand = jQuery("select[name='payment[cc_installments_second]']").attr('brand');

                    if(
                        amountSecond != parseFloat(currentAmount) ||
                        (currentBrand != "" && newValue != currentBrand)
                    ){
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

                            jQuery("select[name='payment[cc_installments_second]']").attr('amount', amountSecond);
                            jQuery("select[name='payment[cc_installments_second]']").attr('brand', newValue);

                        }).always(function () {
                            fullScreenLoader.stopLoader();
                        });
                    }

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
                        value = value.replace(/[^\d]/g, "");
                        return this.formatPrice(value);
                    },
                    write: function (value) {
                        if (value != 'null') {
                            value = this.formatPrice(value);
                            var totalQuote = quote.getTotals()().grand_total;
                            value = value.replace(/[^,\d]/g, "");
                            value = value.replace(",", ".");
                            this.firstCreditCardAmount(value);
                            this.secondCreditCardAmount((totalQuote - value).toFixed(2));
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
                        value = value.replace(/[^\d]/g, "");
                        return this.formatPrice(value);
                    },
                    write: function (value) {
                        if (value != 'null') {
                            var totalQuote = quote.getTotals()().grand_total;
                            value = this.formatPrice(value);
                            value = value.replace(/[^,\d]/g, "");
                            value = value.replace(",", ".");
                            this.secondCreditCardAmount(value);
                            this.firstCreditCardAmount((totalQuote - value).toFixed(2));
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

                        var currentAmount =  jQuery("select[name='payment[cc_installments_first]']").attr('amount');
                        var currentBrand =  jQuery("select[name='payment[cc_installments_first]']").attr('brand');

                        if(
                            firstCreditCardAmount != parseFloat(currentAmount) ||
                            (currentBrand != "" && brandFirst != currentBrand)
                        ){
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
                                jQuery("select[name='payment[cc_installments_first]']").attr('amount', firstCreditCardAmount);
                                jQuery("select[name='payment[cc_installments_first]']").attr('amount', brandFirst);

                            }).always(function () {
                                // fullScreenLoader.stopLoader();
                            });
                        }

                        self.creditCardTypeSecond(brandSecond);
                        secondCreditCardAmount = secondCreditCardAmount != '' ? secondCreditCardAmount : 0;

                        var currentAmount = jQuery("select[name='payment[cc_installments_second]']").attr('amount');
                        var currentBrand = jQuery("select[name='payment[cc_installments_second]']").attr('brand');

                        if(
                            secondCreditCardAmount != parseFloat(currentAmount) ||
                            (currentBrand != "" && brandSecond != currentBrand)
                        ){
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

                                jQuery("select[name='payment[cc_installments_second]']").attr('amount', secondCreditCardAmount);
                                jQuery("select[name='payment[cc_installments_second]']").attr('brand', brandSecond);

                            }).always(function () {
                                // fullScreenLoader.stopLoader();
                            });
                        }

                    }

                };

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

            isSaveCardStyle: function() {
                if (window.checkoutConfig.payment.mundipagg_two_creditcard.selected_card) {
                    return 'display: none;';
                }

                return 'display: block;';
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
                        'creditCardInstallmentsSecond',
                        'creditCardBuyerCheckboxFirst',
                        'creditCardBuyerNameFirst',
                        'creditCardBuyerEmailFirst',
                        'creditCardBuyerDocumentFirst',
                        'creditCardBuyerStreetTitleFirst',
                        'creditCardBuyerStreetNumberFirst',
                        'creditCardBuyerStreetComplementFirst',
                        'creditCardBuyerNeighborhoodFirst',
                        'creditCardBuyerCityFirst',
                        'creditCardBuyerStateFirst',
                        'creditCardBuyerZipCodeFirst',
                        'creditCardBuyerCheckboxSecond',
                        'creditCardBuyerNameSecond',
                        'creditCardBuyerEmailSecond',
                        'creditCardBuyerDocumentSecond',
                        'creditCardBuyerStreetTitleSecond',
                        'creditCardBuyerStreetNumberSecond',
                        'creditCardBuyerStreetComplementSecond',
                        'creditCardBuyerNeighborhoodSecond',
                        'creditCardBuyerCitySecond',
                        'creditCardBuyerStateSecond',
                        'creditCardBuyerZipCodeSecond'
                    ]);

                return this;
            },

            getCode: function () {
                return 'mundipagg_two_creditcard';
            },

            isActive: function () {
                return window.checkoutConfig.payment.mundipagg_two_creditcard.active;
            },

            isMultiBuyerActive: function () {
                return window.checkoutConfig.multi_buyer;
            },

            isInstallmentsActive: function () {
                return window.checkoutConfig.payment.ccform.installments.active['mundipagg_creditcard'];
            },
            getCcInstallmentsFirst: function() {
                var self = this;

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
                var cc_last4_first = '';
                var cc_last4_second = '';
                if(this.creditCardSavedNumberFirst().substr(-4, 4) == ''){
                    cc_last4_first = this.creditCardNumberFirst().substr(-4,4);
                }else{
                    cc_last4_first = this.creditCardSavedNumberFirst().substr(-4, 4);
                }

                if(this.creditCardSavedNumberSecond().substr(-4, 4) == ''){
                    cc_last4_second = this.creditCardNumberSecond().substr(-4,4);
                }else{
                    cc_last4_second = this.creditCardSavedNumberSecond().substr(-4, 4);
                }

                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cc_first_card_amount': this.firstCreditCardAmount(),
                        'cc_first_card_tax_amount': this.firstCreditCardTaxAmount(),
                        'cc_last_4_first': cc_last4_first,
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
                        'cc_last_4_second': cc_last4_second,
                        'cc_cid_second': this.creditCardVerificationNumberSecond(),
                        'cc_type_second': this.creditCardTypeSecond(),
                        'cc_exp_year_second': this.creditCardExpYearSecond(),
                        'cc_exp_month_second': this.creditCardExpMonthSecond(),
                        'cc_number_second': this.creditCardNumberSecond(),
                        'cc_owner_second': this.creditCardOwnerSecond(),
                        'cc_savecard_second': this.creditCardsavecardSecond() ? 1 : 0,
                        'cc_saved_card_second': this.creditSavedCardSecond(),
                        'cc_installments_second': this.creditCardInstallmentsSecond(),
                        'cc_token_credit_card_first': this.tokenCreditCardFirst,
                        'cc_token_credit_card_second': this.tokenCreditCardSecond,
                        'cc_buyer_checkbox_first': this.creditCardBuyerCheckboxFirst(),
                        'cc_buyer_name_first': this.creditCardBuyerNameFirst(),
                        'cc_buyer_email_first': this.creditCardBuyerEmailFirst(),
                        'cc_buyer_document_first': this.creditCardBuyerDocumentFirst(),
                        'cc_buyer_street_title_first': this.creditCardBuyerStreetTitleFirst(),
                        'cc_buyer_street_number_first': this.creditCardBuyerStreetNumberFirst(),
                        'cc_buyer_street_complement_first': this.creditCardBuyerStreetComplementFirst(),
                        'cc_buyer_neighborhood_first': this.creditCardBuyerNeighborhoodFirst(),
                        'cc_buyer_city_first': this.creditCardBuyerCityFirst(),
                        'cc_buyer_state_first': this.creditCardBuyerStateFirst(),
                        'cc_buyer_zipcode_first': this.creditCardBuyerZipCodeFirst(),
                        'cc_buyer_checkbox_second': this.creditCardBuyerCheckboxSecond(),
                        'cc_buyer_name_second': this.creditCardBuyerNameSecond(),
                        'cc_buyer_email_second': this.creditCardBuyerEmailSecond(),
                        'cc_buyer_document_second': this.creditCardBuyerDocumentSecond(),
                        'cc_buyer_street_title_second': this.creditCardBuyerStreetTitleSecond(),
                        'cc_buyer_street_number_second': this.creditCardBuyerStreetNumberSecond(),
                        'cc_buyer_street_complement_second': this.creditCardBuyerStreetComplementSecond(),
                        'cc_buyer_neighborhood_second': this.creditCardBuyerNeighborhoodSecond(),
                        'cc_buyer_city_second': this.creditCardBuyerCitySecond(),
                        'cc_buyer_state_second': this.creditCardBuyerStateSecond(),
                        'cc_buyer_zipcode_second': this.creditCardBuyerZipCodeSecond()
                    }
                };
            },

            /**
             * Place order.
             */
            beforeplaceOrder: function (data, event) {

                if (window.checkoutConfig.customerData.hasOwnProperty('email') && data.getData().additional_data.cc_saved_card_second && data.getData().additional_data.cc_saved_card_first) {

                    if(this.isInstallmentsActive() == true) {
                        if (this.creditCardInstallmentsFirst() === undefined) {
                            this.messageContainer.addErrorMessage({
                                message: $t('Installments first card not informed.')
                            });
                            $("html, body").animate({scrollTop: 0}, 600);
                            return false;
                        }

                        if (this.creditCardInstallmentsSecond() === undefined) {
                            this.messageContainer.addErrorMessage({
                                message: $t('Installments second card not informed.')
                            });
                            $("html, body").animate({scrollTop: 0}, 600);
                            return false;
                        }
                    }



                    this.useCardIdPlaceOrder(data, event);
                }else{
                    if (data.getData().additional_data.cc_saved_card_second) {
                        this.createAndSendTokenCreditCardFirst(data, event);
                    }else{
                        if (data.getData().additional_data.cc_saved_card_first) {
                            this.createAndSendTokenCreditCardSecond(data, event);
                        }else{
                            this.createAndSendTokenCreditCard(data, event);
                        }
                    }
                    
                }
            },

            useCardIdPlaceOrder: function (data, event) {

                if((this.firstCreditCardAmount() === "") || (this.firstCreditCardAmount() == "0.00")){
                    this.messageContainer.addErrorMessage({
                        message: $t('Total of the first card not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if((this.secondCreditCardAmount() === "") || (this.secondCreditCardAmount() == "0.00")){
                    this.messageContainer.addErrorMessage({
                        message: $t('Total of the second card not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                this.placeOrder(data, event);
            },

            createAndSendTokenCreditCardFirst: function (data, event) {
                var self = this;
                var address = quote.billingAddress();

                var firstBrandIsValid = window.checkoutConfig.payment.mundipagg_two_creditcard.brandFirstCardIsValid;

                if(!firstBrandIsValid){
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    this.messageContainer.addErrorMessage({
                        message: $t('Brand first credit card not exists.')
                    });
                    return false;
                }

                if(this.creditCardOwnerFirst() === ""){
                    this.messageContainer.addErrorMessage({
                        message: $t('Name first credit card  not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.creditCardExpMonthFirst() === undefined){
                    this.messageContainer.addErrorMessage({
                        message: $t('Month first credit card  not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.creditCardExpYearFirst() === undefined){
                    this.messageContainer.addErrorMessage({
                        message: $t('Year first credit card  not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.creditCardVerificationNumberFirst() === ""){
                    this.messageContainer.addErrorMessage({
                        message: $t('Verifier first credit card code not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.isInstallmentsActive() == true) {
                    if (this.creditCardInstallmentsFirst() === undefined) {
                        this.messageContainer.addErrorMessage({
                            message: $t('Installments first card not informed.')
                        });
                        $("html, body").animate({scrollTop: 0}, 600);
                        return false;
                    }
                }

                if(typeof address.street == "undefined"){
                    this.messageContainer.addErrorMessage({
                        message: $t('Endereço inválido')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(typeof address.street == "undefined" || address.street.lenght < 3){
                    this.messageContainer.addErrorMessage({
                        message: $t('Endereço inválido. Verifique se todos os campos obrigatórios foram preenchidos corretamente')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                var dataJson = {
                        "type": "card",
                        "card": {
                            "type": "credit",
                            "number": this.creditCardNumberFirst(),
                            "holder_name": this.creditCardOwnerFirst(),
                            "exp_month": this.creditCardExpMonthFirst(),
                            "exp_year": this.creditCardExpYearFirst(),
                            "cvv": this.creditCardVerificationNumberFirst(),
                            "billing_address": {
                                "street": address.street[0],
                                "number": address.street[1],
                                "zip_code": address.postcode,
                                "complement": address.street[2],
                                "neighborhood": address.street[3],
                                "city": address.region,
                                "state": address.regionCode,
                                "country": address.countryId
                            }
                        }
                    };

                var data = data;
                var event = event;

                function successCallbackFirst (card) {
                    self.tokenCreditCardFirst = card.id;
                    self.placeOrder.call(this, data, event);
                }

                function failCallbackFirst (fail) {
                    console.log(fail);

                    fullScreenLoader.stopLoader();

                    self.messageContainer.addErrorMessage({
                        message: $t('Primeiro cartão inválido. Por favor, verifique os dados digitados e tente novamente')
                    });
                    $("html, body").animate({scrollTop: 0}, 600);
                }

                token.call(this, dataJson, successCallbackFirst, failCallbackFirst);
            },

            createAndSendTokenCreditCard: function (data, event) {
                var self = this;
                var address = quote.billingAddress();

                var firstBrandIsValid = window.checkoutConfig.payment.mundipagg_two_creditcard.brandFirstCardIsValid;
                var secondBrandIsValid = window.checkoutConfig.payment.mundipagg_two_creditcard.brandSecondCardIsValid;

                if(!firstBrandIsValid || !secondBrandIsValid){
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    this.messageContainer.addErrorMessage({
                        message: $t('Brand second credit card not exists.')
                    });
                    return false;
                }


                if(this.creditCardOwnerSecond() === ""){
                    this.messageContainer.addErrorMessage({
                        message: $t('Name second credit card  not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.creditCardExpMonthSecond() === undefined){
                    this.messageContainer.addErrorMessage({
                        message: $t('Month second credit card  not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.creditCardExpYearSecond() === undefined){
                    this.messageContainer.addErrorMessage({
                        message: $t('Year second credit card  not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.creditCardVerificationNumberSecond() === ""){
                    this.messageContainer.addErrorMessage({
                        message: $t('Verifier second credit card code not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.isInstallmentsActive() == true) {
                    if (this.creditCardInstallmentsSecond() === undefined) {
                        this.messageContainer.addErrorMessage({
                            message: $t('Installments second card not informed.')
                        });
                        $("html, body").animate({scrollTop: 0}, 600);
                        return false;
                    }
                }

                if(typeof address.street == "undefined" || address.street.lenght < 3){
                    this.messageContainer.addErrorMessage({
                        message: $t('Endereço inválido. Verifique se todos os campos obrigatórios foram preenchidos corretamente')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                var dataJson = {
                        "type": "card",
                        "card": {
                            "type": "credit",
                            "number": this.creditCardNumberFirst(),
                            "holder_name": this.creditCardOwnerFirst(),
                            "exp_month": this.creditCardExpMonthFirst(),
                            "exp_year": this.creditCardExpYearFirst(),
                            "cvv": this.creditCardVerificationNumberFirst(),
                            "billing_address": {
                                "street": address.street[0],
                                "number": address.street[1],
                                "zip_code": address.postcode,
                                "complement": address.street[2],
                                "neighborhood": address.street[3],
                                "city": address.region,
                                "state": address.regionCode,
                                "country": address.countryId
                            }
                        }
                    };

                var data = data;
                var event = event;

                function successCallbackFirst (card) {
                    self.tokenCreditCardFirst = card.id;
                    self.createAndSendTokenCreditCardSecond(data, event);
                }

                function failCallbackFirst (fail) {
                    console.log(fail);

                    fullScreenLoader.stopLoader();

                    self.messageContainer.addErrorMessage({
                        message: $t('Primeiro cartão inválido. Por favor, verifique os dados digitados e tente novamente')
                    });
                    $("html, body").animate({scrollTop: 0}, 600);
                }
                token.call(this, dataJson, successCallbackFirst, failCallbackFirst);
            },

            createAndSendTokenCreditCardSecond: function (data, event) {

                var secondBrandIsValid = window.checkoutConfig.payment.mundipagg_two_creditcard.brandSecondCardIsValid;

                if(!secondBrandIsValid){
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    this.messageContainer.addErrorMessage({
                        message: $t('Brand second credit card not exists.')
                    });
                    return false;
                }

                if(this.creditCardOwnerSecond() === ""){
                    this.messageContainer.addErrorMessage({
                        message: $t('Name second credit card  not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.creditCardExpMonthSecond() === undefined){
                    this.messageContainer.addErrorMessage({
                        message: $t('Month second credit card  not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                if(this.creditCardExpYearSecond() === undefined){
                    this.messageContainer.addErrorMessage({
                        message: $t('Year second credit card  not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }


                if(this.creditCardVerificationNumberSecond() === ""){
                    this.messageContainer.addErrorMessage({
                        message: $t('Verifier second credit card code not informed.')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                var self = this;
                var address = quote.billingAddress();

                if(typeof address.street == "undefined" || address.street.lenght < 3){
                    this.messageContainer.addErrorMessage({
                        message: $t('Endereço inválido. Verifique se todos os campos obrigatórios foram preenchidos corretamente')
                    });
                    $("html, body").animate({ scrollTop: 0 }, 600);
                    return false;
                }

                var dataJson = {
                    "type": "card",
                    "card": {
                        "type": "credit",
                        "number": this.creditCardNumberSecond(),
                        "holder_name": this.creditCardOwnerSecond(),
                        "exp_month": this.creditCardExpMonthSecond(),
                        "exp_year": this.creditCardExpYearSecond(),
                        "cvv": this.creditCardVerificationNumberSecond(),
                        "billing_address": {
                            "street": address.street[0],
                            "number": address.street[1],
                            "zip_code": address.postcode,
                            "complement": address.street[2],
                            "neighborhood": address.street[3],
                            "city": address.region,
                            "state": address.regionCode,
                            "country": address.countryId
                        }
                    }
                };

                var data = data;
                var event = event;

                function successCallbackSecond (card) {
                    self.tokenCreditCardSecond = card.id;
                    self.placeOrder.call(this, data, event);
                }

                function failCallbackSecond (fail) {
                    console.log(fail);

                    fullScreenLoader.stopLoader();

                    self.messageContainer.addErrorMessage({
                        message: $t('Segundo cartão inválido. Por favor, verifique os dados digitados e tente novamente')
                    });
                    $("html, body").animate({scrollTop: 0}, 600);
                }
                token.call(this, dataJson, successCallbackSecond, failCallbackSecond);
            },

            onInstallmentItemChange: function() {
                if((jQuery('#mundipagg_two_creditcard_installments_first option:selected').val() != '') && jQuery('#mundipagg_two_creditcard_installments_second option:selected').val() != '') {
                    this.updateTotalWithTax(jQuery('#mundipagg_two_creditcard_installments_first option:selected').attr('interest'), jQuery('#mundipagg_two_creditcard_installments_second option:selected').attr('interest'));
                }
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

                window.checkoutConfig.payment.ccform.installments.value = sumTax;
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
                    jQuery('#mundipagg_two_creditcard_cc_buyer_checkbox_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_name_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_email_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_document_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_street_title_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_street_number_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_street_complement_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_street_neighborhood_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_street_city_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_street_state_' + idValue).css('display','none');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_street_zipcode_' + idValue).css('display','none');

                    if(idValue == 'first'){
                        this.creditCardBuyerCheckboxFirst(false);
                    }

                    if(idValue == 'second'){
                        this.creditCardBuyerCheckboxSecond(false);
                    }

                }else{
                    jQuery('#mundipagg_two_creditcard_cc_icons_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_savecard_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_number_div_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_owner_div_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_type_exp_div_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_type_cvv_div_' + idValue).css('display','block');
                    jQuery('#mundipagg_two_creditcard_cc_buyer_checkbox_' + idValue).css('display','block');
                }
            },
            firstBuyerChecked: function(){
                if(this.creditCardBuyerCheckboxFirst()){
                    return 'display: block;';
                }else{
                    return 'display: none;';
                }
            },
            secondBuyerChecked: function(){
                if(this.creditCardBuyerCheckboxSecond()){
                    return 'display: block;';
                }else{
                    return 'display: none;';
                }
            },
            getCreditCardBuyerHelpHtml: function () {
                return '<span>' + $t('Add a different buyer to Credit Card') + '</span>';
            },
            getTitle: function () {
                return window.checkoutConfig.payment.mundipagg_two_creditcard.title;
            }
        })
    }
);
