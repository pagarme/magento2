var PaymentMethodController = function (methodCode, plarformConfig) {
   this.methodCode = methodCode;
   this.plarformConfig = plarformConfig;
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
    this.plarformConfig = PlarformConfig.bind(this.plarformConfig);
    this.fillInstallments(this.formObject);
    this.addCreditCardListeners(this.formObject);
    this.modelToken = new CreditCardToken(this.formObject);
};

PaymentMethodController.prototype.twocreditcardsInit = function () {
    this.formObject = FormObject.twoCreditCardsInit();

    if (!this.formObject) {
        return;
    }
    this.plarformConfig = PlarformConfig.bind(this.plarformConfig);
    this.fillCardAmount();
    this.fillBrandList(this.formObject[0].container);
    this.fillBrandList(this.formObject[1].container);
    this.fillInstallments(this.formObject[0]);
    this.fillInstallments(this.formObject[1]);
};

PaymentMethodController.prototype.boletoInit = function () {
};

PaymentMethodController.prototype.addCreditCardListeners = function (formObject, obj) {
    if (!formObject) {
        return;
    }

    this.addCreditCardNumberListener(formObject);
    this.addCreditCardInstallmentsListener(formObject);
};

PaymentMethodController.prototype.addCreditCardNumberListener = function(formObject) {

    var paymentMethodController = this;

    formObject.creditCardNumber.on('keydown', function () {
        var element = jQuery(this);
        paymentMethodController.limitCharacters(element, 19);
    });

    var binObj = new Bin();

    formObject.creditCardNumber.on('keyup', function () {
        jQuery(this).change();
    });

    formObject.creditCardNumber.on('change', function () {
        var element = jQuery(this);

        setTimeout(function() {
            paymentMethodController.setBin(binObj,  element);
        }, 1300);
        //installments
    }).bind(this);
};

PaymentMethodController.prototype.addCreditCardInstallmentsListener = function(formObject) {
    formObject.creditCardInstallments.on('change', function() {
        var value = jQuery(this).val();
        if (value != "" && value != 'undefined') {
            var interest = jQuery(this).find(':selected').attr("interest");
            obj.updateTotalWithTax(interest);
        }
    });
};

/**
 * @todo Move other validations from platform to here
 */
PaymentMethodController.prototype.creditCardValidation = function () {
    if (
        typeof this.formObject != "undefined" &&
        typeof this.formObject.creditCardBrand.val() != "undefined" &&
        this.formObject.creditCardBrand.val().length > 0
    ) {
        return true
    }

    return false;
};

// @todo Move to another class
PaymentMethodController.prototype.getCreditCardToken = function (pkKey, success, error) {

    if (this.creditCardValidation()) {
        this.modelToken
            .getToken(pkKey)
            .done(success)
            .fail(error);
    }
};

PaymentMethodController.prototype.fillInstallments = function (form) {
    formHandler = new FormHandler();

    var defaulOption = [{
        'id' : 0,
        'interest' : 0,
        'label' : 'Selecione'
    }];

    formHandler.updateInstallmentSelect(defaulOption, form.creditCardInstallments);
    var installmentsUrl = this.plarformConfig.urls.installments;

    jQuery.ajax({
        url: installmentsUrl,
        method: 'POST',
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
        this.plarformConfig.avaliableBrands
    );
};

PaymentMethodController.prototype.fillCardAmount = function () {
    var orderAmount = this.plarformConfig.orderAmount / 2;

    var amount = orderAmount.toFixed(this.plarformConfig.currency.precision);
    var separator = ".";

    amount = amount.replace(separator, this.plarformConfig.currency.decimalSeparator);

    this.formObject[0].creditCardAmount.val(amount);
    this.formObject[1].creditCardAmount.val(amount);
};

PaymentMethodController.prototype.setBin = function (binObj, creditCardNumberElement) {
    var bin = binObj;

    var cardNumber = bin.formatNumber(creditCardNumberElement.val());
    var isNewBrand = bin.validate(cardNumber);

    bin.init(cardNumber);

    if (isNewBrand) {
        /*obj.getInstallmentsByBrand(
            bin.selectedBrand,
            installments.addOptions
        );*/
    }

    formHandler = new FormHandler();
    formHandler.init(this.formObject);
    formHandler.switchBrand(bin.selectedBrand);
}

PaymentMethodController.prototype.limitCharacters = function (element, limit) {
    var val = element.val();

    if(val != "" && val.length > 19) {
        element.val(val.substring(0, 19));
    }
}