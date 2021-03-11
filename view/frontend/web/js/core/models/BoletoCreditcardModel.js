var BoletoCreditcardModel= function (formObject, publicKey) {
    this.formObject = formObject;
    this.publicKey = publicKey;
    this.modelToken = new CreditCardToken(this.formObject);
    this.errors = [];
    this.formIds = [0, 1];
};

BoletoCreditcardModel.prototype.placeOrder = function (placeOrderObject) {
    this.placeOrderObject = placeOrderObject;
    var _self = this;
    var errors = false;

    for (id in this.formObject) {

        if (id != 1) {
            continue;
        }

        if (
            typeof this.formObject[id].savedCreditCardSelect.val() != 'undefined' &&
            this.formObject[id].savedCreditCardSelect.val() != 'new' &&
            this.formObject[id].savedCreditCardSelect.val() != '' &&
            this.formObject[id].savedCreditCardSelect.html().length > 1
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

BoletoCreditcardModel.prototype.getFormIdInverted = function (id) {
    var ids = this.formIds.slice(0);
    var index = ids.indexOf(id);
    ids.splice(index, 1);

    return ids[0];
}

BoletoCreditcardModel.prototype.addErrors = function (error) {
    this.errors.push({
        message: error
    })
}

BoletoCreditcardModel.prototype.getCreditCardToken = function (formObject, success, error) {
    var modelToken = new CreditCardToken(formObject);
    modelToken.getToken(this.publicKey)
        .done(success)
        .fail(error);
};

BoletoCreditcardModel.prototype.validate = function () {

    var formsInvalid = [];

    for (id in this.formObject) {

        if (id.length > 1) {
            continue;
        }
        var multibuyerValidator = new MultibuyerValidator(this.formObject[id]);
        var isMultibuyerValid = multibuyerValidator.validate();

        if (isMultibuyerValid) {
            continue;
        }

        formsInvalid.push(true);
    }

    var creditCardValidator = new CreditCardValidator(this.formObject[1]);
    var isCreditCardValid = creditCardValidator.validate();

    formsInvalid.push(!isCreditCardValid);

    var hasFormInvalid = formsInvalid.filter(function (item) {
        return item;
    });

    if (hasFormInvalid.length > 0) {
        return false;
    }

    return true;
};

BoletoCreditcardModel.prototype.getData = function () {

    saveThiscard = 0;

    if (this.formObject[1].saveThisCard.prop('checked') === 'on') {
        saveThiscard = 1;
    }

    var data = {
        'method': "pagarme_billet_creditcard",
        'additional_data': {
            //boleto
            'cc_billet_amount': this.formObject[0].inputAmount.val(),
            //credit_card
            'cc_cc_amount': this.formObject[1].inputAmount.val(),
            'cc_cc_tax_amount': this.formObject[1].creditCardInstallments.find(':selected').attr('interest'),
            'cc_type': this.formObject[1].creditCardBrand.val(),
            'cc_last_4': this.getLastFourNumbers(1),
            'cc_cid': this.formObject[1].creditCardCvv.val(),
            'cc_ss_start_year': this.formObject[1].creditCardExpYear.val(),
            'cc_ss_start_month': this.formObject[1].creditCardExpMonth.val(),
            'cc_number': this.formObject[1].creditCardNumber.val(),
            'cc_owner': this.formObject[1].creditCardHolderName.val(),
            'cc_savecard': saveThiscard,
            'cc_saved_card': this.formObject[1].savedCreditCardSelect.val(),
            'cc_installments': this.formObject[1].creditCardInstallments.val(),
            'cc_token_credit_card': this.formObject[1].creditCardToken.val(),
        }
    }

    if (
        typeof this.formObject[0].multibuyer != 'undefined' &&
        typeof this.formObject[0].multibuyer.showMultibuyer != 'undefined' &&
        this.formObject[0].multibuyer.showMultibuyer.prop( "checked" ) == true
    ) {
        multibuyer = this.formObject[0].multibuyer;
        fullName = multibuyer.firstname.val() + ' ' + multibuyer.lastname.val();

        data.additional_data.billet_buyer_checkbox = 1;
        data.additional_data.billet_buyer_name = fullName;
        data.additional_data.billet_buyer_email = multibuyer.email.val();
        data.additional_data.billet_buyer_document = multibuyer.document.val();
        data.additional_data.billet_buyer_street_title = multibuyer.street.val();
        data.additional_data.billet_buyer_street_number = multibuyer.number.val();
        data.additional_data.billet_buyer_street_complement = multibuyer.complement.val();
        data.additional_data.billet_buyer_zipcode = multibuyer.zipcode.val();
        data.additional_data.billet_buyer_neighborhood = multibuyer.neighborhood.val();
        data.additional_data.billet_buyer_city = multibuyer.city.val();
        data.additional_data.billet_buyer_state = multibuyer.state.val();
        data.additional_data.billet_buyer_home_phone = multibuyer.homePhone.val();
        data.additional_data.billet_buyer_mobile_phone = multibuyer.mobilePhone.val();
    }

    if (
        typeof this.formObject[1].multibuyer != 'undefined' &&
        typeof this.formObject[1].multibuyer.showMultibuyer != 'undefined' &&
        this.formObject[1].multibuyer.showMultibuyer.prop( "checked" ) == true
    ) {
        multibuyer = this.formObject[1].multibuyer;
        fullName = multibuyer.firstname.val() + ' ' + multibuyer.lastname.val();

        data.additional_data.cc_buyer_checkbox = 1;
        data.additional_data.cc_buyer_name = fullName;
        data.additional_data.cc_buyer_email = multibuyer.email.val();
        data.additional_data.cc_buyer_document = multibuyer.document.val();
        data.additional_data.cc_buyer_street_title = multibuyer.street.val();
        data.additional_data.cc_buyer_street_number = multibuyer.number.val();
        data.additional_data.cc_buyer_street_complement = multibuyer.complement.val();
        data.additional_data.cc_buyer_zipcode = multibuyer.zipcode.val();
        data.additional_data.cc_buyer_neighborhood = multibuyer.neighborhood.val();
        data.additional_data.cc_buyer_city = multibuyer.city.val();
        data.additional_data.cc_buyer_state = multibuyer.state.val();
        data.additional_data.cc_buyer_home_phone = multibuyer.homePhone.val();
        data.additional_data.cc_buyer_mobile_phone = multibuyer.mobilePhone.val();
    }

    return data;
};

BoletoCreditcardModel.prototype.getLastFourNumbers = function(id) {
    var number = this.formObject[id].creditCardNumber.val();
    if (number !== undefined) {
        return number.slice(-4);
    }
    return "";
}
