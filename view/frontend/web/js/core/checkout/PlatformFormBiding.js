var FormObject = {};
var PlatformConfig = {};

PlatformConfig.bind = function (platformConfig) {
    grandTotal = parseFloat(platformConfig.grand_total);

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
    savedCreditCards = this.getSavedCreditCards(platformConfig);

    loader = {
        start: platformConfig.loader.startLoader,
        stop: platformConfig.loader.stopLoader
    };
    totals = platformConfig.totalsData;

    var config = {
        avaliableBrands: avaliableBrands,
        orderAmount : grandTotal.toFixed(platformConfig.basePriceFormat.precision),
        urls: urls,
        currency : currency,
        text: text,
        publicKey: publicKey,
        totals: totals,
        loader: loader,
        addresses: platformConfig.addresses,
        updateTotals: platformConfig.updateTotals,
        savedCreditCards: savedCreditCards,
        region_states: platformConfig.region_states,
        isMultibuyerEnabled: platformConfig.is_multi_buyer_enabled
    };

    this.PlatformConfig = config;

    return this.PlatformConfig;
};

PlatformConfig.getAvaliableBrands = function (data) {
    var CCBrands = [];
    var VoucherBrands = [];

    var objCreditCardBrands = data.payment.ccform.availableTypes.mundipagg_creditcard;
    if (objCreditCardBrands !== undefined) {
        var brands = Object.keys(objCreditCardBrands);

        for (var i = 0, len = brands.length; i < len; i++) {
            url = data.payment.ccform.icons[brands[i]].url
            fixArray = [];
            imageUrl = fixArray.concat(url);

            CCBrands[i] = {
                'title': brands[i],
                'image': imageUrl[0]
            };
        }
    }

    var objVoucherBrands = data.payment.ccform.availableTypes.mundipagg_voucher;
    if (objVoucherBrands !== undefined) {
        var brands = Object.keys(objVoucherBrands);

        for (var i = 0, len = brands.length; i < len; i++) {
            url = data.payment.ccform.icons[brands[i]].url
            fixArray = [];
            imageUrl = fixArray.concat(url);
            VoucherBrands[i] = {
                'title': brands[i],
                'image': imageUrl[0]
            };
        }
    }

    return {
        'mundipagg_creditcard': CCBrands,
        'mundipagg_voucher': VoucherBrands
    };
}

FormObject.creditCardInit = function (isMultibuyerEnabled) {

    this.FormObject = {};

    var containerSelector = '#mundipagg_creditcard-form';

    if (typeof jQuery(containerSelector).html() == 'undefined') {
        this.FormObject = null;
        return;
    }

    var creditCardForm = {
        'containerSelector' : containerSelector,
        "creditCardNumber" : jQuery(containerSelector + " .cc_number"),
        "creditCardHolderName" : jQuery(containerSelector + " .cc_owner"),
        "creditCardExpMonth" : jQuery(containerSelector + " .cc_exp_month"),
        "creditCardExpYear" : jQuery(containerSelector + " .cc_exp_year"),
        "creditCardCvv" : jQuery(containerSelector + " .cc_cid"),
        "creditCardInstallments" : jQuery(containerSelector + " .cc_installments"),
        "creditCardBrand" : jQuery(containerSelector + " .cc_type"),
        "creditCardToken" : jQuery(containerSelector + " .cc_token"),
        "inputAmount" : jQuery(containerSelector + " .cc_amount"),
        "inputAmountContainer" : jQuery(containerSelector + " .amount-container"),
        "savedCreditCardSelect" : jQuery(containerSelector + " .cc_saved_creditcards"),
        "saveThisCard" : jQuery(containerSelector + " .save_this_card")
    };

    if (isMultibuyerEnabled) {
        var multibuyerForm = {
            "showMultibuyer" : jQuery(containerSelector + " .show_multibuyer"),
            "firstname" : jQuery(containerSelector + " .multibuyer_firstname"),
            "lastname" : jQuery(containerSelector + " .multibuyer_lastname"),
            "email" : jQuery(containerSelector + " .multibuyer_email"),
            "zipcode" : jQuery(containerSelector + " .multibuyer_zipcode"),
            "document" : jQuery(containerSelector + " .multibuyer_document"),
            "street" : jQuery(containerSelector + " .multibuyer_street"),
            "number" : jQuery(containerSelector + " .multibuyer_number"),
            "complement" : jQuery(containerSelector + " .multibuyer_complement"),
            "neighborhood" : jQuery(containerSelector + " .multibuyer_neighborhood"),
            "city" : jQuery(containerSelector + " .multibuyer_city"),
            "state" : jQuery(containerSelector + " .multibuyer_state"),
            "homePhone" : jQuery(containerSelector + " .multibuyer_home_phone"),
            "mobilePhone" : jQuery(containerSelector + " .multibuyer_mobile_phone")
        }
    }

    this.FormObject = creditCardForm;
    this.FormObject.numberOfPaymentForms = 1;
    this.FormObject.multibuyer = multibuyerForm;

    return this.FormObject;
};

