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
        _self.formObject.savedCreditCardSelect.html().length > 1 &&
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

CreditCardModel.prototype.validate = function () {

    var creditCardValidator = new CreditCardValidator(this.formObject);
    var isCreditCardValid = creditCardValidator.validate();

    var multibuyerValidator = new MultibuyerValidator(this.formObject);
    var isMultibuyerValid = multibuyerValidator.validate();

    if (isCreditCardValid && isMultibuyerValid) {
        return true;
    }

    return false;
};

CreditCardModel.prototype.getCreditCardToken = function (success, error) {
    var modelToken = new CreditCardToken(this.formObject);
    modelToken.getToken(this.publicKey)
        .done(success)
        .fail(error);
};

CreditCardModel.prototype.getData = function () {
    saveThiscard = 0;
    var formObject = this.formObject;

    if (formObject.saveThisCard.prop( "checked" )) {
        saveThiscard = 1;
    }

    data = this.fillData();
    data.additional_data.cc_buyer_checkbox = false;

    if (
        typeof formObject.multibuyer != 'undefined' &&
        formObject.multibuyer.showMultibuyer.prop( "checked" ) == true
    ) {
        data = this.fillMultibuyerData(data);
    }

    return data;
};

CreditCardModel.prototype.fillData = function() {
    var formObject = this.formObject;

    return {
        'method': "pagarme_creditcard",
        'additional_data': {
            'cc_type': formObject.creditCardBrand.val(),
            'cc_last_4': this.getLastFourNumbers(),
            'cc_exp_year': formObject.creditCardExpYear.val(),
            'cc_exp_month': formObject.creditCardExpMonth.val(),
            'cc_owner': formObject.creditCardHolderName.val(),
            'cc_savecard': saveThiscard,
            'cc_saved_card': formObject.savedCreditCardSelect.val(),
            'cc_installments': formObject.creditCardInstallments.val(),
            'cc_token_credit_card': formObject.creditCardToken.val(),
            'cc_card_tax_amount' : formObject.creditCardInstallments.find(':selected').attr('interest')
        }
    };
};

CreditCardModel.prototype.fillMultibuyerData = function(data) {
    multibuyer = this.formObject.multibuyer;
    fullname = multibuyer.firstname.val() + ' ' + multibuyer.lastname.val();

    data.additional_data.cc_buyer_checkbox = 1,
    data.additional_data.cc_buyer_name = fullname,
    data.additional_data.cc_buyer_email = multibuyer.email.val(),
    data.additional_data.cc_buyer_document = multibuyer.document.val(),
    data.additional_data.cc_buyer_street_title = multibuyer.street.val(),
    data.additional_data.cc_buyer_street_number = multibuyer.number.val(),
    data.additional_data.cc_buyer_street_complement = multibuyer.complement.val(),
    data.additional_data.cc_buyer_zipcode = multibuyer.zipcode.val(),
    data.additional_data.cc_buyer_neighborhood = multibuyer.neighborhood.val(),
    data.additional_data.cc_buyer_city = multibuyer.city.val(),
    data.additional_data.cc_buyer_state = multibuyer.state.val(),
    data.additional_data.cc_buyer_home_phone = multibuyer.homePhone.val(),
    data.additional_data.cc_buyer_mobile_phone = multibuyer.mobilePhone.val()

    return data;
};

CreditCardModel.prototype.getLastFourNumbers = function() {
    var number = this.formObject.creditCardNumber.val();
    if (number !== undefined) {
        return number.slice(-4);
    }
    return "";
};
