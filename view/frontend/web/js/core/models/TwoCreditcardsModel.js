define([
    'Pagarme_Pagarme/js/core/checkout/CreditCardToken',
    'Pagarme_Pagarme/js/core/validators/CreditCardValidator',
    'Pagarme_Pagarme/js/core/validators/MultibuyerValidator',
], (CreditCardToken, CreditCardValidator, MultibuyerValidator) => {
   return class TwoCreditcardsModel {
        constructor(formObject, publicKey) {
            this.formObject = formObject;
            this.publicKey = publicKey;
            this.modelToken = new CreditCardToken(this.formObject);
            this.errors = [];
            this.formIds = [0, 1];
        }
       validate() {

           const formsInvalid = [];

           for (const id in this.formObject) {

               if (id.length > 1) {
                   continue;
               }
               const creditCardValidator = new CreditCardValidator(this.formObject[id]);
               const isCreditCardValid = creditCardValidator.validate();

               const multibuyerValidator = new MultibuyerValidator(this.formObject[id]);
               const isMultibuyerValid = multibuyerValidator.validate();

               if (isCreditCardValid && isMultibuyerValid) {
                   continue;
               }

               formsInvalid.push(true);
           }

           const hasFormInvalid = formsInvalid.filter(function (item) {
               return item;
           });

           if (hasFormInvalid.length > 0) {
               return false;
           }

           return true;
       }
       placeOrder(placeOrderObject) {
           this.placeOrderObject = placeOrderObject;
           const _self = this;
           let errors = false;

           for (const id in this.formObject) {

               if (id.length > 1) {
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
       }
       getFormIdInverted(id) {
           const ids = this.formIds.slice(0);
           const index = ids.indexOf(id);
           ids.splice(index, 1);

           return ids[0];
       }
       addErrors(error) {
           this.errors.push({
               message: error
           })
       }
       getCreditCardToken(formObject, success, error) {
           const modelToken = new CreditCardToken(formObject);
           modelToken.getToken(this.publicKey)
               .done(success)
               .fail(error);
       }
       getData() {
           const data = this.fillData();

           if (
               typeof this.formObject[0].multibuyer.showMultibuyer != 'undefined' &&
               this.formObject[0].multibuyer.showMultibuyer.prop( "checked" ) == true
           ) {
               const multibuyer = this.formObject[0].multibuyer;
               const fullName = multibuyer.firstname.val() + ' ' + multibuyer.lastname.val();

               data.additional_data.cc_buyer_checkbox_first = 1;
               data.additional_data.cc_buyer_name_first = fullName;
               data.additional_data.cc_buyer_email_first = multibuyer.email.val();
               data.additional_data.cc_buyer_document_first = multibuyer.document.val();
               data.additional_data.cc_buyer_street_title_first = multibuyer.street.val();
               data.additional_data.cc_buyer_street_number_first = multibuyer.number.val();
               data.additional_data.cc_buyer_street_complement_first = multibuyer.complement.val();
               data.additional_data.cc_buyer_zipcode_first = multibuyer.zipcode.val();
               data.additional_data.cc_buyer_neighborhood_first = multibuyer.neighborhood.val();
               data.additional_data.cc_buyer_city_first = multibuyer.city.val();
               data.additional_data.cc_buyer_state_first = multibuyer.state.val();
               data.additional_data.cc_buyer_home_phone_first = multibuyer.homePhone.val();
               data.additional_data.cc_buyer_mobile_phone_first = multibuyer.mobilePhone.val();
           }

           if (
               typeof this.formObject[1].multibuyer.showMultibuyer != 'undefined' &&
               this.formObject[1].multibuyer.showMultibuyer.prop( "checked" ) == true
           ) {
               const multibuyer = this.formObject[1].multibuyer;
               const fullName = multibuyer.firstname.val() + ' ' + multibuyer.lastname.val();

               data.additional_data.cc_buyer_checkbox_second = 1;
               data.additional_data.cc_buyer_name_second = fullName;
               data.additional_data.cc_buyer_email_second = multibuyer.email.val();
               data.additional_data.cc_buyer_document_second = multibuyer.document.val();
               data.additional_data.cc_buyer_street_title_second = multibuyer.street.val();
               data.additional_data.cc_buyer_street_number_second = multibuyer.number.val();
               data.additional_data.cc_buyer_street_complement_second = multibuyer.complement.val();
               data.additional_data.cc_buyer_zipcode_second = multibuyer.zipcode.val();
               data.additional_data.cc_buyer_neighborhood_second = multibuyer.neighborhood.val();
               data.additional_data.cc_buyer_city_second = multibuyer.city.val();
               data.additional_data.cc_buyer_state_second = multibuyer.state.val();
               data.additional_data.cc_buyer_home_phone_second = multibuyer.homePhone.val();
               data.additional_data.cc_buyer_mobile_phone_second = multibuyer.mobilePhone.val();
           }

           return data;
       }
       fillData() {

           let saveFirstCard = 0;
           let saveSecondCard = 0;

           if (this.formObject[0].saveThisCard?.prop('checked') == true) {
               saveFirstCard = 1;
           }

           if (this.formObject[1].saveThisCard?.prop('checked') == true) {
               saveSecondCard = 1;
           }

           return {
               'method': "pagarme_two_creditcard",
               'additional_data': {
                   //first
                   'cc_first_card_amount': this.formObject[0].inputAmount.val(),
                   'cc_first_card_tax_amount': this.formObject[0].creditCardInstallments.find(':selected').attr('interest'),
                   'cc_type_first': this.formObject[0].creditCardBrand.val(),
                   'cc_savecard_first' : saveFirstCard,
                   'cc_saved_card_first' : this.formObject[0].savedCreditCardSelect.val(),
                   'cc_installments_first': this.formObject[0].creditCardInstallments.val(),
                   'cc_token_credit_card_first' : this.formObject[0].creditCardToken.val(),
                   //second
                   'cc_second_card_amount': this.formObject[1].inputAmount.val(),
                   'cc_second_card_tax_amount': this.formObject[1].creditCardInstallments.find(':selected').attr('interest'),
                   'cc_type_second': this.formObject[1].creditCardBrand.val(),
                   'cc_savecard_second' : saveSecondCard,
                   'cc_saved_card_second' : this.formObject[1].savedCreditCardSelect.val(),
                   'cc_installments_second': this.formObject[1].creditCardInstallments.val(),
                   'cc_token_credit_card_second' : this.formObject[1].creditCardToken.val()
               }
           };
       }
       getLastFourNumbers(id) {
           const number = this.formObject[id].creditCardNumber.val();
           if (number !== undefined) {
               return number.slice(-4);
           }
           return "";
       }
   };
});
