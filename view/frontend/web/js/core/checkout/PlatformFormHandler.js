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
}

FormHandler.prototype.fillBrandList = function (listContainer, brandsObject) {

    var content = '';

    for (var i = 0, len = brandsObject.length; i < len; i++) {

        content +=
            "<li class='item'>" +
            "<img src='" + brandsObject[i].image + "' " +
            "alt='" + brandsObject[i].title + "' " +
            "width='46' " +
            "height='30' " +
            "class='brands visa'>" +
            "</li>";

    }

    jQuery('.credit-card-types').each(function () {
      jQuery(this).html(content);
    });
}