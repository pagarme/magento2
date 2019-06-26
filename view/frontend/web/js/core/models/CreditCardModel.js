var CreditCardModel = function () {
    this.formObject = ''
};

CreditCardModel.prototype.init = function () {
    this.formObject = FormObject.creditCardInit();

    this.modelToken = new CreditCardToken(this.formObject);

    this.addCreditCardListeners();
};

CreditCardModel.prototype.addCreditCardListeners = function () {
    bin = new Bin();
    formHandler = new FormHandler();

    this.formObject.creditCardNumber.on('keyup', function () {
        setTimeout(function(){
            bin.init(formObject.creditCardNumber.val());
            formHandler.init(formObject);
            formHandler.switchBrand(bin.selectedBrand);
        }, 1300);
    });

    this.formObject.creditCardNumber.on('change', function () {
        bin.init(jQuery(this).val());
    });
};