FormObject.voucherInit = function (isMultibuyerEnabled) {

    this.FormObject = {};

    var containerSelector = '#mundipagg_voucher-form';

    if (typeof jQuery(containerSelector).html() == 'undefined') {
        this.FormObject = null;
        return;
    }

    var voucherForm = {
        'containerSelector' : containerSelector,
        "creditCardNumber" : jQuery(containerSelector + " .cc_number"),
        "creditCardHolderName" : jQuery(containerSelector + " .cc_owner"),
        "creditCardExpMonth" : jQuery(containerSelector + " .cc_exp_month"),
        "creditCardExpYear" : jQuery(containerSelector + " .cc_exp_year"),
        "creditCardCvv" : jQuery(containerSelector + " .cc_cid"),
        "creditCardInstallments" : jQuery(containerSelector + " .cc_installments"),
        "creditCardBrand" : jQuery(containerSelector + " .cc_type"),
        "creditCardToken" : jQuery(containerSelector + " .cc_token"),
        "inputAmount" : jQuery(containerSelector + " .cc_amount"),
        "savedCreditCardSelect" : jQuery(containerSelector + " .cc_saved_creditcards"),
        "saveThisCard" : jQuery(containerSelector + " .save_this_card")
    };

    if (isMultibuyerEnabled) {
        var multibuyerForm = {
            "showMultibuyer" : jQuery(containerSelector + " .show_multibuyer"),
            "firstname" : jQuery(containerSelector + " .multibuyer_firstname"),
            "lastname" : jQuery(containerSelector + " .multibuyer_lastname"),
            "email" : jQuery(containerSelector + " .multibuyer_email"),
            "zipcode" : jQuery(containerSelector + " .multibuyer_zipcode"),
            "document" : jQuery(containerSelector + " .multibuyer_document"),
            "street" : jQuery(containerSelector + " .multibuyer_street"),
            "number" : jQuery(containerSelector + " .multibuyer_number"),
            "complement" : jQuery(containerSelector + " .multibuyer_complement"),
            "neighborhood" : jQuery(containerSelector + " .multibuyer_neighborhood"),
            "city" : jQuery(containerSelector + " .multibuyer_city"),
            "state" : jQuery(containerSelector + " .multibuyer_state"),
            "homePhone" : jQuery(containerSelector + " .multibuyer_home_phone"),
            "mobilePhone" : jQuery(containerSelector + " .multibuyer_mobile_phone")
        }
    }

    this.FormObject = voucherForm;
    this.FormObject.numberOfPaymentForms = 1;
    this.FormObject.multibuyer = multibuyerForm;

    return this.FormObject;
};

FormObject.twoCreditCardsInit = function (isMultibuyerEnabled) {

    this.FormObject = {};

    containerSelector = [];
    containerSelector.push("#mundipagg_two_creditcard-form #two-credit-cards-form-0");
    containerSelector.push("#mundipagg_two_creditcard-form #two-credit-cards-form-1");


    if (typeof jQuery(containerSelector[0]).html() == 'undefined') {
        this.FormObject = null;
        return;
    }

    //Using for for IE compatibility
    for (var i = 0, len = containerSelector.length; i < len; i++) {
        FormObject.fillTwoCreditCardsElements(containerSelector[i], i, isMultibuyerEnabled);
    }

    this.FormObject.numberOfPaymentForms = 2;

    return this.FormObject;
};

