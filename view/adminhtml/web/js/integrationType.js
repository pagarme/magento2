require([
    "jquery",
    "jquery/ui",
], function ($) {
    $(document).ready(function(){
        var integrationTypeElement = $("select[id*='pagarme_pagarme_global_is_gateway_integration_type']");
        changeCommentsByIntegrionType(integrationTypeElement.value);

        integrationTypeElement.on("change", function () {
            changeCommentsByIntegrionType(this.value);
        });

        function changeCommentsByIntegrionType(integrationType) {
            document.getElementById('soft_description_max_size').innerHTML = integrationType === '0' ? 13 : 22;
            document.getElementById('installments_max_size').innerHTML = integrationType === '0' ? 12 : 24;
        }
    });
});
