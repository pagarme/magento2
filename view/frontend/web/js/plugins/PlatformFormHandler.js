var FormHandler = function () {
    formObject = {}
};

FormHandler.prototype.init = function (formObject) {
    this.formObject = formObject;
}

FormHandler.prototype.switchBrand = function (brand) {

    jQuery(this.formObject.containerSelector + ' .brands').css('filter', 'grayscale(100%)');

    if (typeof brand != 'undefined' && brand.length > 0) {
        jQuery(this.formObject.containerSelector + ' .' + brand).css('filter', 'none');
        this.formObject.creditCardBrand.val(brand);

        return;
    }

}