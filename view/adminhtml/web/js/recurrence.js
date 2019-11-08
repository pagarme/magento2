require([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $(document).ready(function(){

        $("#allow_installments_div").hide();
        var editProduct = $("#edit-product").val();
        if (editProduct.length > 0) {
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
                    showErrorMessage('Nenhum produto do tipo bundle encontrado');
                }
            }
        });

        $("#add-product").on('click', function() {
            updateTableProduct($(this));
        });

        $("#form-product").submit(formSubmit);
        $("#credit-card").on('change', toogleInstallments);

    });

    function toogleInstallments(e) {
        if ($(this).prop('checked')) {
            return $("#allow_installments_div").show();
        }
        return $("#allow_installments_div").hide();
    }

    function formSubmit(e) {
        e.preventDefault();
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
            }
        });
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

        var tr = $('<tr>').append(
            $('<td>').html("<img src='" + data.image + "' width='70px' height='70px'>"),
            $('<td>').text(data.name),
            $('<td>').html("<input type='number' name='form[items][" + index +"][cycles]' value='" + data.cycles + "' step='1' min='0'/>"),
            $('<td>').html(
                "<input type='number' name='form[items][" + index +"][quantity]' value='" + data.quantity + "' step='1' min='1'/>" +
                "<input type='hidden' name='form[items][" + index +"][product_id]' value='" + data.code + "'/>" +
                "<input type='hidden' name='form[items][" + index +"][name]' value='" + data.name + "'/>" +
                "<input type='hidden' name='form[items][" + index +"][price]' value='" + data.price + "'/>" +
                "<input type='hidden' name='form[items][" + index +"][id]' value='" + data.id + "'/>"
            ),
        );

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
        $("#interval").val(product.interval);
        $("#interval_count").val(product.interval_count);
        $("#product_id").val(product.productId);

        if (product.creditCard) {
            $("#allow_installments").prop('checked', product.allowInstallments);
            $("#allow_installments_div").show();
        }

        updateTableProduct($("#add-product"));
        fillRepetitionTable(product.repetitions);
    }

    function fillRepetitionTable(reptitions) {
        if (reptitions == undefined) {
            return;
        }

        for (var index in reptitions) {
            var count = parseInt(index) + 1;
            $("#interval_count_" + count).val(reptitions[index].intervalCount);
            $("#interval_" + count).val(reptitions[index].intervalType);
            $("#discount_value_" + count).val(reptitions[index].discountValue);
            $("#discount_type_" + count).val(reptitions[index].discountType);
            $("#repetition_id_" + count).val(reptitions[index].id);
        }
    }

    function showErrorMessage(message) {
        var message = message;
        $('#error-message').html(message).show();

        setTimeout(function(){ $('#error-message').fadeOut() }, 3000);
    }
});