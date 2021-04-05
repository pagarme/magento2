require([
    "jquery",
    "jquery/ui",
], function ($) {
    "use strict";

    var PagarmeAdmin = {};

    $(document).ready(function(){
    });

    PagarmeAdmin.placeOrder = function (order) {
        var code = order.paymentMethod;
        var method = code.split("_");

        var submitFunction = order.submit;
        window.PagarmeAdmin[method[1]].placeOrder(submitFunction);
    };

    PagarmeAdmin.updateTotals = function (action, interest, amount) {
        var amountFormatted = "R$" + this.formatMoney(amount);
        jQuery(".pagarme-tax").remove();
        if (action === "remove-tax") {
            jQuery("#order-totals table tr:last .price").html(amountFormatted);
            return;
        }

        var interestFormatted = "R$" + this.formatMoney(interest);
        var html = this.getTaxHtml(interestFormatted);
        jQuery("#order-totals table tr:last").before(html);
        jQuery("#order-totals table tr:last .price").html(amountFormatted);
    };

    PagarmeAdmin.formatMoney = function (amount) {
        var tmp = amount.toString();
        tmp = tmp.replace(/\D/g, "");
        tmp = tmp.replace(/([0-9]{2})$/g, ",$1");
        if( tmp.length > 6 ) {
            tmp = tmp.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
        }

        return tmp;
    };

    PagarmeAdmin.getTaxHtml = function (interest) {
        return "<tr id=\"pagarme-tax\" class=\"row-totals pagarme-tax\">" +
        "<td style=\"\" class=\"admin__total-mark\" colspan=\"1\"> Tax </td>" +
        "<td style=\"\" class=\"admin__total-amount\">" +
        "   <span class=\"price\">" + interest + "</span>"+
        "</td>" +
        "</tr>";
    };

    window.PagarmeAdmin = PagarmeAdmin;
});
