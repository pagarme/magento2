define(['jquery'], ($) => {
    return (config) => {
        $('.copy-pix').click(async () => {
            const qrCodeJqueryElement = $('#pagarme_qr_code');

            if (qrCodeJqueryElement.length < 1) {
                return;
            }

            const rawCode = qrCodeJqueryElement.attr("value");
            const alternativeCopyQrCode = () => {
                qrCodeJqueryElement.show();
                qrCodeJqueryElement.focus();
                qrCodeJqueryElement.select();
                alert(config.errorCopyMessage);
            };

            if (window.isSecureContext && navigator.clipboard) {
                try {
                    await navigator.clipboard.writeText(rawCode);
                    alert(config.successCopyMessage);
                } catch (err) {
                    alternativeCopyQrCode(rawCode);
                }
                return;
            }

            const [qrCodeDOMElement] = qrCodeJqueryElement;
            qrCodeDOMElement.select();
            qrCodeDOMElement.setSelectionRange(0, qrCodeJqueryElement.val().length);
            try {
                document.execCommand('copy', false);
                alert(config.successCopyMessage);
            } catch (err) {
                alternativeCopyQrCode(rawCode);
            }
        });
    };
});
