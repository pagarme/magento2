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
    this.formObject = FormObject.creditCardInit();
    if (!this.formObject) {
        return;
    }
    this.platformConfig = PlatformConfig.bind(this.platformConfig);
    this.model = new CreditCardModel(
        this.formObject,
        this.platformConfig.publicKey
    );
    this.fillCardAmount(this.formObject, 1);
    this.hideCardAmount(this.formObject);
    this.fillFormText(this.formObject);
    this.fillInstallments(this.formObject);
    this.fillSavedCreditCardsSelect(this.formObject);
    this.addCreditCardListeners(this.formObject);
    this.modelToken = new CreditCardToken(this.formObject);
};

PaymentMethodController.prototype.twocreditcardsInit = function () {
    this.formObject = FormObject.twoCreditCardsInit();

    if (!this.formObject) {
        return;
    }
    this.platformConfig = PlatformConfig.bind(this.platformConfig);
    this.model = new TwoCreditcardsModel(
        this.formObject,
        this.platformConfig.publicKey
    );

    if (typeof this.formObject[1] !== "undefined") {
        for (var i = 0, len = this.formObject.numberOfPaymentForms; i < len; i++) {
            this.fillCardAmount(this.formObject[i], 2);
            this.fillFormText(this.formObject[i]);
            this.fillBrandList(this.formObject[i].container);
            this.fillInstallments(this.formObject[i]);
            this.fillSavedCreditCardsSelect(this.formObject[i]);
            this.addCreditCardListeners(this.formObject[i]);
            this.addCreditCardAmountBalanceListener(this.formObject[i], i);
        }
    }

    this.modelToken = new CreditCardToken(this.formObject);
};

PaymentMethodController.prototype.boletoInit = function () {
    this.model = new BoletoModel({});
};

PaymentMethodController.prototype.addCreditCardListeners = function (formObject) {
    if (!formObject) {
        return;
    }

    this.addCreditCardNumberListener(formObject);
    this.addCreditCardInstallmentsListener(formObject);
    this.addCreditCardHolderNameListener(formObject);
};

PaymentMethodController.prototype.addCreditCardAmountBalanceListener = function(formObject, id) {
    var paymentMethodController = this;
    var id = id;

    formObject.creditCardAmount.on('change', function () {
        paymentMethodController.fillInstallments(formObject);
        var formId = paymentMethodController.model.getFormIdInverted(id);
        var form = paymentMethodController.formObject[formId];
        paymentMethodController.fillInstallments(form);
    });

    formObject.creditCardAmount.on('keyup', function(){
        element = jQuery(this);

        var orderAmount = paymentMethodController.platformConfig.orderAmount;
        orderAmount = orderAmount.replace(/[^0-9]/g, '');
        orderAmount = Number(orderAmount);

        var value = element.val();
        value = value.replace(/[^0-9]/g, '');
        value = Number(value);

        if (value > orderAmount) {
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

        form.creditCardAmount.val(remaining.toString().replace('.', paymentMethodController.platformConfig.currency.decimalSeparator));
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

PaymentMethodController.prototype.addCreditCardInstallmentsListener = function(formObject) {

    var paymentMethodController = this;

    formObject.creditCardInstallments.on('change', function() {
        var value = jQuery(this).val();
        if (value != "" && value != 'undefined') {
            var interest = jQuery(this).find(':selected').attr("interest");
            paymentMethodController.updateTotal(
                interest,
                jQuery(this).attr('name')
            );
        }
    });
};

PaymentMethodController.prototype.placeOrder = function (placeOrderObject) {
    var errors = this.validateAddress();
    if (errors.length > 0) {
        for (id in errors) {
            this.model.addErrors(errors[id]);
        }
        return;
    }
    this.model.placeOrder(placeOrderObject);
};

PaymentMethodController.prototype.validateAddress = function () {
    var errors = [];
    var address = this.platformConfig.addresses.billingAddress;

    if (address.vatId <= 0) {
        errors.push("VatId não informado");
    }

    if (address.street.length < 3) {
        errors.push("Endereço invalido");
    }

    return errors;
}

PaymentMethodController.prototype.updateTotal = function(interest, selectName) {
    var paymentMethodController = this;

    if (paymentMethodController.formObject.numberOfPaymentForms > 1) {
        interest = this.sumInterests(interest, selectName);
    }

    var total = paymentMethodController.platformConfig.totals;
    total.tax_amount = parseFloat(interest);
    total.base_tax_amount = parseFloat(interest);

    for (var i = 0, len = total.total_segments.length; i < len; i++) {
        if (total.total_segments[i].code === "grand_total") {
            total.total_segments[i].value = parseFloat(total.base_grand_total) + parseFloat(interest)
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

        if (id.length > 1) {
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

PaymentMethodController.prototype.fillInstallments = function (form) {
    var _self = this;

    _self.platformConfig.loader.start();
    formHandler = new FormHandler();

    var defaulOption = [{
        'id' : 0,
        'interest' : 0,
        'label' : 'Selecione'
    }];
    var selectedBrand = form.creditCardBrand.val();

    var amount = form.creditCardAmount.val();
    if (typeof selectedBrand == "undefined") {
        selectedBrand = 'default';

    }
    if (typeof amount == "undefined") {
        amount = 0;

    }
    formHandler.updateInstallmentSelect(defaulOption, form.creditCardInstallments);

    var installmentsUrl =
        this.platformConfig.urls.installments + '/' +
        selectedBrand + '/' +
        amount;
    jQuery.ajax({
        url: installmentsUrl,
        method: 'GET',
        cache: true
    }).done(function(data) {
        formHandler = new FormHandler();
        formHandler.updateInstallmentSelect(data, form.creditCardInstallments);
        _self.platformConfig.loader.stop();
    });
};

PaymentMethodController.prototype.fillBrandList = function (formContainer) {
    formHandler = new FormHandler();
    formHandler.fillBrandList(
        formContainer,
        this.platformConfig.avaliableBrands
    );
};

PaymentMethodController.prototype.fillCardAmount = function (formObject, count) {
    var orderAmount = this.platformConfig.orderAmount / count;

    var amount = orderAmount.toFixed(this.platformConfig.currency.precision);
    var separator = ".";

    amount = amount.replace(separator, this.platformConfig.currency.decimalSeparator);

    formObject.creditCardAmount.val(amount);
};

PaymentMethodController.prototype.setBin = function (binObj, creditCardNumberElement, formObject) {
    var bin = binObj;
    var cardNumber = bin.formatNumber(creditCardNumberElement.val());
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
    var newVal = val.replace(/[0-9]+/g, '');
    element.val(newVal);
};

PaymentMethodController.prototype.hideCardAmount = function (formObject) {
    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.hideCreditCardAmount();
};

PaymentMethodController.prototype.fillFormText = function (formObject) {
    formText = this.platformConfig.text;

    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.fillExpirationYearSelect(formText);
    formHandler.fillExpirationMonthSelect(formText);
    //@Todo add other texts
};

PaymentMethodController.prototype.fillSavedCreditCardsSelect = function (formObject) {
    platformConfig = this.platformConfig;

    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.fillSavedCreditCardsSelect(platformConfig, formObject);
};