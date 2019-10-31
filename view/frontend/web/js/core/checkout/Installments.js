var Installments = function () {
}

Installments.prototype.init = function () {
};

Installments.prototype.addOptions = function (element, installments) {

    if (installments != undefined) {
        jQuery(element).find('option').remove();

        installments.forEach(function (value) {
            opt = new Option(value.label, value.id);
            jQuery(opt).attr("interest", value.interest);
            jQuery(element).append(opt);
        });
    }
}
