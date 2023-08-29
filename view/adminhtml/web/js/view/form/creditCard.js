define([
    "jquery",
    'Pagarme_Pagarme/js/core/models/creditCardModel',
    "jquery/ui"
], function (
    $,
    CreditCardModel
) {
    'use strict';
    return (initializationConfig) => {
        $(document).ready(function(){
            const config = {
                isMultibuyerEnabled: false,
                order : window.order,
                payment : window.payment,
                installmentUrl: initializationConfig.installmentUrl
            };

            CreditCardModel.init(initializationConfig.code, config);
        });
    };
});
