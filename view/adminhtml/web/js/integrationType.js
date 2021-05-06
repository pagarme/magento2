require([
    "jquery",
    "jquery/ui",
], function ($) {
    $(document).ready(function(){
        var integrationTypeElement = document.getElementById('payment_us_pagarme_pagarme_pagarme_pagarme_global_integration_type');
        changeCommentsByIntegrionType(integrationTypeElement.value);

        integrationTypeElement.addEventListener('change', function () {
            changeCommentsByIntegrionType(this.value);
            console.log(integrationTypeElement.value);
        });

        function changeCommentsByIntegrionType(integrationType) {
            document.getElementById('soft_description_max_size').innerHTML = integrationType === '0' ? 13 : 22;
            document.getElementById('installments_max_size').innerHTML = integrationType === '0' ? 12 : 24;
        }
    });
});
