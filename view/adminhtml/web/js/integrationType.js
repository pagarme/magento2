require([
    "jquery",
    "jquery/ui",
], function ($) {
    $(document).ready(function(){
        var integrationTypeElement = $("select[id*='pagarme_pagarme_global_is_gateway_integration_type']");
        changeCommentsByIntegrionType(integrationTypeElement.val());

        changeAdminCssBorders();

        integrationTypeElement.on("change", function () {
            changeCommentsByIntegrionType($(this).val());
        });

        function changeCommentsByIntegrionType(integrationType) {
            const softDescriptionMaxSizeElement =
                document.getElementById('soft_description_max_size');

            const installmentsMaxSizeElement =
                document.getElementById('installments_max_size');

            if (softDescriptionMaxSizeElement){
                softDescriptionMaxSizeElement.innerHTML =
                    integrationType === '0' ? 13 : 22;
            }

            if (installmentsMaxSizeElement){
                installmentsMaxSizeElement.innerHTML =
                    integrationType === '0' ? 12 : 24;
            }
        }

        function changeAdminCssBorders() {
            var transactionDiv = $("tr[id*='pagarme_pagarme_transaction']").find('.section-config');
            transactionDiv.css('border', 0);
        }
    });
});
