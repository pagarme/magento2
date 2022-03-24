require([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $(document).ready(function(){
        $("#form-recipients").submit(formSubmit);

        hideMainInformations();
        hideElementByMenuSelectValue(
            $("#existing_recipient").val(),
            "pagarme_id"
        );
        $('#recipient-id').attr('disabled', true);

        $("#existing_recipient").on('change', function () {
            $('#recipient-id').attr('disabled', $(this).val() == 0 ? true : false);

            hideElementByMenuSelectValue(
                $(this).val(),
                "pagarme_id"
            )
        });

        $("#select-webkul-seller").on('change', function () {
            const externalId = $('#select-webkul-seller').val();
            $('#external-id').val(
                externalId
            );
            $( "#external-id" ).prop( "readonly", !!externalId );

            const recipientName = $('#select-webkul-seller')
                .find(":selected")
                .attr("sellername");
            $('#recipient-name').val(
                recipientName
            );
            $( "#recipient-name" ).prop( "readonly", !!recipientName );

            const recipientEmail = $('#select-webkul-seller')
                .find(":selected")
                .attr("email");
            $('#email-recipient').val(
                recipientEmail
            );
            $( "#email-recipient" ).prop( "readonly", !!recipientEmail );

            $("#document-type").val('cpf');
            $( "#document-type" ).addClass('readonly');

            const recipientDocument = $('#select-webkul-seller')
                .find(":selected")
                .attr("document");

            $("#document").val(
                recipientDocument
            );
            
            $("#holder-document-type").val('cpf');
            $("#holder-document-type").addClass('readonly');

            $("#holder-document").val(
                recipientDocument
            );

            $("#document").prop( "readonly", !!recipientDocument );
            $("#holder-document").prop( "readonly", !!recipientDocument );
            
            if (externalId == "") {
                $("#document-type").removeClass('readonly');
                $("#holder-document-type").removeClass('readonly');
                hideMainInformations();
                return;
            }

            showMainInformations();
        });

        hideElementByMenuSelectValue(
            $("#transfer-enabled").val(),
            "transfer-interval-div"
        );

        $("#transfer-enabled").on('change', function () {
            hideElementByMenuSelectValue(
                $(this).val(),
                "transfer-interval-div"
            )
        });

        hideElementByMenuSelectValue(
            $("#transfer-enabled").val(),
            "transfer-day-div"
        );

        $("#transfer-enabled").on('change', function () {
            hideElementByMenuSelectValue(
                $(this).val(),
                "transfer-day-div"
            )
        });

        $("#document-type").on('change', function() {
            fillTypeValueByDocumentType();
            changeDocumentSizeByDocumentType();
        });

        $("#transfer-interval").on('change', function () {
            fillTransferDayValuesByTransferInterval();
        });

        fillFieldsWithSameValue();

        changeDocumentSizeByDocumentType();

        var editRecipient = $("#edit-recipient").val();
        if (editRecipient.length > 0) {
            $("#select-seller").hide();
            loadRecipient(JSON.parse(editRecipient),false);
        }

        $("#search-recipient-id").on('click', searchRecipient);
    });

    function searchRecipient(e) {
        e.preventDefault();

        var recipientId = $("#recipient-id").val();
        var url =  $("#url-search-recipient-id").val();

        console.log(JSON.stringify($("#recipient-id").serialize()));

        fetch(url, {
          method: 'POST',
          headers: {
            'Accept': 'application/json, text/plain, */*',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({recipientId})
        }).then(res => res.json())
          .then(res => {
              const response = JSON.parse(res);
              if (response.code != 200) {
                  alert(response.message);
                  return;
              }

              loadRecipient(response.recipient, true);
          });

    }

    function formSubmit(e) {
        e.preventDefault();

        var isValidEmail = validateEmail($("#email-recipient").val());

        if(!isValidEmail) {
            alert("Invalid email");
            return;
        }

        toogleSaveButton();

        var dataSerialize = jQuery(this).serialize();
        var url =  $("#url-post").val();

        jQuery.ajax({
            method: "POST",
            url: url,
            contentType: 'application/json',
            data : JSON.stringify(dataSerialize),
            success: function(data) {
                data = JSON.parse(data);
                if (data.code === 200) {
                    alert(data.message);
                    return window.history.back();
                }
                alert(data.message);
            },
            complete: function () {
                toogleSaveButton()
            }
        });
    }

    function toogleSaveButton()
    {
        var disabled = $("#save-button").prop('disabled');
        if (disabled) {
            $("#save-button").attr('disabled', false);
            $("#save-button span").html("Save");
            return;
        }
        $("#save-button").attr('disabled', true);
        $("#save-button span").html("Saving");
    }

    function hideElementByMenuSelectValue(value, elementIdToHide)
    {
        document.getElementById(elementIdToHide).style.display
            = value == 1 ? 'block' : 'none';
    }

    function hideMainInformations(){
        const mainInformationTags = [
            '#document-div',
            '#document-type-div',
            '#email-recipient-div',
            '#recipient-name-div',
            '#external-id-div'
        ];

        mainInformationTags.forEach(tag => {
            hideElement(
                $(tag)
            );
        });
    }

    function showMainInformations(){
        const mainInformationTags = [
            '#document-div',
            '#document-type-div',
            '#email-recipient-div',
            '#recipient-name-div',
            '#external-id-div'
        ];

        mainInformationTags.forEach(tag => {
            showElement(
                $(tag)
            );
        });
    }

    function hideElement(element){
        element.hide();
    }

    function showElement(element){
        element.show();
    }

    function fillFieldsWithSameValue()
    {
        $("#document-type").on('change', function() {
            $("#holder-document-type").val($(this).val());
        });

        $("#document").on('change', function() {
            $("#holder-document").val($(this).val());
        });

        $("#holder-document").attr("readonly", true);
        $("#holder-document-type").attr("readonly", true);
    }

    function fillTypeValueByDocumentType()
    {
        var documentTypeValue = $("#document-type").val();
        document.getElementById("type").value
            = documentTypeValue == 'cpf' ? 'individual' : 'company';
    }

    function fillTransferDayValuesByTransferInterval()
    {
        var transferDayValue = $("#transfer-interval").val();

        if (transferDayValue == 'Weekly') {
            $('#transfer-day').children().remove().end().append( '<option value="1">1</option>' );
            for (var i = 2; i < 6; i++) {
                $('#transfer-day').append( '<option value="' + i + '">' + i + '</option>' );
            }
        }

        if (transferDayValue == 'Monthly') {
            $('#transfer-day').children().remove().end().append( '<option value="1">1</option>' );
            for (var i = 2; i < 32; i++) {
                $('#transfer-day').append('<option value="' + i + '">' +  i + '</option>');
            }
        }

        if (transferDayValue == 'Daily') {
            $('#transfer-day').children().remove().end().append( '<option value="0">0</option>' );
        }
    }

    function validateEmail(email) {
        var validationExpression = /\S+@\S+\.\S+/;
        return validationExpression.test(email);
    }

    function changeDocumentSizeByDocumentType() {
        var documentType = $("#document-type").val();

        if (documentType == "cpf") {
            $("#document").attr("maxlength", 11);
        }

        if (documentType == "cnpj") {
            $("#document").attr("maxlength", 14);
        }
    }

    function buildRecipientObject(recipient){
        return {
            "#holder-name": recipient.default_bank_account.holder_name,
            "#holder-document-type": recipient.default_bank_account.holder_type == 'individual' ? 'cpf' : 'cnpj',
            "#holder-document":recipient.document,
            "#bank": recipient.default_bank_account.bank,
            "#branch-number": recipient.default_bank_account.branch_number,
            "#branch-check-digit": recipient.default_bank_account.branch_check_digit,
            "#account-number": recipient.default_bank_account.account_number,
            "#account-check-digit": recipient.default_bank_account.account_check_digit,
            "#account-type": recipient.default_bank_account.type,
            "#transfer-enabled": recipient.transfer_settings.transfer_enabled ? 1 : 0,
            "#transfer-interval": recipient.transfer_settings.transfer_interval,
            "#transfer-day": recipient.transfer_settings.transfer_day
        };
    }

    function loadRecipient(recipient, wasSearched) {
        const recipientObject = buildRecipientObject(recipient);

        for (const elementId in recipientObject) {
            if (!Object.hasOwnProperty.call(recipientObject, elementId))
                continue;

            const recipientValue = recipientObject[elementId];
            const element = $(elementId);
            element.val(recipientValue);
            if (wasSearched) {
                element.attr("readonly", true);
                if (element.is('select')) {
                    element.addClass('readonly');
                }
            }
            

        }

        fillTransferDayValuesByTransferInterval();

        hideElementByMenuSelectValue(
            $("#transfer-enabled").val(),
            "transfer-day-div"
        );

        hideElementByMenuSelectValue(
            $("#transfer-enabled").val(),
            "transfer-interval-div"
        );

        $('#recipient-id').attr('disabled', false);

        $("#document").attr("readonly", true);
        $("#document-type").attr("readonly", true);

        if (wasSearched) return;

        $('#external-id').val(recipient.externalId);
        $("#external-id").attr("readonly", true);
        $("#external-id-div").show();

        $('#existing_recipient').val('1');
        $("#existing_recipient").attr("readonly", true);
        
        $("#use_existing_pagarme_id").hide();

        $('#pagarme_id').show();
        $('#recipient-id').val(recipient.id);

        $("#recipient-name").val(recipient.name);
        $("#recipient-name").attr("readonly", true);

        $('#email-recipient').val(recipient.email);
        $('#email-recipient').attr("readonly", true);

        $('#email-recipient').val(recipient.email);
        $('#email-recipient').attr("readonly", true);

        $('#email-recipient').val(recipient.email);
        $('#email-recipient').attr("readonly", true);

        $('#document').val(recipient.document);
        $('#document').attr("readonly", true);

        $('#holder-document').val(recipient.document);
        $('#holder-document').attr("readonly", true);

        $('#holder-document-type').val(recipient.default_bank_account.holder_type == 'individual' ? 'cpf' : 'cnpj');
        $('#holder-document-type').attr("readonly", true);
        $("#holder-document-type").addClass('readonly');
    }

});
