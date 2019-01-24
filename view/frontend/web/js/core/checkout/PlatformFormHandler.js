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