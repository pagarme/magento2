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
    });

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

        console.log(JSON.stringify(dataSerialize));

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
        $("#document-type").val(recipient.documentType);
        $("#type").val(recipient.documentType);
        $("#holder-name").val(recipient.holderName);
        $("#holder-document-type").val(recipient.documentType);
        $("#holder-document").val(recipient.holderDocument);
        $("#bank").val(recipient.bank);
        $("#branch-number").val(recipient.branchNumber);
        $("#branch-check-digit").val(recipient.branchCheckDigit);
        $("#account-number").val(recipient.accountNumber);
        $("#account-check-digit").val(recipient.accountCheckDigit);
        $("#account-type").val(recipient.accountType);
        $("#document").val(recipient.document);
        $("#transfer-enabled").val(recipient.transferEnabled ? 1 : 0);
        $("#transfer-interval").val(recipient.transferInterval);
        fillTransferDayValuesByTransferInterval();
        $("#transfer-day").val(recipient.transferDay);

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

        fillTypeValueByDocumentType();
    }

});
