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

        $("#holder-document").prop("readonly", true);
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

});
