var FormHandler = function () {
    formObject = {}
};

FormHandler.prototype.init = function (formObject) {
    this.formObject = formObject;
};

FormHandler.prototype.switchBrand = function (brand) {
    var brandsSelector = this.formObject.containerSelector + ' .brands';

    jQuery(brandsSelector).css('filter', 'grayscale(100%)');

    if(typeof brand != 'undefined' && brand.length > 0){
        var brandSelector =
            this.formObject.containerSelector + ' .' +
            brand.toLowerCase();

        jQuery(brandSelector).css('filter', 'none');
        this.formObject.creditCardBrand.val(brand);

        return;
    }

    this.formObject.creditCardBrand.val('');
    this.formObject.creditCardNumber.change();
};

FormHandler.prototype.updateInstallmentSelect = function (installmentsObj, element) {
    var content = '';
    for (var i = 0, len = installmentsObj.length; i < len; i++) {
        content +=
            "<option value='" +
                installmentsObj[i].id +
                "' interest='" +
                installmentsObj[i].interest +
                "'>" +
                installmentsObj[i].label +
            "</option>";
    }

    element.html(content);
};

FormHandler.prototype.fillBrandList = function (listContainer, brandsObject) {

    var html = '';

    for (var i = 0, len = brandsObject.length; i < len; i++) {
        html +=
            "<li class='item'>" +
            "<img src='" + brandsObject[i].image + "' " +
            "alt='" + brandsObject[i].title + "' " +
            "width='46' " +
            "height='30' " +
            "class='brands " +
            brandsObject[i].title.toLowerCase() +
            "'>" +
            "</li>";
    }

    jQuery('.credit-card-types').each(function () {
      jQuery(this).html(html);
    });
};

FormHandler.prototype.hideCreditCardAmount = function () {
    jQuery(this.formObject.creditCardAmount).parent().parent('.field').hide();
};

FormHandler.prototype.fillExpirationYearSelect = function (formText) {

    var html = '';
    var years = Object.keys(formText.years);
    var len = years.length;

    for (var i = 0; i < len; i++) {
        html +=
            "<option value='" +
                years[i] +
            "'>" +
                years[i] +
            "</option>"
        ;
    }

    jQuery(this.formObject.creditCardExpYear).html(html);
};

FormHandler.prototype.fillExpirationMonthSelect = function (formText) {

    var html = '';
    var months = formText.months;
    var monthKeys = Object.keys(months);
    var len = monthKeys.length;

    for (var i = 0; i < len; i++) {
        html +=
            "<option value='" +
                monthKeys[i] +
            "'>" +
                months[i + 1] +
            "</option>"
        ;
    }

    jQuery(this.formObject.creditCardExpMonth).html(html);
};

FormHandler.prototype.fillSavedCreditCardsSelect = function (platformConfig, formObject) {
    var html = '';
    var cards = platformConfig.savedCreditCards;

    if (cards) {
        var cardKeys = Object.keys(cards);
        var len = cardKeys.length;

        for (var i = 0; i < len; i++) {
            html +=
                "<option value='" +
                    cards[i].id +
                "'" +
                " brand='" + cards[i].brand  + "'" +
                ">" +
                    cards[i].brand + " " +
                    cards[i].first_six_digits +
                    ".xxxxxx. " +
                    cards[i].last_four_numbers +
                "</option>"
            ;
        }
    }

    if (html.length > 0) {
        jQuery(formObject.containerSelector + ' .new').hide();
        jQuery('.saved').show();
        html += "<option value='new'>Preencher dados</option>";
        jQuery(formObject.savedCreditCardSelect).html(html);
    }
};

FormHandler.prototype.fillMultibuyerStateSelect = function (platformConfig, formObject) {
    var html = "<option value=''>Selecione</option>";
    var states = platformConfig.region_states;

    if (states) {
        var stateKeys = Object.keys(states);
        var len = stateKeys.length;

        for (var i = 0; i < len; i++) {
            html +=
                "<option value='" +
                    states[i].code +

                ">" +
                    states[i].name +
                "</option>"
            ;
        }
    }

    if (html.length > 0) {
        jQuery(formObject.multibuyer.state).html(html);
    }
};

FormHandler.prototype.removeMultibuyerForm = function (formObject) {
    debugger;
    jQuery(formObject.containerSelector + ' .multibuyer').remove();
}