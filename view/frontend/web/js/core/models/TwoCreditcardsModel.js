var TwoCreditcardsModel= function (formObject, publicKey) {
    this.formObject = formObject;
    this.publicKey = publicKey;
    this.modelToken = new CreditCardToken(this.formObject);
};

TwoCreditcardsModel.prototype.placeOrder = function (placeOrderObject) {
    this.placeOrderObject = placeOrderObject;
    var _self = this;

    for (id in this.formObject) {

        if (id.length > 1) {
            continue;
        }

        this.getCreditCardToken(
            this.formObject[id],
            function (data) {
                _self.formObject[id].creditCardToken.val(data.id);
            },
            function (error) {
                var errors = error.responseJSON.errors;

                errors.forEach((value, key) => {
                    _self.placeOrderObject.platformObject.messageContainer.addErrorMessage({
                        message: value
                    });
                });

            }
        );
    }

    _self.placeOrderObject.placeOrder();
};


TwoCreditcardsModel.prototype.getCreditCardToken = function (formObject, success, error) {
    var modelToken = new CreditCardToken(formObject);
    var _self = this;
    if (this.creditCardValidation(formObject)) {
        modelToken.getToken(_self.publicKey)
            .done(success)
            .fail(error);
    }
};

TwoCreditcardsModel.prototype.creditCardValidation = function (formObject) {

    if (typeof formObject == "undefined") {
        return false;
    }

    var isValid = true;
    var cardBrand = formObject.creditCardBrand.val();

    if (typeof cardBrand == "undefined" || cardBrand.length <= 0 ) {
        isValid = false;
    }

    return isValid;
};

TwoCreditcardsModel.prototype.getData = function () {
    return {
        'method': "mundipagg_two_creditcard",
        'additional_data': {
            //first
            'cc_first_card_amount': this.formObject[0].creditCardAmount.val(),
            'cc_first_card_tax_amount': this.formObject[0].creditCardInstallments.attr('interest'),
            'cc_type_first': this.formObject[0].creditCardBrand.val(),
            'cc_last_4_first': this.getLastFourNumbers(0),
            'cc_cid_first': this.formObject[0].creditCardCvv.val(),
            'cc_exp_year_first': this.formObject[0].creditCardExpYear.val(),
            'cc_exp_month_first': this.formObject[0].creditCardExpMonth.val(),
            'cc_number_first': this.formObject[0].creditCardNumber.val(),
            'cc_owner_first': this.formObject[0].creditCardHolderName.val(),
            'cc_savecard_first' : 0,
            'cc_saved_card_first' : 0,
            'cc_installments_first': this.formObject[0].creditCardInstallments.val(),
            'cc_token_credit_card_first' : this.formObject[0].creditCardToken.val(),
            //second
            'cc_second_card_amount': this.formObject[1].creditCardAmount.val(),
            'cc_second_card_tax_amount': this.formObject[1].creditCardInstallments.attr('interest'),
            'cc_type_second': this.formObject[1].creditCardBrand.val(),
            'cc_last_4_second': this.getLastFourNumbers(1),
            'cc_cid_second': this.formObject[1].creditCardCvv.val(),
            'cc_exp_year_first': this.formObject[1].creditCardExpYear.val(),
            'cc_exp_month_second': this.formObject[1].creditCardExpMonth.val(),
            'cc_number_second': this.formObject[1].creditCardNumber.val(),
            'cc_owner_second': this.formObject[1].creditCardHolderName.val(),
            'cc_savecard_second' : 0,
            'cc_saved_card_second' : 0,
            'cc_installments_second': this.formObject[1].creditCardInstallments.val(),
            'cc_token_credit_card_second' : this.formObject[1].creditCardToken.val(),
        }
    };
};


TwoCreditcardsModel.prototype.getLastFourNumbers = function(id) {
    var number = this.formObject[id].creditCardNumber.val();
    return number.slice(-4);
}