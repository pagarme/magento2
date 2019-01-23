var FormObject = {

};

FormObject.creditCardInit = function () {

    var containerSelector = '#mundipagg_creditcard-form';

    this.FormObject = {
        'containerSelector' : containerSelector,
        'creditCardNumber' : jQuery(containerSelector + " input[name='payment[cc_number]']"),
        'creditCardHolderName' : jQuery(containerSelector + " input[name='payment[cc_owner]']"),
        'creditExpMonth' : jQuery(containerSelector + " input[name='payment[cc_exp_month]']"),
        'creditCardExpYear' : jQuery(containerSelector + " input[name='payment[cc_exp_year]']"),
        'creditCardCvv' : jQuery(containerSelector + " input[name='payment[cc_cid]']"),
        'creditCardInstallments' : jQuery(containerSelector + " input[name='payment[cc_installments]']"),
        'creditCardBrand' : jQuery(containerSelector + " input[name='payment[cc_type]']")
    };

    return this.FormObject;
}
