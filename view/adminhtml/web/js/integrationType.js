require([
    "jquery",
    "jquery/ui",
], function ($) {
    "use strict";
    $(document).ready(function(){
        const integrationTypeElement = $('select[id*="pagarme_pagarme_global_is_gateway_integration_type"]'),
              installmentsNumberElements = $('input[id*="pagarme_creditcard_installments"][id*="installments_number"]'),
              installmentsWithoutInterestElements = $('input[id*="pagarme_creditcard_installments"][id*="max_without_interest"]');

        integrationTypeElement.change(function() {
            const integrationType = $(this).val();
            changeCommentsByIntegrionType(integrationType);
            changeInstallmentsValidation(integrationType);
        })
        .change();

        installmentsNumberElements.change(function() {
            const installmentsNumberVal = $(this).val(),
                installmentsWithoutInterestElement = $(this).closest('fieldset[id*="pagarme_creditcard_installments"]')
                    .find('input[id*="_pagarme_creditcard_installments_"][id*="_max_without_interest"]').first();

            if (installmentsNumberVal !== '') {
                changeInstallmentsWithoutInterestValidation(installmentsWithoutInterestElement, installmentsNumberVal)
            }
        })
        .change();

        installmentsWithoutInterestElements.change(function(){
            const installmentsNumberParent = $(this).closest('fieldset.config')
                .find('input[id*="_pagarme_creditcard_installments_"][id*="_installments_number"]').first();

            if (installmentsNumberParent.val() === '') {
                $(this).val('');
            }
        });

        function changeCommentsByIntegrionType(integrationType) {
            const installmentsMaxSizeElements =
                $('[id^="installments_max_size"]');


            if (installmentsMaxSizeElements){
                installmentsMaxSizeElements.each(function(){
                    $(this).html(integrationType === '0' ? 12 : 24);
                });
            }
        }

        function changeInstallmentsValidation(integrationType) {
            if (integrationType === '0') {
                installmentsNumberElements.toggleClass('number-range-1-24', false);
                installmentsNumberElements.toggleClass('number-range-1-12', true);
            } else {
                installmentsNumberElements.toggleClass('number-range-1-12', false);
                installmentsNumberElements.toggleClass('number-range-1-24', true);
            }
        }

        function changeInstallmentsWithoutInterestValidation(element, maxRange) {
            element.removeClass(function(index, classNames) {
                return (classNames.match(/(^|\s)number-range-1-\S+/g) || []).join(' ');
            })
            .addClass('number-range-1-' + maxRange);
        }
    });
});
