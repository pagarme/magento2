var PaymentMethodController = function (methodCode, platformConfig) {
    this.methodCode = methodCode;
    this.platformConfig = platformConfig;
};

PaymentMethodController.prototype.init = function () {
    var paymentMethodInit = this.methodCode + 'Init';
    this[paymentMethodInit]();
};

PaymentMethodController.prototype.formObject = function (formObject) {
    this.formObject = formObject
};

PaymentMethodController.prototype.formValidation = function () {
    formValidation = this.methodCode + 'Validation';

    return this[formValidation]();
};

PaymentMethodController.prototype.creditcardInit = function () {
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
};

PaymentMethodController.prototype.voucherInit = function () {
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
};

PaymentMethodController.prototype.debitInit = function () {
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

PaymentMethodController.prototype.twocreditcardsInit = function () {
    this.platformConfig = PlatformConfig.bind(this.platformConfig);
    this.formObject = FormObject.twoCreditCardsInit(this.platformConfig.isMultibuyerEnabled);

    if (!this.formObject) {
        return;
    }
    this.model = new TwoCreditcardsModel(
        this.formObject,
        this.platformConfig.publicKey
    );

    var isTotalOnAmountInputs = this.isTotalOnAmountInputs(this.formObject, this.platformConfig);

    if (typeof this.formObject[1] !== "undefined") {
        for (var i = 0, len = this.formObject.numberOfPaymentForms; i < len; i++) {
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
};


PaymentMethodController.prototype.pixInit = function () {
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
    }
};

PaymentMethodController.prototype.boletoInit = function () {
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
    }
};

PaymentMethodController.prototype.removeSavedCardsSelect = function (formObject) {
    var formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.removeSavedCardsSelect(formObject);
}


PaymentMethodController.prototype.boletoCreditcardInit = function () {
    this.platformConfig = PlatformConfig.bind(this.platformConfig);
    this.formObject = FormObject.boletoCreditCardInit(this.platformConfig.isMultibuyerEnabled);

    if (!this.formObject) {
        return;
    }

    var isTotalOnAmountInputs = this.isTotalOnAmountInputs(this.formObject, this.platformConfig);

    if (typeof this.formObject[1] !== "undefined") {

        for (var i = 0, len = this.formObject.numberOfPaymentForms; i < len; i++) {

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
}

var timesRunObserver = 1;
PaymentMethodController.prototype.addCreditCardListeners = function (formObject) {
    if (!formObject) {
        return;
    }

    this.addCreditCardNumberListener(formObject);
    this.addCreditCardInstallmentsListener(formObject);
    this.addCreditCardHolderNameListener(formObject);
    this.addSavedCreditCardsListener(formObject);
    this.removeSavedCards(formObject);

    if (timesRunObserver <= 1) {
        timesRunObserver++;
        this.addListenerUpdateAmount();
    }
};

PaymentMethodController.prototype.removeSavedCards = function (formObject) {
    if (checkoutConfig.payment[formObject.savedCardSelectUsed].enabled_saved_cards) {
        return;
    }

    var selectCard = document.querySelector(formObject.containerSelector)
        .querySelector('.saved-card');

    if (selectCard == null) {
        return;
    }

    selectCard.remove();
};

PaymentMethodController.prototype.addListenerUpdateAmount = function () {
    var observerMutation = new MutationObserver(function (mutationsList, observer) {

        var paymentMethodName = ['twocreditcards', 'boletoCreditcard', 'voucher'];
        setTimeout(function () {
            for (var i = 0; i < paymentMethodName.length; i++) {
                var initPaymentMethod = new PaymentMethodController(paymentMethodName[i], platFormConfig);
                initPaymentMethod.init();
            }
        }, 800);

        var initCreditCard = new PaymentMethodController('creditcard', platFormConfig);
        initCreditCard.init();
    });

    observerMutation.observe(
        document.getElementById('opc-sidebar'),
        {
            attributes: false,
            childList: true,
            subtree: true
        }
    );
}

PaymentMethodController.prototype.addInputAmountBalanceListener = function(formObject, id) {
    var paymentMethodController = this;
    var id = id;

    formObject.inputAmount.on('change', function () {
        paymentMethodController.fillInstallments(formObject);
        var formId = paymentMethodController.model.getFormIdInverted(id);
        var form = paymentMethodController.formObject[formId];
        paymentMethodController.fillInstallments(form);

        setTimeout(function () {
            paymentMethodController.updateTotalByPaymentMethod(paymentMethodController, form.creditCardInstallments);
        }, 3000);

    });

    formObject.inputAmount.on('keyup', function(){
        element = jQuery(this);

        var orginalValue = platFormConfig.updateTotals.getTotals()().grand_total
        var orderAmount = (orginalValue).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        orderAmount = orderAmount.replace(/[^0-9]/g, '');
        orderAmount = Number(orderAmount);

        var value = element.val();
        value = value.replace(/[^0-9]/g, '');
        value = Number(value);

        if (value >= orderAmount) {
            value = orderAmount - 1;
        }

        if (isNaN(value) || value == 0) {
            value = 1;
        }

        var remaining = orderAmount - value;

        remaining = (remaining / 100).toFixed(2);
        value = (value / 100).toFixed(2);

        var formId = paymentMethodController.model.getFormIdInverted(id);
        var form = paymentMethodController.formObject[formId];

        form.inputAmount.val(remaining.toString().replace('.', paymentMethodController.platformConfig.currency.decimalSeparator));
        element.val(value.toString().replace('.', paymentMethodController.platformConfig.currency.decimalSeparator));
    });
}

PaymentMethodController.prototype.addCreditCardHolderNameListener = function(formObject) {
    var paymentMethodController = this;
    formObject.creditCardHolderName.on('keyup', function () {
        var element = jQuery(this);
        paymentMethodController.clearNumbers(element);
    });
}

PaymentMethodController.prototype.addCreditCardNumberListener = function(formObject) {

    var paymentMethodController = this;

    formObject.creditCardNumber.unbind();
    formObject.creditCardNumber.on('keydown', function () {
        element = jQuery(this);
        paymentMethodController.limitCharacters(element, 19);
    });

    var binObj = new Bin();

    formObject.creditCardNumber.on('keyup', function () {
        var element = jQuery(this);
        paymentMethodController.clearLetters(element);
    });

    formObject.creditCardNumber.on('change', function () {
        var element = jQuery(this);

        setTimeout(function() {
            paymentMethodController.setBin(binObj,  element, formObject);
        }, 300);
    }).bind(this);
};

PaymentMethodController.prototype.twoCardsTotal = function (paymentMethod) {
    var card1 = paymentMethod.formObject[0].creditCardInstallments.selector;
    var card2 = paymentMethod.formObject[1].creditCardInstallments.selector;

    var totalCard1 = paymentMethod.formObject[0].inputAmount.val().replace(platformConfig.currency.decimalSeparator, ".");
    var totalCard2 = paymentMethod.formObject[1].inputAmount.val().replace(platformConfig.currency.decimalSeparator, ".");

    var interestTotalCard1 = jQuery(card1).find(":selected").attr("interest");
    var interestTotalCard2 = jQuery(card2).find(":selected").attr("interest");

    var sumTotal = (parseFloat(totalCard1) + parseFloat(totalCard2));
    var sumInterestTotal = (parseFloat(interestTotalCard1) + parseFloat(interestTotalCard2));

    sumTotal = (sumTotal + sumInterestTotal).toString();
    sumInterestTotal = sumInterestTotal.toString();

    return { sumTotal, sumInterestTotal };
}

PaymentMethodController.prototype.boletoCreditCardTotal = function (paymentMethod) {
    var cardElement = paymentMethod.formObject[1].creditCardInstallments.selector;

    var sumInterestTotal = jQuery(cardElement).find(":selected").attr("interest");

    var valueCard = paymentMethod.formObject[1].inputAmount.val().replace(platformConfig.currency.decimalSeparator, ".");
    var valueBoleto = paymentMethod.formObject[0].inputAmount.val().replace(platformConfig.currency.decimalSeparator, ".");

    var sumTotal = (parseFloat(valueCard) + parseFloat(valueBoleto));

    sumTotal = (sumTotal + parseFloat(sumInterestTotal)).toString();
    sumInterestTotal = sumInterestTotal.toString();

    return { sumTotal, sumInterestTotal };
}

PaymentMethodController.prototype.updateTotalByPaymentMethod = function (paymentMethod, event) {
    var interest = jQuery(event).find(':selected').attr("interest");
    var grandTotal = jQuery(event).find(':selected').attr("total_with_tax");

    if (paymentMethod.methodCode === "twocreditcards") {
        var twoCardsTotalObject = paymentMethod.twoCardsTotal(paymentMethod);

        grandTotal = twoCardsTotalObject.sumTotal;
        interest = twoCardsTotalObject.sumInterestTotal;
    }

    if (paymentMethod.methodCode === "boletoCreditcard") {
        var boletoCreditCardTotalObject = paymentMethod.boletoCreditCardTotal(paymentMethod);

        grandTotal = boletoCreditCardTotalObject.sumTotal;
        interest = boletoCreditCardTotalObject.sumInterestTotal;
    }

    paymentMethod.updateTotal(
        interest,
        grandTotal,
        jQuery(event).attr('name')
    );
}

PaymentMethodController.prototype.addCreditCardInstallmentsListener = function (formObject) {
    var paymentMethodController = this;

    formObject.creditCardInstallments.on('change', function () {
        var value = jQuery(this).val();

        if (value != "" && value != 'undefined') {
            paymentMethodController.updateTotalByPaymentMethod(paymentMethodController, this);
        }
    });
};

PaymentMethodController.prototype.addSavedCreditCardsListener = function(formObject) {

    var paymentMethodController = this;
    var selector = formObject.savedCreditCardSelect.selector;
    var brand = jQuery(selector + ' option:selected').attr('brand');

    if (brand == undefined) {
        brand = formObject.creditCardBrand.val();
    }

    var formObject = formObject;
    formObject.creditCardBrand.val(brand);

    formObject.savedCreditCardSelect.on('change', function() {
        var value = jQuery(this).val();
        var brand = jQuery(selector + ' option:selected').attr('brand');

        formObject.creditCardBrand.val(brand);
        if (value === 'new') {
            jQuery(formObject.containerSelector + ' .new').show();

            if (
                typeof formObject.multibuyer != 'undefined' &&
                typeof formObject.multibuyer.showMultibuyer != 'undefined'
            ) {
                formObject.multibuyer.showMultibuyer.parent().show();
            }
            return;
        }

        paymentMethodController.fillInstallments(formObject);
        jQuery(formObject.containerSelector + ' .new').hide();

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
};

PaymentMethodController.prototype.placeOrder = function (placeOrderObject) {
    var customerValidator = new CustomerValidator(
        this.platformConfig.addresses.billingAddress
    );
    customerValidator.validate();
    var errors = customerValidator.getErrors();

    if (errors.length > 0) {
        for (id in errors) {
            this.model.addErrors(errors[id]);
        }
        return;
    }

    var isPublickKeyValid = this.validatePublicKey(
        this.platformConfig.publicKey
    );

    if (!isPublickKeyValid) {
        return;
    }

    this.model.placeOrder(placeOrderObject);
};

PaymentMethodController.prototype.updateTotal = function(interest, grandTotal, selectName) {
    var paymentMethodController = this;

    /**@fixme Move gettotals() to PlatformFormBiding */
    var total = paymentMethodController.platformConfig.updateTotals.getTotals()();
    interest = (parseInt((interest * 100).toFixed(2))) / 100;

    if (interest < 0) {
        interest = 0;
    }

    total.tax_amount = interest;
    total.base_tax_amount = interest;

    for (var i = 0, len = total.total_segments.length; i < len; i++) {
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
};

PaymentMethodController.prototype.sumInterests = function(interest, selectName) {
    var interest = interest;
    var paymentMethodController = this;

    var formObject = paymentMethodController.formObject;

    for (id in formObject) {

        if (id.length > 1 || formObject[id].creditCardInstallments == undefined) {
            continue;
        }

        var name = formObject[id].creditCardInstallments.attr('name');
        if (name == selectName) {
            continue;
        }

        var otherInterest = formObject[id].creditCardInstallments.find(':selected').attr('interest');
        if (isNaN(otherInterest)) {
            continue;
        }

        interest = parseFloat(otherInterest) + parseFloat(interest);
    }

    return interest;
}

PaymentMethodController.prototype.removeInstallmentsSelect = function (formObject) {
    var formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.removeInstallmentsSelect(formObject);
}

PaymentMethodController.prototype.showCvvCard = function (formObject) {
    var cvvElement = document.querySelector(formObject.containerSelector + " .cvv");

    if (cvvElement != undefined) {
        cvvElement.style.display = "";
    }
}

PaymentMethodController.prototype.fillInstallments = function (form) {
    var _self = this;

    if (form.creditCardBrand == undefined) {
        return;
    }

    var installmentSelected = form.creditCardInstallments.val();

    formHandler = new FormHandler();

    var selectedBrand = form.creditCardBrand.val();

    var amount = form.inputAmount.val();
    if (typeof selectedBrand == "undefined") {
        selectedBrand = 'default';
    }

    if (typeof amount == "undefined") {
        amount = 0;
    }

    var installmentsUrl =
        this.platformConfig.urls.installments + '/' +
        selectedBrand + '/' +
        amount;

    jQuery.ajax({
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
};

PaymentMethodController.prototype.fillBrandList = function (formObject, method) {
    if (method == undefined) {
        method = 'pagarme_creditcard';
    }
    var formHandler = new FormHandler();
    formHandler.fillBrandList(
        this.platformConfig.avaliableBrands[method],
        formObject
    );
};

PaymentMethodController.prototype.fillCardAmount = function (formObject, count, card = null) {
    var orderAmount = platFormConfig.updateTotals.getTotals()().grand_total / count;

    var amount = orderAmount.toFixed(this.platformConfig.currency.precision);
    var separator = ".";

    amount = amount.replace(separator, this.platformConfig.currency.decimalSeparator);

    if (card === 1) {
        var orderAmountOriginal =  amount.replace(this.platformConfig.currency.decimalSeparator, ".");
        var amountBalance = (platFormConfig.updateTotals.getTotals()().grand_total - orderAmountOriginal).toFixed(2);
        formObject.inputAmount.val(amountBalance.replace(".", this.platformConfig.currency.decimalSeparator));
        return;
    }

    formObject.inputAmount.val(amount);
};

PaymentMethodController.prototype.setBin = function (binObj, creditCardNumberElement, formObject) {

    var bin = binObj;
    var cardNumber = bin.formatNumber(creditCardNumberElement.val());

    if (cardNumber.length < 4) {
        return;
    }

    var isNewBrand = bin.validate(cardNumber);

    bin.init(cardNumber);

    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.switchBrand(bin.selectedBrand);
    if (isNewBrand) {
        this.fillInstallments(formObject);
    }

    return;
};

PaymentMethodController.prototype.limitCharacters = function (element, limit) {
    var val = element.val();

    if(val != "" && val.length > limit) {
        element.val(val.substring(0, limit));
    }
};

PaymentMethodController.prototype.clearLetters = function (element) {
    var val = element.val();
    var newVal = val.replace(/[^0-9]+/g, '');
    element.val(newVal);
};

PaymentMethodController.prototype.clearNumbers = function (element) {
    var val = element.val();
    var newVal = val.replace(/[0-9.-]+/g, '');
    element.val(newVal);
};

PaymentMethodController.prototype.hideCardAmount = function (formObject) {
    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.hideInputAmount(formObject);
};

PaymentMethodController.prototype.fillFormText = function (formObject, method = null) {
    formText = this.platformConfig.text;

    var creditCardExpYear = formObject.creditCardExpYear.val();
    var creditCardExpMonth = formObject.creditCardExpMonth.val()

    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.fillExpirationYearSelect(formText, method, creditCardExpYear);
    formHandler.fillExpirationMonthSelect(formText, method, creditCardExpMonth);
    //@Todo add other texts
};

PaymentMethodController.prototype.fillSavedCreditCardsSelect = function (formObject) {
    platformConfig = this.platformConfig;

    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.fillSavedCreditCardsSelect(platformConfig, formObject);

    if (typeof formObject.savedCreditCardSelect.selector != 'undefined') {

        selector = formObject.savedCreditCardSelect.selector;
        var brand = jQuery(selector + ' option:selected').attr('brand');

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
};

PaymentMethodController.prototype.fillMultibuyerStateSelect = function (formObject) {
    platformConfig = this.platformConfig;

    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.fillMultibuyerStateSelect(platformConfig, formObject);
};

PaymentMethodController.prototype.removeMultibuyerForm = function (formObject) {
    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.removeMultibuyerForm(formObject);
};

PaymentMethodController.prototype.addShowMultibuyerListener = function(formObject) {
    jQuery(formObject.multibuyer.showMultibuyer.selector).on('click', function () {
        formHandler.init(formObject);
        formHandler.toggleMultibuyer(formObject);
    });
};

PaymentMethodController.prototype.isTotalOnAmountInputs = function(formObject, platformConfig) {
    var orderTotal = platformConfig.updateTotals.getTotals()().grand_total;
    var card1 = formObject[0].inputAmount.val().replace(platformConfig.currency.decimalSeparator, ".");
    var card2 = formObject[1].inputAmount.val().replace(platformConfig.currency.decimalSeparator, ".");
    var totalInputs = (parseFloat(card1) + parseFloat(card2));

    return orderTotal == totalInputs;
};

PaymentMethodController.prototype.validatePublicKey = function (publicKey) {
    if (!publicKey) {
        var error =
            "Não foi possivel conectar com o serviço de pagamento. " +
            "Por favor contate o administrador da loja.";
        this.model.addErrors(error);
        return false;
    }

    return true;
};
