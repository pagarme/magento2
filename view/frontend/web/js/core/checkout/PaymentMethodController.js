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
            this.addCreditCardListeners(this.formObject[i]);
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
        element.change();
    });

    formObject.creditCardNumber.on('change', function () {
        var element = jQuery(this);

        setTimeout(function() {
            paymentMethodController.setBin(binObj,  element, formObject);
        }, 1300);
        //@Todo
        //installments
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
    this.model.placeOrder(placeOrderObject);
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

    paymentMethodController.platformConfig.quote.setTotals(total);

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

    if (isNewBrand) {
        //@Todo
        //Call update installments sending the credit card brand
        //this.fillInstallments(this.formObject[1]);
    }
    formHandler = new FormHandler();
    formHandler.init(formObject);
    formHandler.switchBrand(bin.selectedBrand);

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