var CreditCardValidator = function (formObject) {
    this.formObject = formObject;
};

CreditCardValidator.prototype.validate = function () {
    if (
        typeof this.formObject.savedCreditCardSelect != 'undefined' &&
        this.formObject.savedCreditCardSelect.html().length > 1 &&
        this.formObject.savedCreditCardSelect.val() !== 'new'
    ) {
        return this.validateSavedCard();
    }
    return this.validateNewCard();
}

CreditCardValidator.prototype.validateSavedCard = function () {

    var inputsInvalid = [];
    var formObject = this.formObject;


    if (formObject.savedCreditCardSelect.val() == "") {
        inputsInvalid.push(
            this.isInputInvalid(formObject.savedCreditCardSelect)
        );
    }

    inputsInvalid.push(
        this.isInputInstallmentInvalid(formObject.creditCardInstallments)
    );

    var hasInputInvalid = inputsInvalid.filter(function (item) {
        return item;
    });

    if (hasInputInvalid.length > 0) {
        return false;
    }

    return true;
}

CreditCardValidator.prototype.validateNewCard = function () {

    var inputsInvalid = [];
    var formObject = this.formObject;

    inputsInvalid.push(
        this.isInputInvalid(formObject.creditCardBrand),
        this.isInputInvalid(formObject.creditCardNumber),
        this.isInputInvalid(formObject.creditCardHolderName),
        this.isInputInvalid(formObject.creditCardCvv),
        this.isInputExpirationInvalid(formObject),
        this.isInputInstallmentInvalid(formObject.creditCardInstallments),
        this.isInputInvalidBrandAvailable(formObject.creditCardBrand)
    );

    var hasInputInvalid = inputsInvalid.filter(function (item) {
        return item;
    });

    if (hasInputInvalid.length > 0) {
        return false;
    }

    return true;
}

CreditCardValidator.prototype.isInputInvalidBrandAvailable = function (element) {
    var parentsElements = element.parent().parent();

    var brands = [];
    PlatformConfig.PlatformConfig.avaliableBrands[this.formObject.savedCardSelectUsed].forEach(function (item) {
        brands.push(item.title.toUpperCase());
    });

    if (!brands.includes(this.formObject.creditCardBrand.val().toUpperCase())) {
        parentsElements.addClass("_error");
        parentsElements.find(".field-error").show();
        parentsElements.find(".nobrand").hide();
        return true;
    }

    parentsElements.removeClass("_error");
    parentsElements.find(".field-error").hide();
    return false;
}

CreditCardValidator.prototype.isInputInvalid = function (element, message = "") {

    var parentsElements = element.parent().parent();

    if (element.val() == "") {
        parentsElements.addClass("_error");
        parentsElements.find('.field-error').show();
        return true;
    }

    parentsElements.removeClass('_error');
    parentsElements.find('.field-error').hide();
    return false;
}

CreditCardValidator.prototype.isInputExpirationInvalid = function (formObject) {
    var cardExpirationMonth = formObject.creditCardExpMonth;
    var cardExpirationYear = formObject.creditCardExpYear;

    var cardDate = new Date (cardExpirationYear.val(), cardExpirationMonth.val() -1);
    var dateNow = new Date();

    var monthParentsElements = cardExpirationMonth.parent().parent();
    var yearParentsElements = cardExpirationYear.parent().parent();
    var parentsElements = yearParentsElements.parents(".field");

    if (cardDate < dateNow) {
        monthParentsElements.addClass("_error");
        yearParentsElements.addClass("_error");
        parentsElements.find('.field-error').show();
        return true;
    }

    monthParentsElements.removeClass("_error");
    yearParentsElements.removeClass("_error");
    parentsElements.find('.field-error').hide();
    return false;
}

CreditCardValidator.prototype.isInputInstallmentInvalid = function (element) {

    var parentsElements = element.parents(".field");

    if (element.val() == "") {

        element.parent().parent().addClass("_error");
        parentsElements.find('.field-error').show();
        return true;
    }
    element.parent().parent().removeClass("_error");
    parentsElements.find('.field-error').hide();
    return false;
}
