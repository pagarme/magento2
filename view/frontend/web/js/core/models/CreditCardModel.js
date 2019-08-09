var CreditCardModel = function (formObject, publicKey) {
    this.formObject = formObject;
    this.publicKey = publicKey;

};

CreditCardModel.prototype.placeOrder = function (placeOrderObject) {
    this.placeOrderObject = placeOrderObject;
    var _self = this;

    this.getCreditCardToken(
        function (data) {
            _self.formObject.creditCardToken.val(data.id);
            _self.placeOrderObject.placeOrder();
        },
        function (error) {
            var errors = error.responseJSON;
            _self.placeOrderObject.platformObject.messageContainer.addErrorMessage(errors);
        }
    );
};

CreditCardModel.prototype.creditCardValidation = function () {

    if (typeof this.formObject == "undefined") {
        return false;
    }

    var isValid = true;
    var cardBrand = this.formObject.creditCardBrand.val();

    if (typeof cardBrand == "undefined" || cardBrand.length <= 0 ) {
        isValid = false;
    }

    return isValid;
};

CreditCardModel.prototype.getCreditCardToken = function (success, error) {
    var modelToken = new CreditCardToken(this.formObject);
    var _self = this;
    if (this.creditCardValidation()) {
        modelToken.getToken(_self.publicKey)
            .done(success)
            .fail(error);
    }
};


CreditCardModel.prototype.getData = function () {
    return {
        'method': "mundipagg_creditcard",
        'additional_data': {
            'cc_type': this.formObject.creditCardBrand.val(),
            'cc_last_4': this.getLastFourNumbers(),
            'cc_exp_year': this.formObject.creditCardExpYear.val(),
            'cc_exp_month': this.formObject.creditCardExpMonth.val(),
            'cc_owner': this.formObject.creditCardHolderName.val(),
            'cc_savecard': 0,
            'cc_saved_card': 0,
            'cc_installments': this.formObject.creditCardInstallments.val(),
            'cc_token_credit_card': this.formObject.creditCardToken.val(),
        }
    };
};

CreditCardModel.prototype.getLastFourNumbers = function() {
    var number = this.formObject.creditCardNumber.val();
    return number.slice(-4);
}
