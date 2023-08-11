define([], () => {
    return class MultibuyerValidator {
        constructor(formObject) {
            this.formObject = formObject;
        }
        validate() {
            const formObject = this.formObject;
            const inputsInvalid = [];


            if (
                typeof formObject.multibuyer != 'undefined' &&
                typeof formObject.multibuyer.showMultibuyer != 'undefined' &&
                formObject.multibuyer.showMultibuyer.prop( "checked" ) == true
            ) {

                inputsInvalid.push(
                    this.isInputInvalid(formObject.multibuyer.firstname),
                    this.isInputInvalid(formObject.multibuyer.lastname),
                    this.isEmailInvalid(formObject.multibuyer.email),
                    this.isInputInvalid(formObject.multibuyer.zipcode),
                    this.isInputInvalid(formObject.multibuyer.document),
                    this.isInputInvalid(formObject.multibuyer.street),
                    this.isInputInvalid(formObject.multibuyer.number),
                    this.isInputInvalid(formObject.multibuyer.neighborhood),
                    this.isInputInvalid(formObject.multibuyer.city),
                    this.isInputInvalid(formObject.multibuyer.state),
                    this.isInputInvalid(formObject.multibuyer.mobilePhone)
                );
            }

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
        isEmailInvalid(element, message = "") {
            if (this.isInputInvalid(element)) {
                return true;
            }

            const parentsElements = element.parent().parent();

            const isValid = /\S+@\S+\.\S+/.test(element.val());

            if (!isValid) {
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
