var FormObject = {};

FormObject.creditCardInit = function () {

    if (typeof(this.FormObject === 'undefined')) {
        this.FormObject = {};
    }

    var containerSelector = '#mundipagg_creditcard-form';
    var creditCardForm = {
        'containerSelector' : containerSelector,
        'creditCardNumber' : jQuery(containerSelector + " input[name='payment[cc_number]']"),
        'creditCardHolderName' : jQuery(containerSelector + " input[name='payment[cc_owner]']"),
        'creditExpMonth' : jQuery(containerSelector + " select[name='payment[cc_exp_month]']"),
        'creditCardExpYear' : jQuery(containerSelector + " select[name='payment[cc_exp_year]']"),
        'creditCardCvv' : jQuery(containerSelector + " input[name='payment[cc_cid]']"),
        'creditCardInstallments' : jQuery(containerSelector + " select[name='payment[cc_installments]']"),
        'creditCardBrand' : jQuery(containerSelector + " input[name='payment[cc_type]']"),
        'creditCardToken' : jQuery(containerSelector + " input[name='payment[cc_token]']"),
    };
    this.FormObject = creditCardForm;

    return this.FormObject;
};

FormObject.twoCreditCardsInit = function () {

    if (typeof(this.FormObject === 'undefined')) {
        this.FormObject = {};
    }
    containerSelector = [];
    containerSelector.push("#mundipagg_two_creditcard-form #two-credit-cards-form-0");
    containerSelector.push("#mundipagg_two_creditcard-form #two-credit-cards-form-1");

    //Using for for IE compatibility
    for (var i = 0, len = containerSelector.length; i < len; i++) {
        FormObject.fillTwoCreditCardsElements(containerSelector[i], i);
    }

    return this.FormObject;
};

FormObject.fillTwoCreditCardsElements = function (containerSelector, elementId) {

    var twoCreditCardForm = {};
    var elements = {
        "creditCardNumber" : jQuery(containerSelector + " input[name='payment[cc_number]']"),
        "creditCardHolderName" : jQuery(containerSelector + " input[name='payment[cc_owner]']"),
        "creditExpMonth" : jQuery(containerSelector + " select[name='payment[cc_exp_month]']"),
        "creditCardExpYear" : jQuery(containerSelector + " select[name='payment[cc_exp_year]']"),
        "creditCardCvv" : jQuery(containerSelector + " input[name='payment[cc_cid]']"),
        "creditCardInstallments" : jQuery(containerSelector + " select[name='payment[cc_installments]']"),
        "creditCardBrand" : jQuery(containerSelector + " input[name='payment[cc_type]']"),
        "creditCardToken" : jQuery(containerSelector + " input[name='payment[cc_token]']"),
    };

    for (var key in elements) {
        newName = elements[key].attr('name') + '[' + elementId + ']';
        elements[key].attr('name', newName);

    debugger;
        twoCreditCardForm.elementId = elementId;
        twoCreditCardForm.elementId.key = jQuery(containerSelector + " input[name='" + newName + "']");
    }

    this.FormObject = twoCreditCardForm;
    return this.FormObject;
}
