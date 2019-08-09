var FormObject = {};
var PlatformConfig = {};

PlatformConfig.bind = function (platformConfig) {
    grandTotal = parseFloat(platformConfig.quoteData.grand_total);

    publicKey = platformConfig.payment.ccform.pk_token;

    urls = {
        base: platformConfig.base_url,
        installments : platformConfig.moduleUrls.installments
    };

    currency = {
        code : platformConfig.quoteData.base_currency_code,
        decimalSeparator : platformConfig.basePriceFormat.decimalSymbol,
        precision : platformConfig.basePriceFormat.precision
    };

    text = {
        months: platformConfig.payment.ccform.months.mundipagg_creditcard,
        years: platformConfig.payment.ccform.years.mundipagg_creditcard
    }

    avaliableBrands = this.getAvaliableBrands(platformConfig);

    var config = {
        avaliableBrands: avaliableBrands,
        orderAmount : grandTotal.toFixed(platformConfig.basePriceFormat.precision),
        urls: urls,
        currency : currency,
        text: text,
        publicKey: publicKey
    };

    this.PlatformConfig = config;

    return this.PlatformConfig;
};

PlatformConfig.getAvaliableBrands = function (data) {
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
    this.FormObject.numberOfPaymentForms = 1;

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

    this.FormObject.numberOfPaymentForms = 2;

    return this.FormObject;
};

FormObject.fillTwoCreditCardsElements = function (containerSelector, elementId) {
    var elements = {
        "creditCardNumber" : jQuery(containerSelector + " .cc_number"),
        "creditCardHolderName" : jQuery(containerSelector + " .cc_owner"),
        "creditCardExpMonth" : jQuery(containerSelector + " .cc_exp_month"),
        "creditCardExpYear" : jQuery(containerSelector + " .cc_exp_year"),
        "creditCardCvv" : jQuery(containerSelector + " .cc_cid"),
        "creditCardInstallments" : jQuery(containerSelector + " .cc_installments"),
        "creditCardBrand" : jQuery(containerSelector + " .cc_type"),
        "creditCardToken" : jQuery(containerSelector + " .cc_token"),
        "creditCardAmount" : jQuery(containerSelector + " .cc_amount")
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