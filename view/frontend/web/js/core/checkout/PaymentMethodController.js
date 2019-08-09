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
    this.fillCardAmount(this.formObject[0], 2);
    this.fillCardAmount(this.formObject[1], 2);
    this.fillFormText(this.formObject[0]);
    this.fillFormText(this.formObject[1]);
    this.fillBrandList(this.formObject[0].container);
    this.fillBrandList(this.formObject[1].container);
    this.fillInstallments(this.formObject[0]);
    this.fillInstallments(this.formObject[1]);
    this.addCreditCardListeners(this.formObject[0]);
    this.addCreditCardListeners(this.formObject[1]);

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
};

PaymentMethodController.prototype.addCreditCardNumberListener = function(formObject) {

    var paymentMethodController = this;

    formObject.creditCardNumber.on('keydown', function () {
        element = jQuery(this);
        paymentMethodController.limitCharacters(element, 19);
    });

    var binObj = new Bin();

    formObject.creditCardNumber.on('keyup', function () {
        jQuery(this).change();
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
            paymentMethodController.updateTotal(interest);
        }
    });
};

PaymentMethodController.prototype.placeOrder = function (placeOrderObject) {
    this.model.placeOrder(placeOrderObject);
}

PaymentMethodController.prototype.updateTotal = function(interest) {
    var paymentMethodController = this;

};



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