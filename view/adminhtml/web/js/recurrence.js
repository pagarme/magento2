require([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $(document).ready(function(){
        $("#allow_installments_div").hide();
        var editProduct = $("#edit-product").val();
        if (editProduct.length > 0) {
            $(".select-product").hide();
            loadProduct(JSON.parse(editProduct));
        }

        var products = Object.values(
            JSON.parse($("#products-bundle").val())
        );

        $("#product-search").autocomplete({
            minLength:3,
            source: products,
            select: function( event, ui ) {
                $("#product_id").val(ui.item.id);
            },
            search: function(event, oUi) {
                var currentValue = $(event.target).val().toLowerCase();
                var arraySearch = [];
                for (var index in products) {

                    var productName = products[index].value.toLowerCase();

                    if (productName.indexOf(currentValue) >= 0) {
                        arraySearch.push(products[index]);
                    }
                }

                $(this).autocomplete('option', 'source', arraySearch);

                if (arraySearch.length == 0) {
                    var message = 'Nenhum produto encontrado com nome: ' + currentValue;
                    showErrorMessage(message);
                }
            }
        });

        $("#add-product").on('click', function() {
            if ($("#product_id").val() == "") {
                return;
            }
            updateTableProduct($(this));
        });

        $("#form-product").submit(formSubmit);
        $("#credit-card").on('change', toogleInstallments);
        $('.recurrence_price').on('keyup', formatPriceValue);

    });

    function formatPriceValue(e) {
        var value = $(this).val();
        value = value.replace(/[^0-9]/g, '');
        value = (value / 100).toFixed(2);
        $(this).val(value.toString().replace('.',","));
    }

    function toogleInstallments(e) {
        if ($(this).prop('checked')) {
            return $("#allow_installments_div").show();
        }
        return $("#allow_installments_div").hide();
    }

    function formSubmit(e) {
        e.preventDefault();

        var errors = validateForm(e);
        if (errors.length > 0) {
            alert(errors.join("\r\n"));
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

    function validateForm(e) {
        var errors = [];

        var type = $("#recurrence-type").val();

        var productId = $("#product_id").val();
        if (productId.length <= 0) {
            errors.push("Bundle product not selected");
        }

        var paymentMethod = [
            $("#boleto").prop("checked"),
            $("#credit-card").prop("checked")
        ];

        var paymentsSelecteds = paymentMethod.filter(function (item) {
           return item !== false;
        });

        if (paymentsSelecteds.length <= 0) {
            errors.push("Select at last one payment method");
        }

        if (type == 'subscription') {
            var cycles = [
                $("#interval_count_1").val(),
                $("#interval_count_2").val(),
                $("#interval_count_3").val(),
                $("#interval_count_4").val()
            ];

            var cyclesSelecteds = cycles.filter(function (item) {
                return item !== "";
            });

            if (cyclesSelecteds.length <= 0) {
                errors.push("Fill at last one cycle option");
            }
        }

        return errors;
    }

    function updateTableProduct(element) {
        var data = {
            productId: $("#product_id").val(),
            recurrenceType: $("#recurrence-type").val(),
            recurrenceProductId: $("#product-recurrence-id").val()
        }

        if (data.productId.length == 0) {
            return;
        }

        element.attr('disabled', true);
        if (element.data('action') == 'add') {
            var url = $("#url-search").val();
            $.getJSON(url, data, showData);
            return;
        }

        $("#table-products").hide();
        $("#table-products tbody").empty();
        $("#product-search").val("");
        changeButton();
        return;
    }

    function showData(data) {
        if (!data || data.length == 0) {
            var msg =
                'Não foi possível encontrar os subprodutos deste bundle. ' +
                'Verifique sua configuração e tente novamente';
            changeButton();
            showErrorMessage(msg);
            return;
        }
        $("#table-products").show();
        for (var index in data) {
            addRow(data[index], index);
        }
        fillProductBundle(data['productBundle']);

        changeButton();
    }

    function fillProductBundle(item) {
        $("#product_id").val(item.id);
        $("#product_name").val(item.name);
        $("#product_description").val(item.description);
        $("#info-bundle span").html(item.name);
    }

    function addRow(data, index) {
        if (data.image == undefined) {
            return;
        }

        var id = data.id == undefined ? "" : data.id;
        var cycles = data.cycles == undefined ? "" : data.cycles;
        var quantity = data.quantity == undefined ? 1 : data.quantity;
        var pagarme_id = data.pagarme_id == undefined ? "" : data.pagarme_id;

        var inputsHidden = "<input type='hidden' name='form[items][" + index + "][product_id]' value='" + data.code + "'/>" +
            "<input type='hidden' name='form[items][" + index + "][name]' value='" + data.name + "'/>" +
            "<input type='hidden' name='form[items][" + index + "][price]' value='" + data.price + "'/>" +
            "<input type='hidden' name='form[items][" + index + "][quantity]' value='" + quantity + "'/>" +
            "<input type='hidden' name='form[items][" + index + "][pagarme_id]' value='" + pagarme_id + "'/>" +
            "<input type='hidden' name='form[items][" + index + "][id]' value='" + id + "'/>";

        var quantityColumn = "<input type='number' disabled name='form[items][" + index + "][quantity]' value='" + quantity + "'/>";
        var priceColumn = "<input type='number' disabled value='" + (data.price / 100).toFixed(2) + "' />" +
            "<input type='hidden' name='form[items][" + index + "][quantity]' value='" + quantity + "'/>";

        var type = $("#recurrence-type").val();

        var lastColumn = quantityColumn;
        if (type == 'subscription') {
            lastColumn = priceColumn;
        }
        var tr = $('<tr>').append(
            $('<td>').html("<img src='" + data.image + "' width='70px' height='70px'>"),
            $('<td>').text(data.name),
            $('<td>').html(lastColumn + inputsHidden),
        );

        var cycleColumn = "<input type='number' name='form[items][" + index + "][cycles]' value='" + cycles + "' step='1' min='0'/>";
        if (type !== 'subscription') {
            tr.append($('<td>').html(cycleColumn))
        }

        var table = $('#table-products tbody');
        table.append(tr);
    }

    function changeButton() {
        var button = $("#add-product");
        button.attr('disabled', false);

        if (button.data('action') == 'add') {
            $('#product-search').attr('disabled', true);
            button.data('action', 'remove');
            button.find('span').html("Remove Product");
            return;
        }
        $('#product-search').attr('disabled', false);
        button.data('action', 'add');
        button.find('span').html("Add Product");
        return;
    }

    function loadProduct(product) {
        $("#credit-card").prop('checked', product.creditCard);
        $("#boleto").prop('checked', product.boleto);
        $("#interval_type").val(product.intervalType);
        $("#interval_count").val(product.intervalCount);
        $("#product_id").val(product.productId);
        $("#plan-id").val(product.pagarmeId);
        $("#status").val(product.status);
        $("#trial_period_days").val(product.trialPeriodDays);


        if (product.creditCard) {
            $("#allow_installments").prop('checked', product.allowInstallments);
            $("#allow_installments_div").show();
        }
        $("#sell_as_normal_product").prop('checked', product.sellAsNormalProduct);

        updateTableProduct($("#add-product"));
        fillRepetitionTable(product.repetitions);
    }

    function fillRepetitionTable(reptitions) {
        if (reptitions == undefined) {
            return;
        }

        for (var index in reptitions) {
            var count = parseInt(index) + 1;
            var recurrencePrice = reptitions[index].recurrencePrice;
            recurrencePrice = (recurrencePrice / 100).toFixed(2);
            recurrencePrice = recurrencePrice.toString().replace('.',',');

            $("#interval_count_" + count).val(reptitions[index].intervalCount);
            $("#interval_" + count).val(reptitions[index].interval);
            $("#recurrence_price_" + count).val(recurrencePrice);
            $("#cycles_" + count).val(reptitions[index].cycles || 0);
            $("#repetition_id_" + count).val(reptitions[index].id);
        }
    }

    function showErrorMessage(message) {
        var message = message;
        $('#error-message').html(message).show();

        setTimeout(function(){ $('#error-message').fadeOut() }, 3000);
    }
});
