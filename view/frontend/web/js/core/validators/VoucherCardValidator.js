var VoucherCardValidator = function (formObject) {
    this.formObject = formObject;
};

VoucherCardValidator.prototype.validate = function () {
    if (
        typeof this.formObject.savedCreditCardSelect != 'undefined' &&
        this.formObject.savedCreditCardSelect.html().length > 1 &&
        this.formObject.savedCreditCardSelect.val() !== 'new'
    ) {
        return this.validateSavedCard();
    }

    return true;
}

VoucherCardValidator.prototype.validateSavedCard = function () {
    var inputsInvalid = [];
    var formObject = this.formObject;

    if (formObject.savedCreditCardSelect.val() == "") {
        inputsInvalid.push(
            this.isInputInvalid(formObject.savedCreditCardSelect)
        );
    }

    inputsInvalid.push(
        this.isInputInvalid(formObject.creditCardCvv)
    );

    var hasInputInvalid = inputsInvalid.filter(function (item) {
        return item;
    });

    if (hasInputInvalid.length > 0) {
        return false;
    }

    return true;
}

VoucherCardValidator.prototype.isInputInvalid = function (element, message = "") {

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