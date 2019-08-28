var CreditCardModel = function (formObject, publicKey) {
    this.formObject = formObject;
    this.publicKey = publicKey;
    this.errors = [];

};

CreditCardModel.prototype.placeOrder = function (placeOrderObject) {

    this.placeOrderObject = placeOrderObject;
    var _self = this;

    if (
        typeof _self.formObject.savedCreditCardSelect.val() != 'undefined' &&
        _self.formObject.savedCreditCardSelect.val() != 'new' &&
        _self.formObject.savedCreditCardSelect.val() != ''
    ) {
        _self.placeOrderObject.placeOrder();
        return;
    }

    this.getCreditCardToken(
        function (data) {
            _self.formObject.creditCardToken.val(data.id);
            _self.placeOrderObject.placeOrder();
        },
        function (error) {
            var errors = error.responseJSON;
            _self.addErrors("Cartão inválido. Por favor, verifique os dados digitados e tente novamente");
        }
    );
};

CreditCardModel.prototype.addErrors = function (error) {
    this.errors.push({
        message: error
    })
}

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

    saveThiscard = 0;

    if (this.formObject.saveThisCard.prop( "checked" )) {
        saveThiscard = 1;
    }

    return {
        'method': "mundipagg_creditcard",
        'additional_data': {
            'cc_type': this.formObject.creditCardBrand.val(),
            'cc_last_4': this.getLastFourNumbers(),
            'cc_exp_year': this.formObject.creditCardExpYear.val(),
            'cc_exp_month': this.formObject.creditCardExpMonth.val(),
            'cc_owner': this.formObject.creditCardHolderName.val(),
            'cc_savecard': saveThiscard,
            'cc_saved_card': this.formObject.savedCreditCardSelect.val(),
            'cc_installments': this.formObject.creditCardInstallments.val(),
            'cc_token_credit_card': this.formObject.creditCardToken.val(),
            'cc_card_tax_amount' : this.formObject.creditCardInstallments.find(':selected').attr('interest'),
        }
    };
};

CreditCardModel.prototype.getLastFourNumbers = function() {
    var number = this.formObject.creditCardNumber.val();
    return number.slice(-4);
}
