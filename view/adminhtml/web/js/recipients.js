require([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $(document).ready(function(){
        $("#form-recipients").submit(formSubmit);

        hideElementByMenuSelectValue(
            $("#existing_recipient").val(),
            "pagarme_id"
        );

        $("#existing_recipient").on('change', function () {
            hideElementByMenuSelectValue(
                $(this).val(),
                "pagarme_id"
            )
        });

        $("#select-webkul-seller").on('change', function () {
            $('#external-id').val(
                $('#select-webkul-seller').val()
            );

            $('#recipient-name').val(
                $('#select-webkul-seller').find(":selected").attr("sellername")
            );

            $('#email-recipient').val(
                $('#select-webkul-seller').find(":selected").attr("email")
            );

            $('#email-recipient').val(
                $('#select-webkul-seller').find(":selected").attr("email")
            );

            $("#document-type").val('cpf');

            $("#document").val(
                $('#select-webkul-seller').find(":selected").attr("document")
            );
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
            loadRecipient(JSON.parse(editRecipient));
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

              loadRecipient(response.recipient);
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

    function fillFieldsWithSameValue()
    {
        $("#document-type").on('change', function() {
            $("#holder-document-type").val($(this).val());
        });

        $("#document").on('change', function() {
            $("#holder-document").val($(this).val());
        });

        $("#holder-document").attr("disabled", true);
        $("#holder-document-type").attr("disabled", true);
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

    function loadRecipient(recipient) {
        $("#external-id").val(recipient.externalId);
        $("#recipient-name").val(recipient.name);
        $("#email-recipient").val(recipient.email);
        $("#document").val(recipient.document);
        $("#type").val(recipient.type);
        // Bank Infos
        $("#holder-name").val(recipient.default_bank_account.holder_name);
        $("#holder-document-type").val(recipient.default_bank_account.holder_type == 'individual' ? 'cpf' : 'cnpj');
        $("#holder-document").val(recipient.document);
        $("#bank").val(recipient.default_bank_account.bank);
        $("#branch-number").val(recipient.default_bank_account.branch_number);
        $("#branch-check-digit").val(recipient.default_bank_account.branch_check_digit);
        $("#account-number").val(recipient.default_bank_account.account_number);
        $("#account-check-digit").val(recipient.default_bank_account.account_check_digit);
        $("#account-type").val(recipient.default_bank_account.type);
        // Transfer Infos
        $("#transfer-enabled").val(recipient.transfer_settings.transfer_enabled ? 1 : 0);
        $("#transfer-interval").val(recipient.transfer_settings.transfer_interval);
        fillTransferDayValuesByTransferInterval();
        $("#transfer-day").val(recipient.transfer_settings.transfer_day);

        hideElementByMenuSelectValue(
            $("#transfer-enabled").val(),
            "transfer-day-div"
        );

        hideElementByMenuSelectValue(
            $("#transfer-enabled").val(),
            "transfer-interval-div"
        );

        $("#document").attr("disabled", true);
        $("#document-type").attr("disabled", true);
    }

});
