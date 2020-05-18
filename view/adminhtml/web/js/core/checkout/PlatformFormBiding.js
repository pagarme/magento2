define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'MundiPagg_MundiPagg/js/core/checkout/PlatformFormBiding',
], function ($, Class, alert, PlatformFormBiding) {

    var FormObject = {};
    var PlatformConfig = {};

    PlatformConfig.bind = function (platformConfig) {
        grandTotal = parseFloat(platformConfig.grand_total);

        publicKey = platformConfig.payment.ccform.pk_token;

        urls = {
            base: platformConfig.base_url,
            installments: platformConfig.moduleUrls.installments
        };

        currency = {
            code: platformConfig.quoteData.base_currency_code,
            decimalSeparator: platformConfig.basePriceFormat.decimalSymbol,
            precision: platformConfig.basePriceFormat.precision
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
            orderAmount: grandTotal.toFixed(platformConfig.basePriceFormat.precision),
            urls: urls,
            currency: currency,
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


    FormObject.creditcardInit = function (isMultibuyerEnabled) {

        var creditCardForm = {};

        var containerSelector = '#payment_form_mundipagg_creditcard';

        if (typeof jQuery(containerSelector).html() == 'undefined') {
            this.creditCardForm = null;
            return;
        }

        creditCardForm = {
            'containerSelector': containerSelector,
            "creditCardNumber": jQuery(containerSelector + " .cc_number"),
            "creditCardHolderName": jQuery(containerSelector + " .cc_owner"),
            "creditCardExpMonth": jQuery(containerSelector + " .cc_exp_month"),
            "creditCardExpYear": jQuery(containerSelector + " .cc_exp_year"),
            "creditCardCvv": jQuery(containerSelector + " .cc_cid"),
            "creditCardInstallments": jQuery(containerSelector + " .cc_installments"),
            "creditCardBrand": jQuery(containerSelector + " .cc_type"),
            "creditCardToken": jQuery(containerSelector + " .cc_token"),
            "inputAmount": jQuery(containerSelector + " .cc_amount"),
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
            }
        }

        creditCardForm.numberOfPaymentForms = 1;
        creditCardForm.multibuyer = multibuyerForm;

        return creditCardForm;
    };

    return {
        PlatformConfig,
        FormObject
    };

})