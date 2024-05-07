define([
    'Magento_Ui/js/modal/modal',
    "mage/url",
    'jquery',
    'mage/translate',
    'loader'
], function (modal, mageUrl, $) {
    return function (config) {
        const successModalOptions = {
            responsive: true,
            innerScroll: true,
            title: $.mage.__('Start validation'),
            buttons: [{
                text: $.mage.__('Close'),
                click: function () {
                    this.closeModal();
                }
            }]
        };
        modal(successModalOptions, $('#modal-success-content'));

        const errorModalOptions = { ...successModalOptions };
        errorModalOptions.title = $.mage.__('Error!');
        modal(errorModalOptions, $('#modal-error-content'));

        $('#pagarme-kyc-start-validation').on('click', async function (){
            const body = $('body');
            try {
                body.loader('show');
                const url = mageUrl.build(`rest/V1/pagarme/marketplace/recipient/kyc/link/${config.id}`);
                const response = await $.get(mageUrl.build(url));
                body.loader('hide');
                if (response) {
                    $('#pagarme-kyc-qrcode').attr('src', response.qr_code);
                    $('#pagarme-kyc-link').attr('href', response.url);
                    $('#modal-success-content .pagarme-kyc-modal').show();

                    $('#modal-success-content').modal('openModal');
                }
            } catch (exception) {
                body.loader('hide');
                $('#modal-error-content .pagarme-kyc-modal').show();
                $('#modal-error-content').modal('openModal');
            }
        });
    };
});
