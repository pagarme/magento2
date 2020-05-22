require([
    "jquery",
    "jquery/ui",
], function ($) {
    'use strict';

    var MundipaggAdmin = {};

    $(document).ready(function(){
    });

    MundipaggAdmin.placeOrder = function (order) {
        var code = order.paymentMethod;
        var method = code.split("_");

        var submitFunction = order.submit;
        window.MundipaggAdmin[method[1]].placeOrder(submitFunction)
    }

    MundipaggAdmin.switchPaymentMethod = function (originalFunction, amount) {
        MundipaggAdmin.updateTotals('remove-tax', 0, amount);
        originalFunction();
    }

    MundipaggAdmin.bindSwitchPaymentMethod = function (payment, amount) {
        var switchFunction = payment.switchMethod;
        payment.switchMethod = this.switchPaymentMethod.bind(this, switchFunction, amount);
    }

    MundipaggAdmin.updateTotals = function (action, interest, amount) {
        var amountFormatted = "R$" + this.formatMoney(amount);
        jQuery(".mundipagg-tax").remove()
        if (action == 'remove-tax') {
            jQuery("#order-totals table tr:last .price").html(amountFormatted);
            return;
        }

        var interestFormatted = "R$" + this.formatMoney(interest);
        var html = this.getTaxHtml(interestFormatted);
        jQuery("#order-totals table tr:last").before(html);
        jQuery("#order-totals table tr:last .price").html(amountFormatted);
    }

    MundipaggAdmin.formatMoney = function (amount) {
        var tmp = amount.toString();
        var tmp = tmp.replace(/\D/g, "");
        tmp = tmp.replace(/([0-9]{2})$/g, ",$1");
        if( tmp.length > 6 )
            tmp = tmp.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");

        return tmp;
    }

    MundipaggAdmin.getTaxHtml = function (interest) {
        return '<tr id="mundipagg-tax" class="row-totals mundipagg-tax">' +
        '<td style="" class="admin__total-mark" colspan="1"> Tax </td>' +
        '<td style="" class="admin__total-amount">' +
        '   <span class="price">' + interest + '</span>'+
        '</td>' +
        '</tr>';
    }

    window.MundipaggAdmin = MundipaggAdmin
});