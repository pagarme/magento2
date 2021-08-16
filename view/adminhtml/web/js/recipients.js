require([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $(document).ready(function(){
        $("#form-recipients").submit(formSubmit);

        hiddenElementByMenuSelectValue(
            $("#existing_recipient").val(),
            "pagarme_id"
        );

        $("#existing_recipient").on('change', function () {
            hiddenElementByMenuSelectValue(
                $(this).val(),
                "pagarme_id"
            )
        });

        hiddenElementByMenuSelectValue(
            $("#transfer-enabled").val(),
            "transfer-interval-div"
        );

        $("#transfer-enabled").on('change', function () {
            hiddenElementByMenuSelectValue(
                $(this).val(),
                "transfer-interval-div"
            )
        });

        hiddenElementByMenuSelectValue(
            $("#transfer-enabled").val(),
            "transfer-day-div"
        );

        $("#transfer-enabled").on('change', function () {
            hiddenElementByMenuSelectValue(
                $(this).val(),
                "transfer-day-div"
            )
        });

        fillFieldWithSameValue();

        $("#document-type").on('change', function() {
            fillTypeValueByDocumentType();
        });

    });

    function formSubmit(e) {
        e.preventDefault();

        // var errors = validateForm(e);
        // if (errors.length > 0) {
        //     alert(errors.join("\r\n"));
        //     return;
        // }
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

    function hiddenElementByMenuSelectValue(value, elementIdToHide)
    {
        document.getElementById(elementIdToHide).style.display
            = value == 1 ? 'block' : 'none';
    }

    function fillFieldWithSameValue()
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
        console.log(documentTypeValue);

        document.getElementById("type").value
            = documentTypeValue == 'cpf' ? 'individual' : 'company';
    }

});
