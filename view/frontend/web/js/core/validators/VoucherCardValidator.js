define([], () => {
    return class VoucherCardValidator {
        constructor(formObject) {
            this.formObject = formObject;
        }
        validate() {
            if (
                typeof this.formObject.savedCreditCardSelect != 'undefined' &&
                this.formObject.savedCreditCardSelect.html().length > 1 &&
                this.formObject.savedCreditCardSelect.val() !== 'new'
            ) {
                return this.validateSavedCard();
            }

            return true;
        }
        validateSavedCard() {
            const inputsInvalid = [];
            const formObject = this.formObject;

            if (formObject.savedCreditCardSelect.val() == "") {
                inputsInvalid.push(
                    this.isInputInvalid(formObject.savedCreditCardSelect)
                );
            }

            inputsInvalid.push(
                this.isInputInvalid(formObject.creditCardCvv)
            );

            const hasInputInvalid = inputsInvalid.filter(function (item) {
                return item;
            });

            if (hasInputInvalid.length > 0) {
                return false;
            }

            return true;
        }
        isInputInvalid(element, message = "") {

            const parentsElements = element.parent().parent();

            if (element.val() == "") {
                parentsElements.addClass("_error");
                parentsElements.find('.field-error').show();
                return true;
            }

            parentsElements.removeClass('_error');
            parentsElements.find('.field-error').hide();
            return false;
        }
    }
});
