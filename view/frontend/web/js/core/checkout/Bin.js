var Bin = function () {
    binValue = '',
    brand = '',
    checkedBins = [0]
    selectedBrand = ''
};

Bin.prototype.init = function (newValue) {

    var newValue = this.formatNumber(newValue);

    if (
        typeof this.checkedBins != 'undefined' &&
        typeof this.checkedBins[newValue] != 'undefined'
    ){
        this.binValue = newValue;
        this.selectedBrand = this.checkedBins[newValue];
        return;
    }

    if (this.validate(newValue)) {
        this.binValue = newValue;
        this.getBrand().done(function (data) {
            this.saveBinInformation(data);
        }.bind(this));

        return;
    }

    this.selectedBrand = '';
};

Bin.prototype.formatNumber = function (number) {
    var newValue = String(number);
    return newValue.slice(0, 6);
};

Bin.prototype.validate = function (newValue) {
    if (newValue.length == 6 && this.binValue != newValue) {
        return true;
    }

    return false;
};

Bin.prototype.getBrand = function () {
    var bin = this.binValue.slice(0, 6);

    return jQuery.ajax({
        type: 'GET',
        dataType: 'json',
        url: 'https://api.mundipagg.com/bin/v1/' + bin,
        async: false,
        cache: true,
    });
};

Bin.prototype.saveBinInformation = function (data) {
    if (typeof this.checkedBins == 'undefined') {
        this.checkedBins = [];
    }

    this.checkedBins[this.binValue] = data.brand;
    this.selectedBrand = data.brand;
};