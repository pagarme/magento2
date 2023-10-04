define(['Pagarme_Pagarme/js/core/checkout/PlatformConfig',], (PlatformConfig) => {
   return class CreditCardValidator {
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
           return this.validateNewCard();
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
               this.isInputInstallmentInvalid(formObject.creditCardInstallments)
           );

           const hasInputInvalid = inputsInvalid.filter(function (item) {
               return item;
           });

           if (hasInputInvalid.length > 0) {
               return false;
           }

           return true;
       }
       validateNewCard() {

           const inputsInvalid = [];
           const formObject = this.formObject;

           inputsInvalid.push(
               this.isInputInvalid(formObject.creditCardBrand),
               this.isInputInvalid(formObject.creditCardNumber),
               this.isInputInvalid(formObject.creditCardHolderName),
               this.isInputInvalid(formObject.creditCardCvv),
               this.isInputExpirationInvalid(formObject),
               this.isInputInstallmentInvalid(formObject.creditCardInstallments),
               this.isInputInvalidBrandAvailable(formObject.creditCardBrand)
           );

           const hasInputInvalid = inputsInvalid.filter(function (item) {
               return item;
           });

           if (hasInputInvalid.length > 0) {
               return false;
           }

           return true;
       }
       isInputInvalidBrandAvailable(element) {
           const parentsElements = element.parent().parent();

           const brands = [];
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
       isInputExpirationInvalid(formObject) {
           const cardExpirationMonth = formObject.creditCardExpMonth;
           const cardExpirationYear = formObject.creditCardExpYear;

           const cardDate = new Date (cardExpirationYear.val(), cardExpirationMonth.val() -1);
           const dateNow = new Date();

           const monthParentsElements = cardExpirationMonth.parent().parent();
           const yearParentsElements = cardExpirationYear.parent().parent();
           const parentsElements = yearParentsElements.parents(".field");

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
       isInputInstallmentInvalid(element) {

           const parentsElements = element.parents(".field");

           if (element.val() == "") {

               element.parent().parent().addClass("_error");
               parentsElements.find('.field-error').show();
               return true;
           }
           element.parent().parent().removeClass("_error");
           parentsElements.find('.field-error').hide();
           return false;
       }
   }
});
