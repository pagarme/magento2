var TwoCreditcardsModel= function (formObject, publicKey) {
    this.formObject = formObject;
    this.publicKey = publicKey;
    this.modelToken = new CreditCardToken(this.formObject);
    this.errors = [];
    this.formIds = [0, 1];
};

TwoCreditcardsModel.prototype.placeOrder = function (placeOrderObject) {
    this.placeOrderObject = placeOrderObject;
    var _self = this;
    var errors = false;

    for (id in this.formObject) {

        if (id.length > 1) {
            continue;
        }

        if (
            typeof this.formObject[id].savedCreditCardSelect.val() != 'undefined' &&
            this.formObject[id].savedCreditCardSelect.val() != 'new' &&
            this.formObject[id].savedCreditCardSelect.val() != ''
        ) {
            continue;
        }

        this.getCreditCardToken(
            this.formObject[id],
            function (data) {
                _self.formObject[id].creditCardToken.val(data.id);
            },
            function (error) {
                errors = true;
                _self.addErrors("Cartão inválido. Por favor, verifique os dados digitados e tente novamente");
            }
        );
    }

    if (!errors) {
        _self.placeOrderObject.placeOrder();
    }
};

TwoCreditcardsModel.prototype.getFormIdInverted = function (id) {
    var ids = this.formIds.slice(0);
    var index = ids.indexOf(id);
    ids.splice(index, 1);

    return ids[0];
}

TwoCreditcardsModel.prototype.addErrors = function (error) {
    this.errors.push({
        message: error
    })
}

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
    saveThiscard = [];

    saveThiscard[0] = 0;
    saveThiscard[1] = 0;

    if (this.formObject[0].saveThisCard.prop('checked')=== 'on') {
        saveThiscard[0] = 1;
    }

    if (this.formObject[1].saveThisCard.prop('checked') === 'on') {
        saveThiscard[1] = 1;
    }

    return {
        'method': "mundipagg_two_creditcard",
        'additional_data': {
            //first
            'cc_first_card_amount': this.formObject[0].creditCardAmount.val(),
            'cc_first_card_tax_amount': this.formObject[0].creditCardInstallments.find(':selected').attr('interest'),
            'cc_type_first': this.formObject[0].creditCardBrand.val(),
            'cc_last_4_first': this.getLastFourNumbers(0),
            'cc_cid_first': this.formObject[0].creditCardCvv.val(),
            'cc_exp_year_first': this.formObject[0].creditCardExpYear.val(),
            'cc_exp_month_first': this.formObject[0].creditCardExpMonth.val(),
            'cc_number_first': this.formObject[0].creditCardNumber.val(),
            'cc_owner_first': this.formObject[0].creditCardHolderName.val(),
            'cc_savecard_first' : saveThiscard[0],
            'cc_saved_card_first' : this.formObject[0].savedCreditCardSelect.val(),
            'cc_installments_first': this.formObject[0].creditCardInstallments.val(),
            'cc_token_credit_card_first' : this.formObject[0].creditCardToken.val(),
            //second
            'cc_second_card_amount': this.formObject[1].creditCardAmount.val(),
            'cc_second_card_tax_amount': this.formObject[1].creditCardInstallments.find(':selected').attr('interest'),
            'cc_type_second': this.formObject[1].creditCardBrand.val(),
            'cc_last_4_second': this.getLastFourNumbers(1),
            'cc_cid_second': this.formObject[1].creditCardCvv.val(),
            'cc_exp_year_first': this.formObject[1].creditCardExpYear.val(),
            'cc_exp_month_second': this.formObject[1].creditCardExpMonth.val(),
            'cc_number_second': this.formObject[1].creditCardNumber.val(),
            'cc_owner_second': this.formObject[1].creditCardHolderName.val(),
            'cc_savecard_first' : saveThiscard[1],
            'cc_saved_card_second' : this.formObject[1].savedCreditCardSelect.val(),
            'cc_installments_second': this.formObject[1].creditCardInstallments.val(),
            'cc_token_credit_card_second' : this.formObject[1].creditCardToken.val(),
        }
    };
};

TwoCreditcardsModel.prototype.getLastFourNumbers = function(id) {
    var number = this.formObject[id].creditCardNumber.val();
    return number.slice(-4);
}