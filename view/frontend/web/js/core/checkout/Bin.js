define(['jquery'], ($) => {
    return class Bin {
        constructor() {
            this.binValue = '';
            this.brand = '';
            this.checkedBins = [0];
            this.selectedBrand = '';
        }
        init(newValue) {

            const formattedNewValue = this.formatNumber(newValue);

            if (
                typeof this.checkedBins != 'undefined' &&
                typeof this.checkedBins[formattedNewValue] != 'undefined'
            ){
                this.binValue = formattedNewValue;
                this.selectedBrand = this.checkedBins[formattedNewValue];
                return;
            }

            if (this.validate(formattedNewValue)) {
                this.binValue = formattedNewValue;
                this.getBrand().always(function (data) {
                    this.saveBinInformation(data);
                }.bind(this));
                
                return;
            }

            this.selectedBrand = '';
        }
        formatNumber(number) {
            const newValue = String(number);
            return newValue.slice(0, 6);
        }
        validate(newValue) {
            if (newValue.length == 6 && this.binValue != newValue) {
                return true;
            }

            return false;
        }
        getBrand() {
            const bin = this.binValue.slice(0, 6);

            return $.ajax({
                type: 'GET',
                dataType: 'json',
                url: 'https://api.mundipagg.com/bin/v1/' + bin,
                async: false,
                cache: true,
            });
        }
        saveBinInformation(data) {
            if (typeof this.checkedBins == 'undefined') {
                this.checkedBins = [];
            }

            this.checkedBins[this.binValue] = data.status !== 404 ? data.brand : '';
            this.selectedBrand = data.status !== 404 ? data.brand : '';
        }
    };
});
