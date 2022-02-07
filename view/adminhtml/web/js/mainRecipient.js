require([
    "jquery",
    "jquery/ui",
], function ($) {
    function MainRecipient() {
        this.getInfoId = 'pagarme-get-info';
        this.url = null;

        this.setup = function() {
            const button = document.getElementById(this.getInfoId);

            if (!button) {
                return;
            }

            button.onclick = this.getInfo;
            this.url = button.getAttribute('api-url');
        }

        this.getInfo = e => {
            e.preventDefault();

            const recipientId = document.querySelector("[id$=main_recipient_id][type=text]").value;

            fetch(this.url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json, text/plain, */*',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({recipientId})
            })
                .then(res => res.json())
                .then(res => {
                    const response = JSON.parse(res);
                    if (response.code == 404) {
                        alert(response.message);
                        return;
                    }

                    this.loadRecipient(response.recipient);
                });
        }

        this.loadRecipient = function (recipient) {
            document.querySelector("[id$=main_recipient_name][type=text]").value = recipient.name;
            document.querySelector("[id$=main_recipient_email][type=text]").value = recipient.email;
            document.querySelector("[id$=main_recipient_document_type] select").value = recipient.type;
            document.querySelector("[id$=main_recipient_document_number][type=text]").value = recipient.document;
        }

        this.setup();

        return this;
    }

    $(document).ready(function (){
        MainRecipient();
    });

});