FormObject.boletoInit = function (isMultibuyerEnabled) {

    this.FormObject = {};

    var containerSelector = '#mundipagg_billet-form';

    if (typeof jQuery(containerSelector).html() == 'undefined') {
        this.FormObject = null;
        return;
    }

    var boletoElements = {
        'containerSelector' : containerSelector,
        "inputAmount" : jQuery(containerSelector + " .cc_amount"),
        "inputAmountContainer" : jQuery(containerSelector + " .amount-container")
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
        }
    }

    this.FormObject = boletoElements;
    this.FormObject.numberOfPaymentForms = 1;
    this.FormObject.multibuyer = multibuyerForm;
    return this.FormObject;
}

FormObject.boletoCreditCardInit = function (isMultibuyerEnabled) {

    var containerBoletoSelector = "#mundipagg_billet_creditcard-form #billet-form";
    var containerCreditCardSelector = "#mundipagg_billet_creditcard-form #credit-card-form";

    this.FormObject = {};

    if (typeof jQuery(containerCreditCardSelector + " .cc_installments").html() == 'undefined') {
        this.FormObject = null;
        return;
    }

    var boletoElements = {
        'containerSelector' : containerBoletoSelector,
        "inputAmount" : jQuery(containerBoletoSelector + " .cc_amount"),
        "inputAmountContainer" : jQuery(containerBoletoSelector + " .amount-container"),
    };

    var cardsElements = {
        'containerSelector' : containerCreditCardSelector,
        "creditCardNumber" : jQuery(containerCreditCardSelector + " .cc_number"),
        "creditCardHolderName" : jQuery(containerCreditCardSelector + " .cc_owner"),
        "creditCardExpMonth" : jQuery(containerCreditCardSelector + " .cc_exp_month"),
        "creditCardExpYear" : jQuery(containerCreditCardSelector + " .cc_exp_year"),
        "creditCardCvv" : jQuery(containerCreditCardSelector + " .cc_cid"),
        "creditCardInstallments" : jQuery(containerCreditCardSelector + " .cc_installments"),
        "creditCardBrand" : jQuery(containerCreditCardSelector + " .cc_type"),
        "creditCardToken" : jQuery(containerCreditCardSelector + " .cc_token"),
        "inputAmount" : jQuery(containerCreditCardSelector + " .cc_amount"),
        "inputAmountContainer" : jQuery(containerCreditCardSelector + " .amount-container"),
        "savedCreditCardSelect" : jQuery(containerCreditCardSelector + " .cc_saved_creditcards"),
        "saveThisCard" : jQuery(containerCreditCardSelector + " .save_this_card")
    };

    this.FormObject[0] = boletoElements;
    this.FormObject[1] = cardsElements;

    for (var i = 0, len = 2; i < len; i++) {
        FormObject.fillBoletoCreditCardElements(this.FormObject[i].containerSelector, i, isMultibuyerEnabled);
    }

    this.FormObject.numberOfPaymentForms = 2;

    return this.FormObject;
}

FormObject.fillBoletoCreditCardElements = function (containerSelector, elementId, isMultibuyerEnabled) {
    if (isMultibuyerEnabled) {
        var multibuyerForm = {
            "showMultibuyer" : jQuery(containerSelector + " .show_multibuyer"),
            "firstname" : jQuery(containerSelector + " .multibuyer_firstname"),
            "lastname" : jQuery(containerSelector + " .multibuyer_lastname"),
            "email" : jQuery(containerSelector + " .multibuyer_email"),
            "zipcode" : jQuery(containerSelector + " .multibuyer_zipcode"),
            "document" : jQuery(containerSelector + " .multibuyer_document"),
            "street" : jQuery(containerSelector + " .multibuyer_street"),
            "number" : jQuery(containerSelector + " .multibuyer_number"),
            "complement" : jQuery(containerSelector + " .multibuyer_complement"),
            "neighborhood" : jQuery(containerSelector + " .multibuyer_neighborhood"),
            "city" : jQuery(containerSelector + " .multibuyer_city"),
            "state" : jQuery(containerSelector + " .multibuyer_state"),
            "homePhone" : jQuery(containerSelector + " .multibuyer_home_phone"),
            "mobilePhone" : jQuery(containerSelector + " .multibuyer_mobile_phone")
        }

        this.FormObject[elementId].multibuyer = multibuyerForm;
    }
    return this.FormObject;
}

