require([
    "jquery",
    "jquery/ui",
], function ($) {
    "use strict";

    const maxPspInstallmentsValue = 18,
          maxGatewayInstallmentsValue = 24;

    $(document).ready(function () {
        const integrationTypeElement = $('select[id*="pagarme_pagarme_global_is_gateway_integration_type"]'),
              softDescriptionElements = $('input[id$="_soft_description"]'),
              installmentsNumberElements = $('input[id*="pagarme_creditcard_installments"][id*="installments_number"]'),
              installmentsWithoutInterestElements = $('input[id*="pagarme_creditcard_installments"][id*="max_without_interest"]');

        integrationTypeElement.change(function () {
            const integrationType = $(this).val(),
                creditcardSoftDescriptionElement = $('input[id$="_creditcard_soft_description"]'),
                softDescriptionMaxLength = integrationType === '0' ? 13 : 22;
            changeCommentsByIntegrationType(integrationType);
            softDescriptionCounter(creditcardSoftDescriptionElement, softDescriptionMaxLength);
            changeInstallmentsValidation(integrationType);
        })
            .change();

        softDescriptionElements.keyup(function () {
            const cssClasses = $(this).attr('class')
            const maximumLengthCssClass = 'maximum-length-';
            const positionMaximumLength = cssClasses.indexOf(maximumLengthCssClass) + maximumLengthCssClass.length;
            let softDescriptionMaxLength = cssClasses.substring(
                positionMaximumLength,
                positionMaximumLength + 2
            );

            if (integrationTypeElement.length > 0) {
                const integrationType = integrationTypeElement.val();
                softDescriptionMaxLength = integrationType === '0' ? 13 : 22;
            }

            changeSoftDescriptionComment($(this), softDescriptionMaxLength);
            softDescriptionCounter($(this), softDescriptionMaxLength);
        })
            .keyup();

        softDescriptionElements.change(function () {
            const softDescription = $(this).val();
            softDescriptionElements.each(function () {
                if (softDescription !== '' && $(this).val() === '') {
                    $(this).val(softDescription);
                }
                $(this).keyup();
            });
        })
            .change();

        installmentsNumberElements.change(function () {
            const installmentsNumberVal = $(this).val(),
                installmentsWithoutInterestElement = $(this).closest('fieldset[id*="pagarme_creditcard_installments"]')
                    .find('input[id*="_pagarme_creditcard_installments_"][id*="_max_without_interest"]').first();

            if (installmentsNumberVal !== '') {
                changeInstallmentsWithoutInterestValidation(installmentsWithoutInterestElement, installmentsNumberVal)
            }
        })
            .change();

        installmentsWithoutInterestElements.change(function () {
            const installmentsNumberParent = $(this).closest('fieldset.config')
                .find('input[id*="_pagarme_creditcard_installments_"][id*="_installments_number"]').first();

            if (installmentsNumberParent.val() === '') {
                $(this).val('');
            }
        });

        function changeCommentsByIntegrationType(integrationType) {
            const softDescriptionMaxSizeElement =
                document.getElementById('soft_description_max_size');

            const softDescriptionCounterMaxSizeElement =
                document.getElementById('creditcard_soft_description_counter_max_size');

            const installmentsMaxSizeElements =
                $('[id^="installments_max_size"]');

            if (softDescriptionMaxSizeElement) {
                softDescriptionMaxSizeElement.innerHTML =
                    integrationType === '0' ? 13 : 22;
            }

            if (softDescriptionCounterMaxSizeElement) {
                softDescriptionCounterMaxSizeElement.innerHTML =
                    integrationType === '0' ? 13 : 22;
            }

            if (installmentsMaxSizeElements) {
                installmentsMaxSizeElements.each(function () {
                    $(this).html(integrationType === '0' ? maxPspInstallmentsValue : maxGatewayInstallmentsValue);
                });
            }
        }

        function changeSoftDescriptionComment(element, maxSize) {
            const softDescriptionMaxSizeElement =
                element.closest('td.value').find('#soft_description_max_size');

            const softDescriptionCounterMaxSizeElement =
                element.closest('td.value').find('#creditcard_soft_description_counter_max_size');

            if (softDescriptionMaxSizeElement.length > 0) {
                softDescriptionMaxSizeElement.html(maxSize);
            }

            if (softDescriptionCounterMaxSizeElement.length > 0) {
                softDescriptionCounterMaxSizeElement.html(maxSize);
            }
        }

        function softDescriptionCounter(element, maxLength) {
            const counter = element.parent().find('[id$="_soft_description_counter_current"]'),
                length = element.val().length;
            if (length >= maxLength) {
                element.val(element.val().substring(0, maxLength));
                counter.text(maxLength);
                counter.parent().addClass('limit-reached');
            } else {
                counter.text(length);
                counter.parent().removeClass('limit-reached');
            }
        }

        function changeInstallmentsValidation(integrationType) {
            if (integrationType === '0') {
                installmentsNumberElements.toggleClass('number-range-1-' + maxGatewayInstallmentsValue, false);
                installmentsNumberElements.toggleClass('number-range-1-' + maxPspInstallmentsValue, true);
            } else {
                installmentsNumberElements.toggleClass('number-range-1-' + maxPspInstallmentsValue, false);
                installmentsNumberElements.toggleClass('number-range-1-' + maxGatewayInstallmentsValue, true);
            }
        }

        function changeInstallmentsWithoutInterestValidation(element, maxRange) {
            element.removeClass(function (index, classNames) {
                return (classNames.match(/(^|\s)number-range-1-\S+/g) || []).join(' ');
            })
                .addClass('number-range-1-' + maxRange);
        }
    });
});
