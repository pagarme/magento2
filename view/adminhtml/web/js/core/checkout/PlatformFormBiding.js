define([
    "jquery",
    "uiComponent",
    "Magento_Ui/js/modal/alert",
    "Pagarme_Pagarme/js/core/checkout/PlatformFormBiding",
], function ($, Class, alert, PlatformFormBiding) {

    var FormObject = {};

    FormObject.creditcardInit = function (isMultibuyerEnabled) {

        var creditCardForm = {};

        var containerSelector = "#payment_form_pagarme_creditcard";

        if (typeof jQuery(containerSelector).html() === "undefined") {
            this.creditCardForm = null;
            return;
        }

        creditCardForm = {
            containerSelector,
            "creditCardNumber": jQuery(containerSelector + " .cc_number"),
            "creditCardHolderName": jQuery(containerSelector + " .cc_owner"),
            "creditCardExpMonth": jQuery(containerSelector + " .cc_exp_month"),
            "creditCardExpYear": jQuery(containerSelector + " .cc_exp_year"),
            "creditCardCvv": jQuery(containerSelector + " .cc_cid"),
            "creditCardInstallments": jQuery(containerSelector + " .cc_installments"),
            "creditCardBrand": jQuery(containerSelector + " .cc_type"),
            "creditCardToken": jQuery(containerSelector + " .cc_token"),
            "inputAmount": jQuery(containerSelector + " .cc_amount"),
            "inputAmountWithoutTax": jQuery(containerSelector + " .cc_amount_without_tax"),
            "inputAmountContainer": jQuery(containerSelector + " .amount-container"),
            "savedCreditCardSelect": jQuery(containerSelector + " .cc_saved_creditcards"),
            "saveThisCard": jQuery(containerSelector + " .save_this_card"),
            "publicKey": jQuery(containerSelector + " .public_key")
        };

        if (isMultibuyerEnabled) {
            var multibuyerForm = {
                "showMultibuyer": jQuery(containerSelector + " .show_multibuyer"),
                "firstname": jQuery(containerSelector + " .multibuyer_firstname"),
                "lastname": jQuery(containerSelector + " .multibuyer_lastname"),
                "email": jQuery(containerSelector + " .multibuyer_email"),
                "zipcode": jQuery(containerSelector + " .multibuyer_zipcode"),
                "document": jQuery(containerSelector + " .multibuyer_document"),
                "street": jQuery(containerSelector + " .multibuyer_street"),
                "number": jQuery(containerSelector + " .multibuyer_number"),
                "complement": jQuery(containerSelector + " .multibuyer_complement"),
                "neighborhood": jQuery(containerSelector + " .multibuyer_neighborhood"),
                "city": jQuery(containerSelector + " .multibuyer_city"),
                "state": jQuery(containerSelector + " .multibuyer_state"),
                "homePhone": jQuery(containerSelector + " .multibuyer_home_phone"),
                "mobilePhone": jQuery(containerSelector + " .multibuyer_mobile_phone")
            };
            creditCardForm.multibuyer = multibuyerForm;
        }

        creditCardForm.numberOfPaymentForms = 1;

        return creditCardForm;
    };

    return FormObject;

});