FormObject.fillTwoCreditCardsElements = function (containerSelector, elementId, isMultibuyerEnabled) {

    if (jQuery(containerSelector).children().length == 0) {
        return;
    }

    var elements = {
        "creditCardNumber" : jQuery(containerSelector + " .cc_number"),
        "creditCardHolderName" : jQuery(containerSelector + " .cc_owner"),
        "creditCardExpMonth" : jQuery(containerSelector + " .cc_exp_month"),
        "creditCardExpYear" : jQuery(containerSelector + " .cc_exp_year"),
        "creditCardCvv" : jQuery(containerSelector + " .cc_cid"),
        "creditCardInstallments" : jQuery(containerSelector + " .cc_installments"),
        "creditCardBrand" : jQuery(containerSelector + " .cc_type"),
        "creditCardToken" : jQuery(containerSelector + " .cc_token"),
        "inputAmount" : jQuery(containerSelector + " .cc_amount"),
        "inputAmountContainer" : jQuery(containerSelector + " .amount-container"),
        "savedCreditCardSelect" : jQuery(containerSelector + " .cc_saved_creditcards"),
        "saveThisCard" : jQuery(containerSelector + " .save_this_card")
    };

    if (isMultibuyerEnabled) {
        var multibuyerForm = {
            "showMultibuyer" : jQuery(containerSelector + " .show_multibuyer"),
            "firstname" : jQuery(containerSelector + " .multibuyer_firstname"),
            "lastname" : jQuery(containerSelector + " .multibuyer_lastname"),
            "email" : jQuery(containerSelector + " .multibuyer_email"),
            "zipcode" : jQuery(containerSelector + " .multibuyer_zipcode"),
            "document" : jQuery(containerSelector + " .multibuyer_document"),
            "street" : jQuery(containerSelector + " .multibuyer_street"),
            "number" : jQuery(containerSelector + " .multibuyer_number"),
            "complement" : jQuery(containerSelector + " .multibuyer_complement"),
            "neighborhood" : jQuery(containerSelector + " .multibuyer_neighborhood"),
            "city" : jQuery(containerSelector + " .multibuyer_city"),
            "state" : jQuery(containerSelector + " .multibuyer_state"),
            "homePhone" : jQuery(containerSelector + " .multibuyer_home_phone"),
            "mobilePhone" : jQuery(containerSelector + " .multibuyer_mobile_phone")
        }
    }

    this.FormObject[elementId] =
        this.renameTwoCreditCardsElements(
            elements,
            elementId
        );

    this.FormObject[elementId].multibuyer =
        this.renameTwoCreditCardsElements(
            multibuyerForm,
            elementId
        );

    this.FormObject[elementId].containerSelector = containerSelector;

    return this.FormObject;
};

FormObject.renameTwoCreditCardsElements = function (elements, elementId) {
    var twoCreditCardForm = {};

    for (var key in elements) {

        name = elements[key].attr('name');

        newName =  name + '[' + elementId + ']';

        if (name.match(/\[\d\]/g)) {
            newName = name;
        }

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
};

PlatformConfig.getSavedCreditCards = function (platFormConfig) {
    if (
        platFormConfig.payment.mundipagg_creditcard.enabled_saved_cards &&
        typeof(platFormConfig.payment.mundipagg_creditcard.cards != 'undefined')
    ) {
        cards = platFormConfig.payment.mundipagg_creditcard.cards;
        return cards;
    }

    return null;
};
