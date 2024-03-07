define([
    'jquery',
    'Pagarme_Pagarme/js/core/checkout/PlatformConfig',
    'Pagarme_Pagarme/js/core/checkout/FormObject',
    'Pagarme_Pagarme/js/core/checkout/PlatformFormHandler',
    'Pagarme_Pagarme/js/core/checkout/Bin',
    'Pagarme_Pagarme/js/core/models/CreditCardModel',
    'Pagarme_Pagarme/js/core/checkout/CreditCardToken',
    'Pagarme_Pagarme/js/core/models/VoucherModel',
    'Pagarme_Pagarme/js/core/models/DebitModel',
    'Pagarme_Pagarme/js/core/models/TwoCreditcardsModel',
    'Pagarme_Pagarme/js/core/models/PixModel',
    'Pagarme_Pagarme/js/core/models/BoletoModel',
    'Pagarme_Pagarme/js/core/models/BoletoCreditcardModel',
    'Pagarme_Pagarme/js/core/validators/CustomerValidator',
], (
    $,
    PlatformConfig,
    FormObject,
    FormHandler,
    Bin,
    CreditCardModel,
    CreditCardToken,
    VoucherModel,
    DebitModel,
    TwoCreditcardsModel,
    PixModel,
    BoletoModel,
    BoletoCreditcardModel,
    CustomerValidator,
) => {

    const fieldError = '.field-error';
    const errorClass = '_error';
    const optionSelectedSelector = 'option:selected';
    return class PaymentMethodController {
        constructor(methodCode, platformConfig) {
            this.methodCode = methodCode;
            this.platformConfig = platformConfig;
        }

        init() {
            const paymentMethodInit = this.methodCode + 'Init';
            this[paymentMethodInit]();
        }

        formObject(formObject) {
            this.formObject = formObject;
        }

        formValidation() {
            const formValidation = this.methodCode + 'Validation';

            return this[formValidation]();
        }

        creditcardInit() {
            this.platformConfig = PlatformConfig.bind(this.platformConfig);
            this.formObject = FormObject.creditCardInit(this.platformConfig.isMultibuyerEnabled);

            if (!this.formObject) {
                return;
            }

            this.model = new CreditCardModel(
                this.formObject,
                this.platformConfig.publicKey
            );

            this.fillCardAmount(this.formObject, 1);
            this.hideCardAmount(this.formObject);
            this.fillFormText(this.formObject, 'pagarme_creditcard');
            this.fillSavedCreditCardsSelect(this.formObject);
            this.fillBrandList(this.formObject, 'pagarme_creditcard');
            this.fillInstallments(this.formObject);

            if (!this.platformConfig.isMultibuyerEnabled) {
                this.removeMultibuyerForm(this.formObject);
            }

            if (this.platformConfig.isMultibuyerEnabled) {
                this.fillMultibuyerStateSelect(this.formObject);
                this.addShowMultibuyerListener(this.formObject);
            }

            this.addCreditCardListeners(this.formObject);
            this.modelToken = new CreditCardToken(this.formObject);

            this.subscribeTotal();
        }

        subscribeTotal() {
            const _self = this;

            this.platformConfig.updateTotals.totals.subscribe(function(){
                if (_self.methodCode === 'twocreditcards' || _self.methodCode === 'boletoCreditcard') {
                    let totalAmount = 0;
                    const separator = '.';
                    for (let i = 0, len = _self.formObject.numberOfPaymentForms; i < len; i++) {
                        let amount = _self.formObject[i].inputAmount.val();
                        if (amount === undefined) {
                            continue;
                        }
                        amount = amount.replace(_self.platformConfig.currency.decimalSeparator, separator);

                        totalAmount += parseFloat(amount);
                    }

                    if (totalAmount === _self.platformConfig.updateTotals.getTotals()().grand_total) {
                        return;
                    }

                    for (let i = 0, len = _self.formObject.numberOfPaymentForms; i < len; i++) {
                        _self.fillCardAmount(_self.formObject[i], 2, i);
                        _self.fillInstallments(_self.formObject[i]);
                    }
                    return;
                }
                _self.fillCardAmount(_self.formObject, 1);
                _self.fillInstallments(_self.formObject);
            });
        }

        voucherInit() {
            this.platformConfig = PlatformConfig.bind(this.platformConfig);
            this.formObject = FormObject.voucherInit(this.platformConfig.isMultibuyerEnabled);

            if (!this.formObject) {
                return;
            }

            this.model = new VoucherModel(
                this.formObject,
                this.platformConfig.publicKey
            );

            this.fillCardAmount(this.formObject, 1);
            this.hideCardAmount(this.formObject);
            this.fillFormText(this.formObject, 'pagarme_voucher');
            this.fillBrandList(this.formObject, "pagarme_voucher");
            this.removeInstallmentsSelect(this.formObject);
            this.fillSavedCreditCardsSelect(this.formObject);
            this.showCvvCard(this.formObject);

            if (!this.platformConfig.isMultibuyerEnabled) {
                this.removeMultibuyerForm(this.formObject);
            }

            if (this.platformConfig.isMultibuyerEnabled) {
                this.fillMultibuyerStateSelect(this.formObject);
                this.addShowMultibuyerListener(this.formObject);
            }

            this.addCreditCardListeners(this.formObject);
            this.modelToken = new CreditCardToken(this.formObject);
        }

        debitInit() {
            this.platformConfig = PlatformConfig.bind(this.platformConfig);
            this.formObject = FormObject.debitInit(this.platformConfig.isMultibuyerEnabled);

            if (!this.formObject) {
                return;
            }

            this.model = new DebitModel(
                this.formObject,
                this.platformConfig.publicKey
            );

            this.fillCardAmount(this.formObject, 1);
            this.hideCardAmount(this.formObject);
            this.fillFormText(this.formObject, 'pagarme_debit');
            this.fillBrandList(this.formObject, "pagarme_debit");
            this.removeInstallmentsSelect(this.formObject);
            this.fillSavedCreditCardsSelect(this.formObject);

            if (!this.platformConfig.isMultibuyerEnabled) {
                this.removeMultibuyerForm(this.formObject);
            }

            if (this.platformConfig.isMultibuyerEnabled) {
                this.fillMultibuyerStateSelect(this.formObject);
                this.addShowMultibuyerListener(this.formObject);
            }

            this.addCreditCardListeners(this.formObject);
            this.modelToken = new CreditCardToken(this.formObject);
        }

        twocreditcardsInit() {
            this.platformConfig = PlatformConfig.bind(this.platformConfig);
            this.formObject = FormObject.twoCreditCardsInit(this.platformConfig.isMultibuyerEnabled);

            if (!this.formObject) {
                return;
            }
            this.model = new TwoCreditcardsModel(
                this.formObject,
                this.platformConfig.publicKey
            );

            const isTotalOnAmountInputs = this.isTotalOnAmountInputs(this.formObject, this.platformConfig);

            if (typeof this.formObject[1] !== "undefined") {
                for (let i = 0, len = this.formObject.numberOfPaymentForms; i < len; i++) {
                    this.fillFormText(this.formObject[i], 'pagarme_two_creditcard');

                    if (this.formObject[i].inputAmount.val() === "" || !isTotalOnAmountInputs) {
                        this.fillCardAmount(this.formObject[i], 2, i);
                    }

                    this.fillBrandList(this.formObject[i], 'pagarme_two_creditcard');
                    this.fillSavedCreditCardsSelect(this.formObject[i]);
                    this.fillInstallments(this.formObject[i]);

                    if (!this.platformConfig.isMultibuyerEnabled) {
                        this.removeMultibuyerForm(this.formObject[i]);
                    }

                    if (this.platformConfig.isMultibuyerEnabled) {
                        this.fillMultibuyerStateSelect(this.formObject[i]);
                        this.addShowMultibuyerListener(this.formObject[i]);
                    }

                    this.addCreditCardListeners(this.formObject[i]);
                    this.addInputAmountBalanceListener(this.formObject[i], i);

                }
            }

            this.modelToken = new CreditCardToken(this.formObject);
            this.subscribeTotal();
        }

        pixInit() {
            this.platformConfig = PlatformConfig.bind(this.platformConfig);
            this.formObject = FormObject.pixInit(this.platformConfig.isMultibuyerEnabled);

            if (!this.formObject) {
                return;
            }

            this.model = new PixModel(this.formObject);
            this.hideCardAmount(this.formObject);

            if (!this.platformConfig.isMultibuyerEnabled) {
                this.removeMultibuyerForm(this.formObject);
            }

            if (this.platformConfig.isMultibuyerEnabled) {
                this.fillMultibuyerStateSelect(this.formObject);
                this.addShowMultibuyerListener(this.formObject);
                this.addValidatorListener(this.formObject);
            }
        }

        boletoInit() {
            this.platformConfig = PlatformConfig.bind(this.platformConfig);
            this.formObject = FormObject.boletoInit(this.platformConfig.isMultibuyerEnabled);

            if (!this.formObject) {
                return;
            }

            this.model = new BoletoModel(this.formObject);
            this.hideCardAmount(this.formObject);

            if (!this.platformConfig.isMultibuyerEnabled) {
                this.removeMultibuyerForm(this.formObject);
            }

            if (this.platformConfig.isMultibuyerEnabled) {
                this.fillMultibuyerStateSelect(this.formObject);
                this.addShowMultibuyerListener(this.formObject);
                this.addValidatorListener(this.formObject);
            }
        }

        removeSavedCardsSelect(formObject) {
            const formHandler = new FormHandler();
            formHandler.init(formObject);
            formHandler.removeSavedCardsSelect(formObject);
        }

        boletoCreditcardInit() {
            this.platformConfig = PlatformConfig.bind(this.platformConfig);
            this.formObject = FormObject.boletoCreditCardInit(this.platformConfig.isMultibuyerEnabled);

            if (!this.formObject) {
                return;
            }

            const isTotalOnAmountInputs = this.isTotalOnAmountInputs(this.formObject, this.platformConfig);

            if (typeof this.formObject[1] !== "undefined") {

                for (let i = 0, len = this.formObject.numberOfPaymentForms; i < len; i++) {

                    if (this.formObject[i].inputAmount.val() === "" || !isTotalOnAmountInputs) {
                        this.fillCardAmount(this.formObject[i], 2, i);
                    }

                    if (!this.platformConfig.isMultibuyerEnabled) {
                        this.removeMultibuyerForm(this.formObject[i]);
                    }

                    if (this.platformConfig.isMultibuyerEnabled) {
                        this.fillMultibuyerStateSelect(this.formObject[i]);
                        this.addShowMultibuyerListener(this.formObject[i]);
                    }

                    this.formObject[i].inputAmountContainer.show();
                    this.addInputAmountBalanceListener(this.formObject[i], i);
                }

                this.fillBrandList(this.formObject[1], 'pagarme_billet_creditcard');
                this.fillFormText(this.formObject[1], 'pagarme_billet_creditcard');
                this.fillSavedCreditCardsSelect(this.formObject[1]);
                this.fillInstallments(this.formObject[1]);
                this.addCreditCardListeners(this.formObject[1]);
                this.modelToken = new CreditCardToken(this.formObject[1]);
            }

            this.model = new BoletoCreditcardModel(
                this.formObject,
                this.platformConfig.publicKey
            );

            this.subscribeTotal();
        }

        addCreditCardListeners(formObject) {
            if (!formObject) {
                return;
            }

            this.addValidatorListener(formObject);
            this.addCreditCardNumberListener(formObject);
            this.addCreditCardInstallmentsListener(formObject);
            this.addCreditCardHolderNameListener(formObject);
            this.addSavedCreditCardsListener(formObject);
            this.removeSavedCards(formObject);
        }

        removeSavedCards(formObject) {
            if (checkoutConfig.payment[formObject.savedCardSelectUsed].enabled_saved_cards) {
                return;
            }

            const selectCard = document.querySelector(formObject.containerSelector)
                .querySelector('.saved-card');

            if (selectCard == null) {
                return;
            }

            selectCard.remove();
        }

        addInputAmountBalanceListener(formObject, id) {
            const paymentMethodController = this;

            formObject.inputAmount.on('change', function () {
                paymentMethodController.fillInstallments(formObject);
                const formId = paymentMethodController.model.getFormIdInverted(id);
                const form = paymentMethodController.formObject[formId];
                paymentMethodController.fillInstallments(form);

                setTimeout(function () {
                    paymentMethodController.updateTotalByPaymentMethod(paymentMethodController, form.creditCardInstallments);
                }, 3000);

            });

            formObject.inputAmount.on('keyup', function(){
                const element = $(this);

                const originalValue = paymentMethodController.platformConfig.updateTotals.getTotals()().grand_total;
                let orderAmount = (originalValue).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                orderAmount = orderAmount.replace(/[^0-9]/g, '');
                orderAmount = Number(orderAmount);

                let value = element.val();
                value = value.replace(/[^0-9]/g, '');
                value = Number(value);

                if (value >= orderAmount) {
                    value = orderAmount - 1;
                }

                if (isNaN(value) || value == 0) {
                    value = 1;
                }

                let remaining = orderAmount - value;

                remaining = (remaining / 100).toFixed(2);
                value = (value / 100).toFixed(2);

                const formId = paymentMethodController.model.getFormIdInverted(id);
                const form = paymentMethodController.formObject[formId];

                form.inputAmount.val(remaining.toString().replace('.', paymentMethodController.platformConfig.currency.decimalSeparator));
                element.val(value.toString().replace('.', paymentMethodController.platformConfig.currency.decimalSeparator));
            });
        }

        addCreditCardHolderNameListener(formObject) {
            const paymentMethodController = this;
            formObject.creditCardHolderName.on('keyup', function () {
                const element = $(this);
                paymentMethodController.clearNumbers(element);
            });
        }

        addValidatorListener(formObject) {
            const paymentMethodController = this;

            $(formObject.containerSelector).on('change', function (event) {
                const element = $(event.target);
                if (
                    element.attr('name').startsWith('payment[cc_type]')
                    && element.val() !== 'default'
                ) {
                    paymentMethodController.validateBrandField(formObject);
                    return;
                }
                if (element.attr('name').startsWith('payment[cc_number]')) {
                    paymentMethodController.validateCcNumberField(element, formObject);
                    return;
                }
                if (
                    element.attr('name').startsWith('payment[cc_exp_month]')
                    || element.attr('name').startsWith('payment[cc_exp_year]')
                ) {
                    paymentMethodController.validateCcExpDateField(formObject);
                    return;
                }
            });
        }
        addCreditCardNumberListener(formObject) {

            const paymentMethodController = this;

            formObject.creditCardNumber.unbind();
            formObject.creditCardNumber.on('keydown', function () {
                const element = $(this);
                paymentMethodController.limitCharacters(element, 19);
            });

            const binObj = new Bin();

            formObject.creditCardNumber.on('keyup', function () {
                const element = $(this);
                paymentMethodController.clearLetters(element);
            });

            formObject.creditCardNumber.on('change', function () {
                const element = $(this);

                setTimeout(function() {
                    paymentMethodController.setBin(binObj,  element, formObject);
                }, 300);
            });
        }

        twoCardsTotal(paymentMethod) {
            const card1 = paymentMethod.formObject[0].creditCardInstallments;
            const card2 = paymentMethod.formObject[1].creditCardInstallments;

            const totalCard1 = paymentMethod.formObject[0].inputAmount.val().replace(this.platformConfig.currency.decimalSeparator, ".");
            const totalCard2 = paymentMethod.formObject[1].inputAmount.val().replace(this.platformConfig.currency.decimalSeparator, ".");

            const interestTotalCard1 = $(card1).find(":selected").attr("interest");
            const interestTotalCard2 = $(card2).find(":selected").attr("interest");

            let sumTotal = (parseFloat(totalCard1) + parseFloat(totalCard2));
            let sumInterestTotal = (parseFloat(interestTotalCard1) + parseFloat(interestTotalCard2));

            sumTotal = (sumTotal + sumInterestTotal).toString();
            sumInterestTotal = sumInterestTotal.toString();

            return { sumTotal, sumInterestTotal };
        }

        boletoCreditCardTotal(paymentMethod) {
            const cardElement = paymentMethod.formObject[1].creditCardInstallments;

            let sumInterestTotal = $(cardElement).find(":selected").attr("interest");

            const valueCard = paymentMethod.formObject[1].inputAmount.val().replace(this.platformConfig.currency.decimalSeparator, ".");
            const valueBoleto = paymentMethod.formObject[0].inputAmount.val().replace(this.platformConfig.currency.decimalSeparator, ".");

            let sumTotal = (parseFloat(valueCard) + parseFloat(valueBoleto));

            sumTotal = (sumTotal + parseFloat(sumInterestTotal)).toString();
            if (sumInterestTotal === undefined) {
                sumInterestTotal = 0.0;
            }
            sumInterestTotal = sumInterestTotal.toString();

            return { sumTotal, sumInterestTotal };
        }

        updateTotalByPaymentMethod(paymentMethod, event) {
            let interest = $(event).find(':selected').attr("interest");
            let grandTotal = $(event).find(':selected').attr("total_with_tax");

            if (paymentMethod.methodCode === "twocreditcards") {
                const twoCardsTotalObject = paymentMethod.twoCardsTotal(paymentMethod);

                grandTotal = twoCardsTotalObject.sumTotal;
                interest = twoCardsTotalObject.sumInterestTotal;
            }

            if (paymentMethod.methodCode === "boletoCreditcard") {
                const boletoCreditCardTotalObject = paymentMethod.boletoCreditCardTotal(paymentMethod);

                grandTotal = boletoCreditCardTotalObject.sumTotal;
                interest = boletoCreditCardTotalObject.sumInterestTotal;
            }

            paymentMethod.updateTotal(
                interest,
                grandTotal,
                $(event).attr('name')
            );
        }

        addCreditCardInstallmentsListener(formObject) {
            const paymentMethodController = this;

            formObject.creditCardInstallments.on('change', function () {
                const value = $(this).val();

                if (value != "" && value != 'undefined') {
                    paymentMethodController.updateTotalByPaymentMethod(paymentMethodController, this);
                }
            });
        }

        addSavedCreditCardsListener(formObject) {

            const paymentMethodController = this;
            let brand = formObject.savedCreditCardSelect
                .find(optionSelectedSelector)
                .attr('brand');

            if (brand == undefined) {
                brand = formObject.creditCardBrand.val();
            }


            formObject.creditCardBrand.val(brand);
            const formHandler = new FormHandler();
            formHandler.init(formObject);


            formObject.savedCreditCardSelect.on('change', function() {
                const value = $(this).val();
                const currentSavedCardBrand = $(this).find(optionSelectedSelector).attr('brand');

                formHandler.switchBrand(currentSavedCardBrand);
                if (value === 'new') {
                    $(formObject.containerSelector + ' .new').show();

                    if (
                        typeof formObject.multibuyer != 'undefined' &&
                        typeof formObject.multibuyer.showMultibuyer != 'undefined'
                    ) {
                        formObject.multibuyer.showMultibuyer.parent().show();
                    }
                    paymentMethodController.fillInstallments(formObject);
                    return
                }

                paymentMethodController.fillInstallments(formObject);
                $(formObject.containerSelector + ' .new').hide();

                if (
                    typeof formObject.multibuyer != 'undefined' &&
                    typeof formObject.multibuyer.showMultibuyer != 'undefined'
                ) {
                    formObject.multibuyer.showMultibuyer.parent().hide();
                }

                if (formObject.containerSelector == "#pagarme_voucher-form") {
                    paymentMethodController.showCvvCard(formObject);
                }
            });
        }

        placeOrder(placeOrderObject) {
            const customerValidator = new CustomerValidator(
                this.platformConfig.addresses.billingAddress
            );
            customerValidator.validate();
            const errors = customerValidator.getErrors();

            if (errors.length > 0) {
                for (let id in errors) {
                    this.model.addErrors(errors[id]);
                }
                return;
            }

            const isPublicKeyValid = this.validatePublicKey(
                this.platformConfig.publicKey
            );

            if (!isPublicKeyValid) {
                return;
            }

            this.model.placeOrder(placeOrderObject);
        }

        updateTotal(interest, grandTotal, selectName) {
            const paymentMethodController = this;

            /**@fixme Move gettotals() to PlatformFormBiding */
            const total = paymentMethodController.platformConfig.updateTotals.getTotals()();
            interest = (parseInt((interest * 100).toFixed(2))) / 100;

            if (interest < 0) {
                interest = 0;
            }

            total.tax_amount = interest;
            total.base_tax_amount = interest;

            for (let i = 0, len = total.total_segments.length; i < len; i++) {
                if (total.total_segments[i].code === "grand_total") {
                    grandTotal = parseInt((grandTotal * 100).toFixed(2));
                    total.total_segments[i].value = grandTotal / 100;
                    continue;
                }
                if (total.total_segments[i].code === "tax") {

                    total.total_segments[i].value = interest;
                }
            }

            paymentMethodController.platformConfig.updateTotals.setTotals(total);
        }

        sumInterests(interest, selectName) {
            const paymentMethodController = this;

            const formObject = paymentMethodController.formObject;

            for (let id in formObject) {

                if (id.length > 1 || formObject[id].creditCardInstallments == undefined) {
                    continue;
                }

                const name = formObject[id].creditCardInstallments.attr('name');
                if (name == selectName) {
                    continue;
                }

                const otherInterest = formObject[id].creditCardInstallments.find(':selected').attr('interest');
                if (isNaN(otherInterest)) {
                    continue;
                }

                interest = parseFloat(otherInterest) + parseFloat(interest);
            }

            return interest;
        }

        removeInstallmentsSelect(formObject) {
            const formHandler = new FormHandler();
            formHandler.init(formObject);
            formHandler.removeInstallmentsSelect(formObject);
        }

        showCvvCard(formObject) {
            const cvvElement = document.querySelector(formObject.containerSelector + " .cvv");

            if (cvvElement != undefined) {
                cvvElement.style.display = "";
            }
        }

        fillInstallments(form) {
            const _self = this;

            if (form.creditCardBrand == undefined) {
                return;
            }

            const installmentSelected = form.creditCardInstallments.val();

            let formHandler = new FormHandler();

            let selectedBrand = form.creditCardBrand.val();

            let amount = form.inputAmount.val();
            if (!selectedBrand || selectedBrand === 'default') {
                formHandler.updateInstallmentSelect([], form.creditCardInstallments);
                return;
            }

            if (typeof amount == "undefined") {
                amount = 0;
            }

            const installmentsUrl =
                this.platformConfig.urls.installments + '/' +
                selectedBrand + '/' +
                amount;

            $.ajax({
                url: installmentsUrl,
                method: 'GET',
                cache: true,
            }).done(function(data) {
                formHandler = new FormHandler();

                if (!data.length) return;

                form.creditCardInstallments.prop('disabled', true);
                formHandler.updateInstallmentSelect(data, form.creditCardInstallments, installmentSelected);
                form.creditCardInstallments.prop('disabled', false);

                formHandler.init(form);
                formHandler.switchBrand(selectedBrand);
            });
        }
        fillBrandList(formObject, method) {
            if (method == undefined) {
                method = 'pagarme_creditcard';
            }
            const formHandler = new FormHandler();
            formHandler.fillBrandList(
                this.platformConfig.avaliableBrands[method],
                formObject
            );
        }

        fillCardAmount(formObject, count, card = null) {
            const orderAmount = this.platformConfig.updateTotals.getTotals()().grand_total / count;

            let amount = orderAmount.toFixed(this.platformConfig.currency.precision);
            const separator = ".";

            amount = amount.replace(separator, this.platformConfig.currency.decimalSeparator);

            if (card === 1) {
                const orderAmountOriginal =  amount.replace(this.platformConfig.currency.decimalSeparator, ".");
                const amountBalance = (this.platformConfig.updateTotals.getTotals()().grand_total - orderAmountOriginal).toFixed(2);
                formObject.inputAmount.val(amountBalance.replace(".", this.platformConfig.currency.decimalSeparator));
                return;
            }

            formObject.inputAmount.val(amount);
        }
        validateCcNumberField(element, formObject) {
            if (element.val() === '') {
                formObject.creditCardBrand.val('');

                const formHandler = new FormHandler();
                formHandler.init(formObject);
                formHandler.switchBrand('');
            }
        }
        validateCcExpDateField(formObject) {
            const cardExpirationMonth = formObject.creditCardExpMonth;
            const cardExpirationYear = formObject.creditCardExpYear;

            const cardDate = new Date(cardExpirationYear.val(), cardExpirationMonth.val() -1);
            const dateNow = new Date();

            const monthParentsElements = cardExpirationMonth.parent().parent();
            const yearParentsElements = cardExpirationYear.parent().parent();
            const parentsElements = yearParentsElements.parents('.field');
            const parentsElementsError = parentsElements.find(fieldError);

            if (cardDate < dateNow) {
                monthParentsElements.addClass(errorClass);
                yearParentsElements.addClass(errorClass);
                parentsElementsError.show();
                return true;
            }

            monthParentsElements.removeClass(errorClass);
            yearParentsElements.removeClass(errorClass);
            parentsElementsError.hide();
            return false;
        }

        validateBrandField(formObject) {
            const element = formObject.creditCardBrand;
            const requiredElement = element.parent().parent();
            const requiredElementError = requiredElement.find(fieldError);

            const brands = [];
            PlatformConfig.PlatformConfig.avaliableBrands[formObject.savedCardSelectUsed].forEach(function (item) {
                brands.push(item.title.toUpperCase());
            });
            if (
                !brands.includes(element.val().toUpperCase())
                && element.val() !== 'default' || element.val() === ''
            ) {
                requiredElement.addClass(errorClass);
                requiredElementError.show();
                requiredElement.find('.nobrand').hide();
                return true;
            }

            requiredElement.removeClass(errorClass);
            requiredElementError.hide();

            return false;
        }
        setBin(binObj, creditCardNumberElement, formObject) {

            const bin = binObj;
            const cardNumber = bin.formatNumber(creditCardNumberElement.val());

            if (cardNumber.length < 4) {
                return;
            }

            const isNewBrand = bin.validate(cardNumber);

            bin.init(cardNumber);

            const formHandler = new FormHandler();
            formHandler.init(formObject);
            formHandler.switchBrand(bin.selectedBrand);
            if (isNewBrand) {
                this.validateBrandField(formObject);
                this.fillInstallments(formObject);
            }

            return;
        }

        limitCharacters(element, limit) {
            const val = element.val();

            if(val != "" && val.length > limit) {
                element.val(val.substring(0, limit));
            }
        }

        clearLetters(element) {
            const val = element.val();
            const newVal = val.replace(/[^0-9]+/g, '');
            element.val(newVal);
        }

        clearNumbers(element) {
            const val = element.val();
            const newVal = val.replace(/[0-9.-]+/g, '');
            element.val(newVal);
        }

        hideCardAmount(formObject) {
            const formHandler = new FormHandler();
            formHandler.init(formObject);
            formHandler.hideInputAmount(formObject);
        }

        fillFormText(formObject, method = null) {
            const formText = this.platformConfig.text;

            const creditCardExpYear = formObject.creditCardExpYear.val();
            const creditCardExpMonth = formObject.creditCardExpMonth.val()

            const formHandler = new FormHandler();
            formHandler.init(formObject);
            formHandler.fillExpirationYearSelect(formText, method, creditCardExpYear);
            formHandler.fillExpirationMonthSelect(formText, method, creditCardExpMonth);
            //@Todo add other texts
        }

        fillSavedCreditCardsSelect(formObject) {
            const platformConfig = this.platformConfig;

            const formHandler = new FormHandler();
            formHandler.init(formObject);
            formHandler.fillSavedCreditCardsSelect(platformConfig, formObject);

            if (typeof formObject.savedCreditCardSelect[0] != 'undefined') {

                let brand = formObject.savedCreditCardSelect
                    .find(optionSelectedSelector)
                    .attr('brand');

                if (brand == undefined) {
                    brand = formObject.creditCardBrand.val();
                }

                formObject.creditCardBrand.val(brand);

                if (
                    typeof formObject.multibuyer != 'undefined' &&
                    typeof formObject.multibuyer.showMultibuyer != 'undefined' &&
                    formObject.savedCreditCardSelect[0].length > 0
                ) {
                    formObject.multibuyer.showMultibuyer.parent().hide();
                }
            }
        }

        fillMultibuyerStateSelect(formObject) {
            const platformConfig = this.platformConfig;

            const formHandler = new FormHandler();
            formHandler.init(formObject);
            formHandler.fillMultibuyerStateSelect(platformConfig, formObject);
        }

        removeMultibuyerForm(formObject) {
            const formHandler = new FormHandler();
            formHandler.init(formObject);
            formHandler.removeMultibuyerForm(formObject);
        }

        addShowMultibuyerListener(formObject) {
            $(formObject.multibuyer.showMultibuyer).on('click', function () {
                formHandler.init(formObject);
                formHandler.toggleMultibuyer(formObject);
            });
        }

        isTotalOnAmountInputs(formObject, platformConfig) {
            const orderTotal = platformConfig.updateTotals.getTotals()().grand_total;
            const card1 = formObject[0].inputAmount.val()?.replace(platformConfig.currency.decimalSeparator, ".");
            const card2 = formObject[1].inputAmount.val()?.replace(platformConfig.currency.decimalSeparator, ".");
            const totalInputs = (parseFloat(card1) + parseFloat(card2));

            return orderTotal == totalInputs;
        }

        validatePublicKey(publicKey) {
            if (!publicKey) {
                const error =
                    "Não foi possivel conectar com o serviço de pagamento. " +
                    "Por favor contate o administrador da loja.";
                this.model.addErrors(error);
                return false;
            }

            return true;
        }
    }
});
