var PaymentMethodController = function (methodCode) {
   this.methodCode = methodCode;
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

PaymentMethodController.prototype.creditCardInit = function () {
    this.formObject = FormObject.creditCardInit();

    this.addCreditCardListeners(this.formObject);
};

PaymentMethodController.prototype.addCreditCardListeners = function (formObject) {
    bin = new Bin();
    formHandler = new FormHandler();

    formObject.creditCardNumber.on('keyup', function () {
        setTimeout(function(){
            bin.init(formObject.creditCardNumber.val());
            formHandler.init(formObject);
            formHandler.switchBrand(bin.selectedBrand);
        }, 1300);
    });

    formObject.creditCardNumber.on('change', function () {
        bin.init(jQuery(this).val());
    });
};

/**
 * @todo Move other validations from platform to here
 */
PaymentMethodController.prototype.creditCardValidation = function () {
    if (
        typeof this.formObject != 'undefined' &&
        typeof this.formObject.creditCardBrand.val() != 'undefined' &&
        this.formObject.creditCardBrand.val().length > 0
    ) {
        return true
    }

    console.log("formObject not found!");
    return false;
};