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
    this.addCreditCardListeners(this.formObject);
    this.plarformConfig = PlarformConfig.bind(this.plarformConfig);
    this.modelToken = new CreditCardToken(this.formObject);
};

PaymentMethodController.prototype.twocreditcardsInit = function () {
    this.formObject = FormObject.twoCreditCardsInit();
    this.plarformConfig = PlarformConfig.bind(this.plarformConfig);
    this.fillCardAmount();
};

PaymentMethodController.prototype.boletoInit = function () {
};

PaymentMethodController.prototype.initBin = function (obj) {
    if (this.methodCode != 'creditcard') {
        return;
    }
    this.addCreditCardListeners(FormObject.creditCardInit(), obj)
}

PaymentMethodController.prototype.addCreditCardListeners = function (formObject, obj) {
    bin = new Bin();
    formHandler = new FormHandler();
    installments = new Installments();

    formObject.creditCardNumber.on('keyup', function () {
        setTimeout(function(){
            var cardNumber = bin.formatNumber(formObject.creditCardNumber.val());

            var isNewBrand = bin.validate(cardNumber);

            bin.init(cardNumber);

            if (isNewBrand) {
                obj.getInstallmentsByBrand(
                    bin.selectedBrand,
                    installments.addOptions
                );
            }

            formHandler.init(formObject);
            formHandler.switchBrand(bin.selectedBrand);

        }, 1300);
    });

    formObject.creditCardInstallments.on('change', function() {
        var value = jQuery(this).val();
        if (value != "" && value != 'undefined') {
            var interest = jQuery(this).find(':selected').attr("interest");
            obj.updateTotalWithTax(interest);
        }
    })

    formObject.creditCardNumber.on('change', function () {
        bin.init(jQuery(this).val());
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


// @todo Mover to another class

PaymentMethodController.prototype.getCreditCardToken = function (pkKey, success, error) {

    if (this.creditCardValidation()) {
        this.modelToken
            .getToken(pkKey)
            .done(success)
            .fail(error);
    }
}

PaymentMethodController.prototype.fillCardAmount = function () {
    var orderAmount = this.plarformConfig.orderAmount / 2;

    var amount = orderAmount.toFixed(this.plarformConfig.currency.precision);
    this.formObject[0].creditCardAmount.val(amount);
    this.formObject[1].creditCardAmount.val(amount);
}