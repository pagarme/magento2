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

            const recipientElements = this.getRecipientElements();

            const recipientId = document.querySelector("[id$=main_recipient_id][type=text]").value;

            if (recipientId == "") {
                recipientElements.forEach(recipientElement => {
                    if (recipientElement.length) recipientElement.hide();
                });
            }
            

            const recipientElementInputs = this.getRecipientInputs();
            for (const recipientAttribute in recipientElementInputs) {
                const recipientAttributeElement = recipientElementInputs[recipientAttribute];

                if (!recipientAttributeElement) continue;
                recipientAttributeElement.setAttribute('readonly', true);
            }
        }

        this.getRecipientElements = function(){
            const recipientNameInput = $('tr[id*="pagarme_marketplace_main_recipient_name"]');
            const recipientDocumentType = $('tr[id*="pagarme_marketplace_main_recipient_document_type"]');
            const recipientEmail = $('tr[id*="pagarme_marketplace_main_recipient_email"');
            const recipientDocumentNumber = $('tr[id*="pagarme_marketplace_main_recipient_document_number"');

            return [
                recipientNameInput,
                recipientDocumentType,
                recipientEmail,
                recipientDocumentNumber
            ];
        }

        this.getRecipientInputs = function(){
            const recipientNameInput = document
                .querySelector("[id$=main_recipient_name][type=text]");
            const recipientEmailInput = document
                .querySelector("[id$=main_recipient_email][type=text]");
            const recipientDocumentTypeSelect = document
                .querySelector("[id$=main_recipient_document_type] select");
            const recipientDocumentInput = document
                .querySelector("[id$=main_recipient_document_number][type=text]");


            return {
                "name": recipientNameInput,
                "email": recipientEmailInput,
                "type": recipientDocumentTypeSelect,
                "document": recipientDocumentInput
            }
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
                    if (response.code != 200) {
                        alert(response.message);
                        return;
                    }

                    this.loadRecipient(response.recipient);
                });
        }

        this.loadRecipient = function (recipient) {

            const recipientInputs = this.getRecipientInputs();

            for (const recipientAttribute in recipientInputs) {
                if (!Object.hasOwnProperty.call(recipientInputs, recipientAttribute)) continue;
                const recipientAttributeElement = recipientInputs[recipientAttribute];

                if (!recipientAttributeElement) continue;
                recipientAttributeElement.value = recipient[recipientAttribute];
                recipientAttributeElement.readOnly = true;
                recipientAttributeElement.disabled = false;
            }

            const recipientElements = this.getRecipientElements();

            recipientElements.forEach(recipientElement => {
                if (recipientElement.length) recipientElement.show();
            });
        }

        this.setup();

        return this;
    }

    $(document).ready(function (){
        MainRecipient();
    });

});
