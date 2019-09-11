var CreditCardValidator = function (formObject) {
    this.formObject = formObject;
    this.errors = [];
};


CreditCardValidator.prototype.validate = function () {

    var inputsInvalid = [];
    var formObject = this.formObject;
    var isSavedCard = formObject.savedCreditCardSelect.val() !== 'new';

    /**@todo valid data from saved card */
    if (isSavedCard && formObject.savedCreditCardSelect.val() == "") {
        this.errors.push(
            "Card selected invalid"
        )
        return;
    }

    inputsInvalid.push(
        this.isInputInvalid(formObject.creditCardBrand, "This brand is not available"),
        this.isInputInvalid(formObject.creditCardNumber),
        this.isInputInvalid(formObject.creditCardHolderName),
        this.isInputInvalid(formObject.creditCardCvv),
        this.isInputExpirationInvalid(formObject),
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

CreditCardValidator.prototype.isInputInvalid = function (element, message = "This is a required field.") {

    var parentsElements = element.parent().parent();

    if (element.val() == "") {
        parentsElements.addClass("_error");
        parentsElements.find('.field-error span').html(message);
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
        parentsElements.find('.field-error span').html("Invalid expiration date.");
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
        parentsElements.find('.field-error span').html("Invalid expiration date.");
        parentsElements.find('.field-error').show();
        return true;
    }
    element.parent().parent().removeClass("_error");
    parentsElements.find('.field-error').hide();
    return false;
}
