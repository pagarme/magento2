define([
    "jquery",
    "uiComponent",
], function ($, Class) {
    
    var CreditCardValidator = {
    };

    CreditCardValidator.validate = function (formObject) {
        return this.validateNewCard(formObject);
    };

    CreditCardValidator.validateSavedCard = function () {

        var inputsInvalid = [];
        var formObject = this.formObject;

        if (formObject.savedCreditCardSelect.val() === "") {
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
    };

    CreditCardValidator.validateNewCard = function (formObject) {
        var inputsInvalid = [];

        inputsInvalid.push(
            this.isInputInvalid(formObject.creditCardBrand),
            this.isInputInvalid(formObject.creditCardNumber),
            this.isInputInvalid(formObject.creditCardHolderName),
            this.isCvvInvalid(formObject.creditCardCvv),
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
    };

    CreditCardValidator.isCvvInvalid = function (element, message = "") {

        if (
            element.val() === "" ||
            element.val().length < 3 ||
            element.val().length > 4
        ) {
            element.parent().find(".hosted-error")
                .css("opacity", "1")
                .css("color", "red");
            return true;
        }

        element.parent().find(".hosted-error")
            .css("opacity", "0")
            .css("color", "red");
        return false;
    };

    CreditCardValidator.isInputInvalid = function (element, message = "") {

        if (element.val() === "") {
            element.parent().find(".hosted-error")
                .css("opacity", "1")
                .css("color", "red");
            return true;
        }

        element.parent().find(".hosted-error")
            .css("opacity", "0")
            .css("color", "red");
        return false;
    };

    CreditCardValidator.isInputExpirationInvalid = function (formObject) {
        var cardExpirationMonth = formObject.creditCardExpMonth;
        var cardExpirationYear = formObject.creditCardExpYear;

        var cardDate = new Date (cardExpirationYear.val(), cardExpirationMonth.val() -1);
        var dateNow = new Date();

        if (cardDate < dateNow) {
            cardExpirationYear.parent().find(".hosted-error")
                .css("opacity", "1")
                .css("color", "red");
            return true;
        }

        cardExpirationYear.parent().find(".hosted-error")
            .css("opacity", "0")
            .css("color", "red");
        return false;
    };

    CreditCardValidator.isInputInstallmentInvalid = function (element) {
        if (element.val() === "") {
            element.parent().find(".hosted-error")
                .css("opacity", "1")
                .css("color", "red");
            return true;
        }

        element.parent().find(".hosted-error")
            .css("opacity", "0")
            .css("color", "red");
        return false;
    };

    return CreditCardValidator;
});