define(['jquery'], ($) => {
    const FormObject = {
        FormObject: {}
    };

    FormObject.creditCardInit = (isMultibuyerEnabled) => {

        FormObject.FormObject = {};

        const containerSelector = '#pagarme_creditcard-form';

        if (typeof $(containerSelector).html() == 'undefined') {
            FormObject.FormObject = null;
            return;
        }

        const creditCardForm = {
            'containerSelector' : containerSelector,
            "creditCardNumber" : $(containerSelector + " .cc_number"),
            "creditCardHolderName" : $(containerSelector + " .cc_owner"),
            "creditCardExpMonth" : $(containerSelector + " .cc_exp_month"),
            "creditCardExpYear" : $(containerSelector + " .cc_exp_year"),
            "creditCardCvv" : $(containerSelector + " .cc_cid"),
            "creditCardInstallments" : $(containerSelector + " .cc_installments"),
            "creditCardBrand" : $(containerSelector + " .cc_type"),
            "creditCardToken" : $(containerSelector + " .cc_token"),
            "inputAmount" : $(containerSelector + " .cc_amount"),
            "inputAmountContainer" : $(containerSelector + " .amount-container"),
            "savedCreditCardSelect" : $(containerSelector + " .cc_saved_creditcards"),
            "saveThisCard" : $(containerSelector + " .save_this_card")
        };

        let multibuyerForm = undefined;
        if (isMultibuyerEnabled) {
            multibuyerForm = {
                "showMultibuyer" : $(containerSelector + " .show_multibuyer"),
                "firstname" : $(containerSelector + " .multibuyer_firstname"),
                "lastname" : $(containerSelector + " .multibuyer_lastname"),
                "email" : $(containerSelector + " .multibuyer_email"),
                "zipcode" : $(containerSelector + " .multibuyer_zipcode"),
                "document" : $(containerSelector + " .multibuyer_document"),
                "street" : $(containerSelector + " .multibuyer_street"),
                "number" : $(containerSelector + " .multibuyer_number"),
                "complement" : $(containerSelector + " .multibuyer_complement"),
                "neighborhood" : $(containerSelector + " .multibuyer_neighborhood"),
                "city" : $(containerSelector + " .multibuyer_city"),
                "state" : $(containerSelector + " .multibuyer_state"),
                "homePhone" : $(containerSelector + " .multibuyer_home_phone"),
                "mobilePhone" : $(containerSelector + " .multibuyer_mobile_phone")
            }
        }

        FormObject.FormObject = creditCardForm;
        FormObject.FormObject.numberOfPaymentForms = 1;
        FormObject.FormObject.multibuyer = multibuyerForm;
        FormObject.FormObject.savedCardSelectUsed = 'pagarme_creditcard';

        return FormObject.FormObject;
    };

    FormObject.voucherInit = (isMultibuyerEnabled) => {

        FormObject.FormObject = {};

        const containerSelector = '#pagarme_voucher-form';

        if (typeof $(containerSelector).html() == 'undefined') {
            FormObject.FormObject = null;
            return;
        }

        const voucherForm = {
            'containerSelector' : containerSelector,
            "creditCardNumber" : $(containerSelector + " .cc_number"),
            "creditCardHolderName" : $(containerSelector + " .cc_owner"),
            "creditCardExpMonth" : $(containerSelector + " .cc_exp_month"),
            "creditCardExpYear" : $(containerSelector + " .cc_exp_year"),
            "creditCardCvv" : $(containerSelector + " .cc_cid"),
            "creditCardInstallments" : $(containerSelector + " .cc_installments"),
            "creditCardBrand" : $(containerSelector + " .cc_type"),
            "creditCardToken" : $(containerSelector + " .cc_token"),
            "inputAmount" : $(containerSelector + " .cc_amount"),
            "savedCreditCardSelect" : $(containerSelector + " .cc_saved_creditcards"),
            "saveThisCard" : $(containerSelector + " .save_this_card")
        };

        let multibuyerForm = undefined;
        if (isMultibuyerEnabled) {
            multibuyerForm = {
                "showMultibuyer" : $(containerSelector + " .show_multibuyer"),
                "firstname" : $(containerSelector + " .multibuyer_firstname"),
                "lastname" : $(containerSelector + " .multibuyer_lastname"),
                "email" : $(containerSelector + " .multibuyer_email"),
                "zipcode" : $(containerSelector + " .multibuyer_zipcode"),
                "document" : $(containerSelector + " .multibuyer_document"),
                "street" : $(containerSelector + " .multibuyer_street"),
                "number" : $(containerSelector + " .multibuyer_number"),
                "complement" : $(containerSelector + " .multibuyer_complement"),
                "neighborhood" : $(containerSelector + " .multibuyer_neighborhood"),
                "city" : $(containerSelector + " .multibuyer_city"),
                "state" : $(containerSelector + " .multibuyer_state"),
                "homePhone" : $(containerSelector + " .multibuyer_home_phone"),
                "mobilePhone" : $(containerSelector + " .multibuyer_mobile_phone")
            }
        }

        FormObject.FormObject = voucherForm;
        FormObject.FormObject.numberOfPaymentForms = 1;
        FormObject.FormObject.multibuyer = multibuyerForm;
        FormObject.FormObject.savedCardSelectUsed = 'pagarme_voucher';

        return FormObject.FormObject;
    };

    FormObject.getMultibuyerForm = (containerSelector) => {
        return {
            "showMultibuyer" : $(containerSelector + " .show_multibuyer"),
            "firstname" : $(containerSelector + " .multibuyer_firstname"),
            "lastname" : $(containerSelector + " .multibuyer_lastname"),
            "email" : $(containerSelector + " .multibuyer_email"),
            "zipcode" : $(containerSelector + " .multibuyer_zipcode"),
            "document" : $(containerSelector + " .multibuyer_document"),
            "street" : $(containerSelector + " .multibuyer_street"),
            "number" : $(containerSelector + " .multibuyer_number"),
            "complement" : $(containerSelector + " .multibuyer_complement"),
            "neighborhood" : $(containerSelector + " .multibuyer_neighborhood"),
            "city" : $(containerSelector + " .multibuyer_city"),
            "state" : $(containerSelector + " .multibuyer_state"),
            "homePhone" : $(containerSelector + " .multibuyer_home_phone"),
            "mobilePhone" : $(containerSelector + " .multibuyer_mobile_phone")
        }
    };

    FormObject.debitInit = (isMultibuyerEnabled) => {

        FormObject.FormObject = {};

        const containerSelector = '#pagarme_debit-form';

        if (typeof $(containerSelector).html() == 'undefined') {
            FormObject.FormObject = null;
            return;
        }

        const debitForm = {
            'containerSelector' : containerSelector,
            "creditCardNumber" : $(containerSelector + " .cc_number"),
            "creditCardHolderName" : $(containerSelector + " .cc_owner"),
            "creditCardExpMonth" : $(containerSelector + " .cc_exp_month"),
            "creditCardExpYear" : $(containerSelector + " .cc_exp_year"),
            "creditCardCvv" : $(containerSelector + " .cc_cid"),
            "creditCardInstallments" : $(containerSelector + " .cc_installments"),
            "creditCardBrand" : $(containerSelector + " .cc_type"),
            "creditCardToken" : $(containerSelector + " .cc_token"),
            "inputAmount" : $(containerSelector + " .cc_amount"),
            "savedCreditCardSelect" : $(containerSelector + " .cc_saved_creditcards"),
            "saveThisCard" : $(containerSelector + " .save_this_card")
        };

        let multibuyerForm = undefined;
        if (isMultibuyerEnabled) {
            multibuyerForm = FormObject.getMultibuyerForm(containerSelector)
        }

        FormObject.FormObject = debitForm;
        FormObject.FormObject.numberOfPaymentForms = 1;
        FormObject.FormObject.multibuyer = multibuyerForm;
        FormObject.FormObject.savedCardSelectUsed = 'pagarme_debit';

        return FormObject.FormObject;
    };

    FormObject.renameTwoCreditCardsElements = (elements, elementId) => {
        const twoCreditCardForm = {};

        for (let key in elements) {

            const name = elements[key].attr('name');

            if (name === undefined) {
                continue;
            }

            let newName =  name + '[' + elementId + ']';

            if (name.match(/\[\d\]/g)) {
                newName = name;
            }

            elements[key].attr('name', newName);
            let elementType = 'input';

            if (elements[key].is('select')) {
                elementType = 'select';
            }

            twoCreditCardForm[key] = $(
                elementType +
                "[name='" +
                newName +
                "']"
            );
        }

        return twoCreditCardForm;
    };

    FormObject.fillTwoCreditCardsElements = (containerSelector, elementId, isMultibuyerEnabled) => {

        if ($(containerSelector).children().length == 0) {
            return;
        }

        const elements = {
            "creditCardNumber" : $(containerSelector + " .cc_number"),
            "creditCardHolderName" : $(containerSelector + " .cc_owner"),
            "creditCardExpMonth" : $(containerSelector + " .cc_exp_month"),
            "creditCardExpYear" : $(containerSelector + " .cc_exp_year"),
            "creditCardCvv" : $(containerSelector + " .cc_cid"),
            "creditCardInstallments" : $(containerSelector + " .cc_installments"),
            "creditCardBrand" : $(containerSelector + " .cc_type"),
            "creditCardToken" : $(containerSelector + " .cc_token"),
            "inputAmount" : $(containerSelector + " .cc_amount"),
            "inputAmountContainer" : $(containerSelector + " .amount-container"),
            "savedCreditCardSelect" : $(containerSelector + " .cc_saved_creditcards"),
            "saveThisCard" : $(containerSelector + " .save_this_card")
        };

        let multibuyerForm = undefined;
        if (isMultibuyerEnabled) {
            multibuyerForm = {
                "showMultibuyer" : $(containerSelector + " .show_multibuyer"),
                "firstname" : $(containerSelector + " .multibuyer_firstname"),
                "lastname" : $(containerSelector + " .multibuyer_lastname"),
                "email" : $(containerSelector + " .multibuyer_email"),
                "zipcode" : $(containerSelector + " .multibuyer_zipcode"),
                "document" : $(containerSelector + " .multibuyer_document"),
                "street" : $(containerSelector + " .multibuyer_street"),
                "number" : $(containerSelector + " .multibuyer_number"),
                "complement" : $(containerSelector + " .multibuyer_complement"),
                "neighborhood" : $(containerSelector + " .multibuyer_neighborhood"),
                "city" : $(containerSelector + " .multibuyer_city"),
                "state" : $(containerSelector + " .multibuyer_state"),
                "homePhone" : $(containerSelector + " .multibuyer_home_phone"),
                "mobilePhone" : $(containerSelector + " .multibuyer_mobile_phone")
            }
        }

        FormObject.FormObject[elementId] =
            FormObject.renameTwoCreditCardsElements(
                elements,
                elementId
            );

        FormObject.FormObject[elementId].multibuyer =
            FormObject.renameTwoCreditCardsElements(
                multibuyerForm,
                elementId
            );

        FormObject.FormObject[elementId].containerSelector = containerSelector;
        FormObject.FormObject[elementId].savedCardSelectUsed = 'pagarme_two_creditcard';

        return FormObject.FormObject;
    };

    FormObject.twoCreditCardsInit = (isMultibuyerEnabled) => {

        FormObject.FormObject = {};

        const containerSelector = [];
        containerSelector.push("#pagarme_two_creditcard-form #two-credit-cards-form-0");
        containerSelector.push("#pagarme_two_creditcard-form #two-credit-cards-form-1");


        if (typeof $(containerSelector[0]).html() == 'undefined') {
            FormObject.FormObject = null;
            return;
        }

        //Using for for IE compatibility
        for (let i = 0, len = containerSelector.length; i < len; i++) {
            FormObject.fillTwoCreditCardsElements(containerSelector[i], i, isMultibuyerEnabled);
        }

        FormObject.FormObject.numberOfPaymentForms = 2;

        return FormObject.FormObject;
    };

    FormObject.pixInit = (isMultibuyerEnabled) => {

        FormObject.FormObject = {};

        const containerSelector = '#pagarme_pix-form';

        if (typeof $(containerSelector).html() == 'undefined') {
            FormObject.FormObject = null;
            return;
        }

        const pixElements = {
            'containerSelector' : containerSelector,
            "inputAmount" : $(containerSelector + " .cc_amount"),
            "inputAmountContainer" : $(containerSelector + " .amount-container")
        };

        let multibuyerForm = undefined;
        if (isMultibuyerEnabled) {
            multibuyerForm = {
                "showMultibuyer": $(containerSelector + " .show_multibuyer"),
                "firstname": $(containerSelector + " .multibuyer_firstname"),
                "lastname": $(containerSelector + " .multibuyer_lastname"),
                "email": $(containerSelector + " .multibuyer_email"),
                "zipcode": $(containerSelector + " .multibuyer_zipcode"),
                "document": $(containerSelector + " .multibuyer_document"),
                "street": $(containerSelector + " .multibuyer_street"),
                "number": $(containerSelector + " .multibuyer_number"),
                "complement": $(containerSelector + " .multibuyer_complement"),
                "neighborhood": $(containerSelector + " .multibuyer_neighborhood"),
                "city": $(containerSelector + " .multibuyer_city"),
                "state": $(containerSelector + " .multibuyer_state"),
                "homePhone": $(containerSelector + " .multibuyer_home_phone"),
                "mobilePhone": $(containerSelector + " .multibuyer_mobile_phone")
            }
        }

        FormObject.FormObject = pixElements;
        FormObject.FormObject.numberOfPaymentForms = 1;
        FormObject.FormObject.multibuyer = multibuyerForm;
        return FormObject.FormObject;
    };

    FormObject.googlePayInit = (isMultibuyerEnabled) => {

        FormObject.FormObject = {};

        const containerSelector = '#pagarme_googlepay-form';

        

        const googlepayElements = {
            'containerSelector' : containerSelector,
            "inputAmount" : $(containerSelector + " .cc_amount"),
            "inputAmountContainer" : $(containerSelector + " .amount-container"),
            'teste' : "test"
        };

        FormObject.FormObject = googlepayElements;
        FormObject.FormObject.numberOfPaymentForms = 1;
        return FormObject.FormObject;
    };

    FormObject.boletoInit = (isMultibuyerEnabled) => {

        FormObject.FormObject = {};

        const containerSelector = '#pagarme_billet-form';

        if (typeof $(containerSelector).html() == 'undefined') {
            FormObject.FormObject = null;
            return;
        }

        const boletoElements = {
            'containerSelector' : containerSelector,
            "inputAmount" : $(containerSelector + " .cc_amount"),
            "inputAmountContainer" : $(containerSelector + " .amount-container")
        };

        let multibuyerForm = undefined;
        if (isMultibuyerEnabled) {
            multibuyerForm = {
                "showMultibuyer": $(containerSelector + " .show_multibuyer"),
                "firstname": $(containerSelector + " .multibuyer_firstname"),
                "lastname": $(containerSelector + " .multibuyer_lastname"),
                "email": $(containerSelector + " .multibuyer_email"),
                "zipcode": $(containerSelector + " .multibuyer_zipcode"),
                "document": $(containerSelector + " .multibuyer_document"),
                "street": $(containerSelector + " .multibuyer_street"),
                "number": $(containerSelector + " .multibuyer_number"),
                "complement": $(containerSelector + " .multibuyer_complement"),
                "neighborhood": $(containerSelector + " .multibuyer_neighborhood"),
                "city": $(containerSelector + " .multibuyer_city"),
                "state": $(containerSelector + " .multibuyer_state"),
                "homePhone": $(containerSelector + " .multibuyer_home_phone"),
                "mobilePhone": $(containerSelector + " .multibuyer_mobile_phone")
            }
        }

        FormObject.FormObject = boletoElements;
        FormObject.FormObject.numberOfPaymentForms = 1;
        FormObject.FormObject.multibuyer = multibuyerForm;
        return FormObject.FormObject;
    };

    FormObject.fillBoletoCreditCardElements = (containerSelector, elementId, isMultibuyerEnabled) => {
        if (isMultibuyerEnabled) {
            const multibuyerForm = {
                "showMultibuyer" : $(containerSelector + " .show_multibuyer"),
                "firstname" : $(containerSelector + " .multibuyer_firstname"),
                "lastname" : $(containerSelector + " .multibuyer_lastname"),
                "email" : $(containerSelector + " .multibuyer_email"),
                "zipcode" : $(containerSelector + " .multibuyer_zipcode"),
                "document" : $(containerSelector + " .multibuyer_document"),
                "street" : $(containerSelector + " .multibuyer_street"),
                "number" : $(containerSelector + " .multibuyer_number"),
                "complement" : $(containerSelector + " .multibuyer_complement"),
                "neighborhood" : $(containerSelector + " .multibuyer_neighborhood"),
                "city" : $(containerSelector + " .multibuyer_city"),
                "state" : $(containerSelector + " .multibuyer_state"),
                "homePhone" : $(containerSelector + " .multibuyer_home_phone"),
                "mobilePhone" : $(containerSelector + " .multibuyer_mobile_phone")
            }

            FormObject.FormObject[elementId].multibuyer = multibuyerForm;
        }
        return FormObject.FormObject;
    }

    FormObject.boletoCreditCardInit = (isMultibuyerEnabled) => {
        const containerBoletoSelector = "#pagarme_billet_creditcard-form #billet-form";
        const containerCreditCardSelector = "#pagarme_billet_creditcard-form #credit-card-form";

        FormObject.FormObject = {};

        if (typeof $(containerCreditCardSelector + " .cc_installments").html() == 'undefined') {
            FormObject.FormObject = null;
            return;
        }

        const boletoElements = {
            'containerSelector' : containerBoletoSelector,
            "inputAmount" : $(containerBoletoSelector + " .cc_amount"),
            "inputAmountContainer" : $(containerBoletoSelector + " .amount-container"),
        };

        const cardsElements = {
            'containerSelector' : containerCreditCardSelector,
            "creditCardNumber" : $(containerCreditCardSelector + " .cc_number"),
            "creditCardHolderName" : $(containerCreditCardSelector + " .cc_owner"),
            "creditCardExpMonth" : $(containerCreditCardSelector + " .cc_exp_month"),
            "creditCardExpYear" : $(containerCreditCardSelector + " .cc_exp_year"),
            "creditCardCvv" : $(containerCreditCardSelector + " .cc_cid"),
            "creditCardInstallments" : $(containerCreditCardSelector + " .cc_installments"),
            "creditCardBrand" : $(containerCreditCardSelector + " .cc_type"),
            "creditCardToken" : $(containerCreditCardSelector + " .cc_token"),
            "inputAmount" : $(containerCreditCardSelector + " .cc_amount"),
            "inputAmountContainer" : $(containerCreditCardSelector + " .amount-container"),
            "savedCreditCardSelect" : $(containerCreditCardSelector + " .cc_saved_creditcards"),
            "saveThisCard" : $(containerCreditCardSelector + " .save_this_card")
        };

        FormObject.FormObject[0] = boletoElements;
        FormObject.FormObject[1] = cardsElements;

        for (let i = 0, len = 2; i < len; i++) {
            FormObject.fillBoletoCreditCardElements(FormObject.FormObject[i].containerSelector, i, isMultibuyerEnabled);
        }

        FormObject.FormObject.numberOfPaymentForms = 2;
        FormObject.FormObject[1].savedCardSelectUsed = 'pagarme_billet_creditcard';

        return FormObject.FormObject;
    };

    return FormObject;
})
