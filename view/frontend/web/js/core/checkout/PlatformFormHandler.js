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
        var brandSelector = this.formObject.containerSelector + ' .' + brand;

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