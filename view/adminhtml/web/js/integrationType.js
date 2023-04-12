require([
    "jquery",
    "jquery/ui",
], function ($) {
    "use strict";
    $(document).ready(function(){
        const integrationTypeElement = $('select[id*="pagarme_pagarme_global_is_gateway_integration_type"]'),
              softDescriptionElements = $('input[id$="_soft_description"]'),
              installmentsNumberElements = $('input[id*="pagarme_creditcard_installments"][id*="installments_number"]'),
              installmentsWithoutInterestElements = $('input[id*="pagarme_creditcard_installments"][id*="max_without_interest"]');

        integrationTypeElement.change(function() {
            var integrationType = $(this).val(),
                creditcardSoftDescriptionElement = $('input[id$="_creditcard_soft_description"]'),
                softDescriptionMaxLength = integrationType === '0' ? 13 : 22;
            changeCommentsByIntegrionType(integrationType);
            softDescriptionCounter(creditcardSoftDescriptionElement, softDescriptionMaxLength);
            changeInstallmentsValidation(integrationType);
        })
        .change();

        softDescriptionElements.keyup(function() {
            var integrationType = integrationTypeElement.val(),
                softDescriptionMaxLength = integrationType === '0' ? 13 : 22;
            softDescriptionCounter($(this), softDescriptionMaxLength);
        })
        .keyup();

        softDescriptionElements.change(function() {
            var softDescription = $(this).val();
            softDescriptionElements.each(function() {
                if(softDescription !== '' &&  $(this).val() === '') {
                    $(this).val(softDescription);
                }
                $(this).keyup();
            });
        })
        .change();

        installmentsNumberElements.change(function() {
            var installmentsNumberVal = $(this).val(),
                installmentsWithoutInterestElement = $(this).closest('fieldset[id*="pagarme_creditcard_installments"]')
                    .find('input[id*="_pagarme_creditcard_installments_"][id*="_max_without_interest"]').first();

            if (installmentsNumberVal !== '') {
                changeInstallmentsWithoutInterestValidation(installmentsWithoutInterestElement, installmentsNumberVal)
            }
        })
        .change();

        installmentsWithoutInterestElements.change(function(){
            var installmentsNumberParent = $(this).closest('fieldset.config')
                .find('input[id*="_pagarme_creditcard_installments_"][id*="_installments_number"]').first();
            
            if (installmentsNumberParent.val() === '') {
                $(this).val('');
            }
        });

        function changeCommentsByIntegrionType(integrationType) {
            const softDescriptionMaxSizeElement =
                document.getElementById('soft_description_max_size');

            const softDescriptionCounterMaxSizeElement =
                document.getElementById('creditcard_soft_description_counter_max_size');

            const installmentsMaxSizeElements =
                $('[id^="installments_max_size"]');

            if (softDescriptionMaxSizeElement){
                softDescriptionMaxSizeElement.innerHTML =
                    integrationType === '0' ? 13 : 22;
            }

            if (softDescriptionCounterMaxSizeElement){
                softDescriptionCounterMaxSizeElement.innerHTML =
                    integrationType === '0' ? 13 : 22;
            }

            if (installmentsMaxSizeElements){
                installmentsMaxSizeElements.each(function(){
                    $(this).html(integrationType === '0' ? 12 : 24);
                });
            }
        };

        function softDescriptionCounter(element, maxLength) {
            var counter =  element.parent().find('[id$="_soft_description_counter_current"]'),
                length = element.val().length;
            if (length >= maxLength) {
                element.val(element.val().substring(0, maxLength));
                counter.text(maxLength);
                counter.parent().addClass('limit-reached');
            } else {
                counter.text(length);
                counter.parent().removeClass('limit-reached');
            }
        };

        function changeInstallmentsValidation(integrationType) {
            if (integrationType === '0') {
                installmentsNumberElements.toggleClass('number-range-1-24', false);
                installmentsNumberElements.toggleClass('number-range-1-12', true);
            } else {
                installmentsNumberElements.toggleClass('number-range-1-12', false);
                installmentsNumberElements.toggleClass('number-range-1-24', true);
            }
        };

        function changeInstallmentsWithoutInterestValidation(element, maxRange) {
            element.removeClass(function(index, classNames) {
                return (classNames.match(/(^|\s)number-range-1-\S+/g) || []).join(' ');
            })
            .addClass('number-range-1-' + maxRange);
        };
    });
});
