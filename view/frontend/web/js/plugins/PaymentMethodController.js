var PaymentMethodController = function (methodCode) {
   this.methodCode = methodCode;
};

PaymentMethodController.prototype.init = function () {
    var paymentMethodInit = this.methodCode + 'Init';

    this[paymentMethodInit]();
};

PaymentMethodController.prototype.creditCardInit = function () {
    var formObject = FormObject.creditCardInit();

    this.addCreditCardListeners(formObject);
};

PaymentMethodController.prototype.addCreditCardListeners = function (formObject) {

    bin = new Bin();
    formHandler = new FormHandler();

    formObject.creditCardNumber.on('keyup', function () {
        bin.init(jQuery(this).val());
        formHandler.init(formObject);
        formHandler.switchBrand(bin.selectedBrand);
    });

    formObject.creditCardNumber.on('change', function () {
        bin.init(jQuery(this).val());
    });
};