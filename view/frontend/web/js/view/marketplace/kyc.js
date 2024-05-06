define([
    'Magento_Ui/js/modal/modal',
    "mage/url",
    'jquery',
    'mage/translate',
    'loader'
], function (modal, mageUrl, $) {
    return function (config) {

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
                    $('.pagarme-kyc-modal').show();

                    const options = {
                        responsive: true,
                        innerScroll: true,
                        // @todo: Using portuguese title for the translation did't work.
                        title: $.mage.__('Iniciar validação'),
                        buttons: [{
                            text: $.mage.__('Close'),
                            click: function () {
                                this.closeModal();
                            }
                        }]
                    };

                    const popup = modal(options, $('#modal-content'));
                    $('#modal-content').modal('openModal');
                }
            } catch (exception) {
                body.loader('hide');
                // this.mageAlert(exception?.responseJSON?.message, $.mage.__('Error!'));
            }
        });
    };
});
