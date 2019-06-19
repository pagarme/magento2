var Installments = function () {
    checkedBrand = [0],
    selectNewBrand = true;
}

Installments.prototype.init = function (Bin) {
    var newValue = Bin.selectedBrand;

    if (
        typeof this.checkedBrand != 'undefined' &&
        typeof this.checkedBrand[newValue] != 'undefined'
    ){
        this.selectNewBrand = false;
        return
    }
    this.saveBrandInformation(newValue);
    this.selectedBrand = true;
};

Installments.prototype.addOptions = function (element, installments) {

    if (installments != undefined && this.selectNewBrand) {
        jQuery(element).find('option').remove();

        installments.forEach(function (value) {
            opt = new Option(value.label, value.id);
            jQuery(element).append(opt);
        });
    }
}

Installments.prototype.saveBrandInformation = function (brand) {
    if (typeof this.checkedBrand == 'undefined') {
        this.checkedBrand = [];
    }

    this.checkedBrand[brand] = brand;
};
