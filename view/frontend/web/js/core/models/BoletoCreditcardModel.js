define([
    'Pagarme_Pagarme/js/core/validators/CreditCardValidator',
    'Pagarme_Pagarme/js/core/validators/MultibuyerValidator',
    'Pagarme_Pagarme/js/core/checkout/CreditCardToken',
], (CreditCardValidator, MultibuyerValidator, CreditCardToken) => {
   return class BoletoCreditcardModel {
       constructor(formObject, publicKey) {
           this.formObject = formObject;
           this.publicKey = publicKey;
           this.modelToken = new CreditCardToken(this.formObject);
           this.errors = [];
           this.formIds = [0, 1];
       }
       placeOrder(placeOrderObject) {
           this.placeOrderObject = placeOrderObject;
           const _self = this;
           let errors = false;

           for (const id in this.formObject) {

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
       validate() {

           const formsInvalid = [];

           for (const id in this.formObject) {

               if (id.length > 1) {
                   continue;
               }
               const multibuyerValidator = new MultibuyerValidator(this.formObject[id]);
               const isMultibuyerValid = multibuyerValidator.validate();

               if (isMultibuyerValid) {
                   continue;
               }

               formsInvalid.push(true);
           }

           const creditCardValidator = new CreditCardValidator(this.formObject[1]);
           const isCreditCardValid = creditCardValidator.validate();

           formsInvalid.push(!isCreditCardValid);

           const hasFormInvalid = formsInvalid.filter(function (item) {
               return item;
           });

           if (hasFormInvalid.length > 0) {
               return false;
           }

           return true;
       }
       getData() {

           let saveThiscard = 0;

           if (this.formObject[1].saveThisCard.prop('checked') === 'on') {
               saveThiscard = 1;
           }

           const data = {
               'method': "pagarme_billet_creditcard",
               'additional_data': {
                   //boleto
                   'cc_billet_amount': this.formObject[0].inputAmount.val(),
                   //credit_card
                   'cc_cc_amount': this.formObject[1].inputAmount.val(),
                   'cc_cc_tax_amount': this.formObject[1].creditCardInstallments.find(':selected').attr('interest'),
                   'cc_type': this.formObject[1].creditCardBrand.val(),
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
               const multibuyer = this.formObject[0].multibuyer;
               const fullName = multibuyer.firstname.val() + ' ' + multibuyer.lastname.val();

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
               const multibuyer = this.formObject[1].multibuyer;
               const fullName = multibuyer.firstname.val() + ' ' + multibuyer.lastname.val();

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
       }
       getLastFourNumbers(id) {
           const number = this.formObject[id].creditCardNumber.val();
           if (number !== undefined) {
               return number.slice(-4);
           }
           return "";
       }
   }
});
