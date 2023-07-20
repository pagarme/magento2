define(['jquery', 'numberFormatter'], ($, numberFormatter) => {
    return (config) => {
        $('.charge-button').click(function(){
            const action = $(this).data('action');
            const charge = $(this).data('charge');
            const order = $(this).data('order');
            const amount = $(this).parent()
                .parent()
                .children('td.amount')
                .children()
                .val();
            const msg = $(this).data('message');
            if (confirm(msg)) {
                let serviceUrl = config.urlCapture;

                if (action === 'cancel') {
                    serviceUrl = config.urlCancel;
                }
                serviceUrl += `amount/${amount}/chargeId/${charge}/orderId/${order}`;

                return $.ajax({
                    method: 'GET',
                    beforeSend: function(request) {
                        request.setRequestHeader('Content-type', 'application/json');
                    },
                    url: serviceUrl,
                    showLoader: true,
                    cache: false,
                    success: function(data) {
                        if (data.code === 200) {
                            document.location.reload();
                        }
                        alert(data.message);
                    }
                });
            }
        });

        const amountValueElement = $('.amount-value');
        amountValueElement.keyup(function(){
            let amountValue = $(this).val();
            amountValue = numberFormatter.formatToPrice(amountValue);
            return $(this).val(amountValue);
        });
        amountValueElement.keyup();
    }
});
