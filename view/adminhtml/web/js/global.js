require([
    "jquery",
    "jquery/ui",
], function ($) {
    "use strict";
    $(document).ready(function () {
        const
            integrationButtonsRow = $('#row_payment_other_pagarme_pagarme_pagarme_pagarme_global_hub_integration'),
            integrationButtons = $('a.pagarme-integration-button'),
            integrateButton = $('#pagarme-integrate-button'),
            viewIntegrationButton = $('#pagarme-view-integration-button'),
            dashButton = $('#pagarme-dash-button'),
            integrationUseDefault = $('#payment_other_pagarme_pagarme_pagarme_pagarme_global_hub_integration_inherit'),
            integratedScope = $('[data-pagarme-integration-scope]').attr('data-pagarme-integration-scope'),
            hasIntegration =
                $('#row_payment_other_pagarme_pagarme_pagarme_pagarme_global_hub_integration .control-value')
                    .html() !== '';

        if (integrationUseDefault.is(':checked') && hasIntegration) {
            disableButtons();
        }

        integrationButtons.on('click', function(event) {
            const isDisabled = integrationButtonsRow.hasClass('is-disabled');

            if (isDisabled) {
                event.preventDefault();
            }
        });

        integrationUseDefault.on('change', function() {
            if ($(this).is(':checked')) {
                disableButtons();
                showIntegratedButtons();
                return;
            }

            enableButtons();
            hideIntegratedButtons();
        })

        function disableButtons() {
            if (!hasIntegration) {
                return;
            }

            integrationButtonsRow.addClass('is-disabled');
        }

        function enableButtons() {
            integrationButtonsRow.removeClass('is-disabled');
        }

        function hideIntegratedButtons() {
            if (integratedScope !== 'default') {
                return;
            }

            viewIntegrationButton.addClass('hidden');
            dashButton.addClass('hidden');
            integrateButton.removeClass('hidden');
        }

        function showIntegratedButtons() {
            if (
                (integratedScope === 'default' && !integrationUseDefault.is(':checked'))
                || !hasIntegration
            ) {
                return;
            }

            integrateButton.addClass('hidden');
            viewIntegrationButton.removeClass('hidden');
            dashButton.removeClass('hidden');
        }
    });
});
