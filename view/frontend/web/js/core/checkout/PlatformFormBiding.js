var FormObject = {};
var PlarformConfig = {};

PlarformConfig.bind = function (plarformConfig) {
    grandTotal = parseFloat(plarformConfig.quoteData.grand_total);
    urls = {
        'base': BASE_URL,
        'installments' : plarformConfig.moduleUrls.installments
    };

    currency = {
        code : plarformConfig.quoteData.base_currency_code,
        decimalSeparator : plarformConfig.basePriceFormat.decimalSymbol,
        precision : plarformConfig.basePriceFormat.precision
    }

    var config = {
        orderAmount : grandTotal.toFixed(plarformConfig.basePriceFormat.precision),
        urls: urls,
        currency : currency
    };

    this.PlarformConfig = config;

    return this.PlarformConfig;
}

FormObject.creditCardInit = function () {

    if (typeof(this.FormObject === 'undefined')) {
        this.FormObject = {};
    }

    var containerSelector = '#mundipagg_creditcard-form';

    if (typeof jQuery(containerSelector).html() == 'undefined') {
        this.FormObject = null;
        return;
    }

    var creditCardForm = {
        'containerSelector' : containerSelector,
        'creditCardNumber' : jQuery("input[name='payment[cc_number]']"),
        'creditCardHolderName' : jQuery("input[name='payment[cc_owner]']"),
        'creditExpMonth' : jQuery("select[name='payment[cc_exp_month]']"),
        'creditCardExpYear' : jQuery("select[name='payment[cc_exp_year]']"),
        'creditCardCvv' : jQuery("input[name='payment[cc_cid]']"),
        'creditCardInstallments' : jQuery("select[name='payment[cc_installments]']"),
        'creditCardBrand' : jQuery("input[name='payment[cc_type]']"),
        'creditCardToken' : jQuery("input[name='payment[cc_token]']")
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


    if (typeof jQuery(containerSelector[0]).html() == 'undefined') {
        this.FormObject = null;
        return;
    }

    //Using for for IE compatibility
    for (var i = 0, len = containerSelector.length; i < len; i++) {
        FormObject.fillTwoCreditCardsElements(containerSelector[i], i);
    }

    return this.FormObject;
};

FormObject.fillTwoCreditCardsElements = function (containerSelector, elementId) {
    var elements = {
        "creditCardNumber" : jQuery("input[name='payment[cc_number]']"),
        "creditCardHolderName" : jQuery("input[name='payment[cc_owner]']"),
        "creditCardExpMonth" : jQuery("select[name='payment[cc_exp_month]']"),
        "creditCardExpYear" : jQuery("select[name='payment[cc_exp_year]']"),
        "creditCardCvv" : jQuery("input[name='payment[cc_cid]']"),
        "creditCardInstallments" : jQuery("select[name='payment[cc_installments]']"),
        "creditCardBrand" : jQuery("input[name='payment[cc_type]']"),
        "creditCardToken" : jQuery("input[name='payment[cc_token]']"),
        "creditCardAmount" : jQuery("input[name='payment[cc_amount]']")
    };

    this.FormObject[elementId] = this.renameTwoCreditCardsElements(elements, elementId);
    return this.FormObject;
}

FormObject.renameTwoCreditCardsElements = function (elements, elementId) {
    var twoCreditCardForm = {};

    for (var key in elements) {
        newName = elements[key].attr('name') + '[' + elementId + ']';
        elements[key].attr('name', newName);
        elementType = 'input';

        if (elements[key].is('select')) {
            elementType = 'select';
        }

        newElement =
            jQuery(
                elementType +
                "[name='" +
                newName +
                "']"
            );

        twoCreditCardForm[key] = newElement;
    }

    return twoCreditCardForm;
}