require([
    'jquery',
    'jquery/ui',
    'mage/translate'
], function ($) {
    'use strict';

    let productBundleMaxIndex = -1;
    const saveButtonElement = "#save-button";
    const recurrenceTypeElement = "#recurrence-type";
    $(document).ready(function(){
        $("#allow_installments_div").hide();
        const editProduct = $("#edit-product").val();
        if (editProduct.length > 0) {
            $(".select-product").hide();
            loadProduct(JSON.parse(editProduct));
        }

        const products = Object.values(
            JSON.parse($("#products-bundle").val())
        );

        $("#product-search").autocomplete({
            minLength:3,
            source: products,
            select: function( event, ui ) {
                $("#product_id").val(ui.item.id);
            },
            search: function(event, oUi) {
                const currentValue = $(event.target).val().toLowerCase();
                const arraySearch = [];
                for (const index in products) {

                    const productName = products[index].value.toLowerCase();

                    if (productName.indexOf(currentValue) >= 0) {
                        arraySearch.push(products[index]);
                    }
                }

                $(this).autocomplete('option', 'source', arraySearch);

                if (arraySearch.length === 0) {
                    const message = $.mage.__('No product founded with name: %1').replace('%1', currentValue);
                    showErrorMessage(message);
                }
            }
        });

        $("#add-product").on('click', function() {
            if ($("#product_id").val() === "") {
                return;
            }
            updateTableProduct($(this));
        });

        $("#form-product").submit(formSubmit);
        $("#credit-card").on('change', toogleInstallments);
        $('.recurrence_price').on('keyup', formatPriceValue);

    });

    function formatPriceValue(e) {
        let value = $(this).val();
        value = value.replace(/\D]/g, '');
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

        const errors = validateForm(e);
        if (errors.length > 0) {
            alert(errors.join("\r\n"));
            return;
        }
        toggleSaveButton();

        const dataSerialize = jQuery(this).serialize();
        const url =  $("#url-post").val();

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
                toggleSaveButton()
            }
        });
    }

    function toggleSaveButton()
    {
        const disabled = $(saveButtonElement).prop('disabled');
        if (disabled) {
            $(saveButtonElement).attr('disabled', false);
            $(`${saveButtonElement} span`).html("Save");
            return;
        }
        $(saveButtonElement).attr('disabled', true);
        $(`${saveButtonElement} span`).html("Saving");
    }

    function validateForm(e) {
        const errors = [];

        const type = $(recurrenceTypeElement).val();

        const productId = $("#product_id").val();
        if (productId.length <= 0) {
            errors.push($.mage.__("Bundle product not selected"));
        }

        const paymentMethod = [
            $("#boleto").prop("checked"),
            $("#credit-card").prop("checked")
        ];

        const selectedPayments = paymentMethod.filter(function (item) {
           return item !== false;
        });

        if (selectedPayments.length <= 0) {
            errors.push($.mage.__("Select at last one payment method"));
        }

        if (type === 'subscription') {
            const cycles = [
                $("#interval_count_1").val(),
                $("#interval_count_2").val(),
                $("#interval_count_3").val(),
                $("#interval_count_4").val()
            ];

            const selectedCycles = cycles.filter(function (item) {
                return item !== "";
            });

            if (selectedCycles.length <= 0) {
                errors.push($.mage.__("Fill at last one cycle option"));
            }
        }

        return errors;
    }

    function updateTableProduct(element) {
        const data = {
            productId: $("#product_id").val(),
            recurrenceType: $(recurrenceTypeElement).val(),
            recurrenceProductId: $("#product-recurrence-id").val()
        }

        if (data.productId.length === 0) {
            return;
        }

        element.attr('disabled', true);
        if (element.data('action') === 'add') {
            const url = $("#url-search").val();
            $.getJSON(url, data, showData);
            return;
        }

        $("#table-products").hide();
        $("#table-products tbody").empty();
        $("#product-search").val("");
        changeButton();
    }

    function showData(data) {
        if (!data || data.length == 0) {
            const msg = $.mage.__('It was not possible to find the subproducts for this bundle. ' +
                'Check your configuration and try again');
            changeButton();
            showErrorMessage(msg);
            return;
        }
        $("#table-products").show();
        for (const index in data) {
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

        if (index > productBundleMaxIndex) {
            productBundleMaxIndex = index;
        }

        const id = data.id === undefined ? "" : data.id;
        const cycles = data.cycles === undefined ? "" : data.cycles;
        const quantity = data.quantity === undefined ? 1 : data.quantity;
        const pagarmeId = data.pagarme_id === undefined ? "" : data.pagarme_id;
        const inputFormItem = `<input type='hidden' name='form[items][${index}`;
        const closeTag = "'/>";

        const inputsHidden = `${inputFormItem}][product_id]' value='${data.code}${closeTag}
            ${inputFormItem}][name]' value='${data.name}${closeTag}
            ${inputFormItem}][price]' value='${data.price}${closeTag}
            ${inputFormItem}][quantity]' value='${quantity}${closeTag}
            ${inputFormItem}][pagarme_id]' value='${pagarmeId}${closeTag}
            ${inputFormItem}][id]' value='${id}${closeTag}`;

        const quantityColumn = `<input type='number' disabled name='form[items][${index}][quantity]' value='${quantity}'/>`;
        const priceColumn = `<input type='number' disabled value='${(data.price / 100).toFixed(2)}' />
            <input type='hidden' name='form[items][${index}][quantity]' value='${quantity}'/>`;

        const type = $(recurrenceTypeElement).val();

        let lastColumn = quantityColumn;
        if (type === 'subscription') {
            lastColumn = priceColumn;
        }
        const tr = $('<tr>').append(
            $('<td>').html(`<img src='${data.image}' width='70px' height='70px'>`),
            $('<td>').text(data.name),
            $('<td>').html(lastColumn + inputsHidden),
        );

        const cycleColumn = `<input type='number' name='form[items][${index}][cycles]' value='${cycles}' step='1' min='0'/>`;
        if (type !== 'subscription') {
            tr.append($('<td>').html(cycleColumn))
        }

        const table = $('#table-products tbody');
        table.append(tr);
    }

    function changeButton() {
        const button = $("#add-product");
        button.attr('disabled', false);

        if (button.data('action') === 'add') {
            $('#product-search').attr('disabled', true);
            button.data('action', 'remove');
            button.find('span').html("Remove Product");
            return;
        }
        $('#product-search').attr('disabled', false);
        button.data('action', 'add');
        button.find('span').html("Add Product");
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
        $("#apply-discount-in-all-product-cycles").prop('checked', product.applyDiscountInAllProductCycles)


        if (product.creditCard) {
            $("#allow_installments").prop('checked', product.allowInstallments);
            $("#allow_installments_div").show();
        }
        $("#sell_as_normal_product").prop('checked', product.sellAsNormalProduct);

        updateTableProduct($("#add-product"));
        fillRepetitionTable(product.repetitions);
    }

    function fillRepetitionTable(reptitions) {
        if (reptitions === undefined) {
            return;
        }

        for (const index in reptitions) {
            const count = parseInt(index) + 1;
            let recurrencePrice = reptitions[index].recurrencePrice;
            recurrencePrice = (recurrencePrice / 100).toFixed(2);
            recurrencePrice = recurrencePrice.toString().replace('.',',');

            $(`#interval_count_${count}`).val(reptitions[index].intervalCount);
            $(`#interval_${count}`).val(reptitions[index].interval);
            $(`#recurrence_price_${count}`).val(recurrencePrice);
            $(`#cycles_${count}`).val(reptitions[index].cycles || 0);
            $(`#repetition_id_${count}`).val(reptitions[index].id);
        }
    }

    function showErrorMessage(message) {
        $('#error-message').html(message).show();

        setTimeout(function(){ $('#error-message').fadeOut() }, 3000);
    }
});
