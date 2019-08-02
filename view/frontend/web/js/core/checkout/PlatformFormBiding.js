var FormObject = {};
var PlarformConfig = {};

PlarformConfig.bind = function (plarformConfig) {
    grandTotal = parseFloat(plarformConfig.quoteData.grand_total);

    urls = {
        base: plarformConfig.base_url,
        installments : plarformConfig.moduleUrls.installments
    };

    currency = {
        code : plarformConfig.quoteData.base_currency_code,
        decimalSeparator : plarformConfig.basePriceFormat.decimalSymbol,
        precision : plarformConfig.basePriceFormat.precision
    };

    text = {
        months: plarformConfig.payment.ccform.months.mundipagg_creditcard,
        years: plarformConfig.payment.ccform.years.mundipagg_creditcard
    }

    avaliableBrands = this.getAvaliableBrands(plarformConfig);

    var config = {
        avaliableBrands: avaliableBrands,
        orderAmount : grandTotal.toFixed(plarformConfig.basePriceFormat.precision),
        urls: urls,
        currency : currency,
        text: text
    };

    this.PlarformConfig = config;

    return this.PlarformConfig;
};

PlarformConfig.getAvaliableBrands = function (data) {
    var avaliableBrands = [];
    var payment = data.payment.ccform.availableTypes.mundipagg_creditcard;
    var brands = Object.keys(payment);

    for (var i = 0, len = brands.length; i < len; i++) {
        avaliableBrands[i] = {
            'title': brands[i],
            'image': data.payment.ccform.icons[brands[i]].url
        };
    }

    return avaliableBrands;
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
        'creditCardExpMonth' : jQuery("select[name='payment[cc_exp_month]']"),
        'creditCardExpYear' : jQuery("select[name='payment[cc_exp_year]']"),
        'creditCardCvv' : jQuery("input[name='payment[cc_cid]']"),
        'creditCardInstallments' : jQuery("select[name='payment[cc_installments]']"),
        'creditCardBrand' : jQuery("input[name='payment[cc_type]']"),
        'creditCardToken' : jQuery("input[name='payment[cc_token]']"),
        "creditCardAmount" : jQuery("input[name='payment[cc_amount]']")
    };

    this.FormObject = creditCardForm;
    //FormObject.numberOfPaymentForms = 1;

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

    //FormObject.numberOfPaymentForms = 2;

    return this.FormObject;
};

FormObject.fillTwoCreditCardsElements = function (containerSelector, elementId) {
    var elements = {
        "creditCardNumber" : jQuery(containerSelector + " input[name='payment[cc_number]']"),
        "creditCardHolderName" : jQuery(containerSelector + " input[name='payment[cc_owner]']"),
        "creditCardExpMonth" : jQuery(containerSelector + " select[name='payment[cc_exp_month]']"),
        "creditCardExpYear" : jQuery(containerSelector + " select[name='payment[cc_exp_year]']"),
        "creditCardCvv" : jQuery(containerSelector + " input[name='payment[cc_cid]']"),
        "creditCardInstallments" : jQuery(containerSelector + " select[name='payment[cc_installments]']"),
        "creditCardBrand" : jQuery(containerSelector + " input[name='payment[cc_type]']"),
        "creditCardToken" : jQuery(containerSelector + " input[name='payment[cc_token]']"),
        "creditCardAmount" : jQuery(containerSelector + " input[name='payment[cc_amount]']")
    };
    this.FormObject[elementId] = this.renameTwoCreditCardsElements(elements, elementId);
    this.FormObject[elementId].containerSelector = containerSelector;

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