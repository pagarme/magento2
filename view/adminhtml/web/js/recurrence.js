require([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $(document).ready(function(){

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
                $("#product_image").val(ui.item.image);
                $("#info-bundle span").html(ui.item.value);
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
            }
        });

        $("#add-product").on('click', function() {
            updateTableProduct($(this));
        });
    });

    function updateTableProduct(element) {
        var data = {
            productId: $("#product_id").val()
        }

        if (data.productId.length == 0) {
            return;
        }

        element.attr('disabled', true);

        if (element.data('action') == 'add') {
            var url = $("#url-search").val();
            $.getJSON(url, data, success);
            return;
        }

        $("#table-products").hide();
        $("#table-products tbody").empty();
        $("#product-search").val("");
        changeButton();
        return;
    }

    function success(data) {
        $("#table-products").show();
        for (var index in data) {
            addRow(data[index], index);
        }
        changeButton();
    }

    function addRow(data, index) {
        var tr = $('<tr>').append(
            $('<td>').html("<img src='" + data.image + "' width='70px' height='70px'>"),
            $('<td>').text(data.name),
            $('<td>').html("<input type='number' name='plan[itens][" + index +"][cicle]' step='1' min='0'/>"),
            $('<td>').html("<input type='number' name='plan[itens][" + index +"][quantity]' step='1' min='1'/>"),
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
        $("#enable").prop('checked', product.enabled);
        $("#credit-card").prop('checked', product.enabled); // @todo Get correct value
        $("#boleto").prop('checked', product.enabled); // @todo Get correct value
        $("#interval").val(product.interval);
        $("#interval_count").val(product.interval_count);
        $("#product_id").val(product.product_id);
        $("#info-bundle span").html(product.name);
        updateTableProduct($("#add-product"));
    }
});