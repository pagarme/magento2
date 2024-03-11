require([
    'jquery',
    'jquery/ui',
    'pagarmeJqueryMask'
], function ($) {
    'use strict';

    const
        webkulSeller = $('#webkul-seller'),
        transferEnabled = $('#transfer-enabled'),
        cpfMax = 14, // Includes punctuation due to the mask
        cpfMask = '000.000.000-00',
        cnpjMax = 18, // Includes punctuation due to the mask
        cnpjMask = '00.000.000/0000-00';

    $(document).ready(function () {
        const phoneMaskOptions = {
            onKeyPress: function (val, e, field, options) {
                field.mask(phoneMaskBehavior.apply({}, arguments), options);
            }
        };

        localeDatePicker();

        $('#pagarme-recipients-form').on('submit', formSubmit);

        $('[data-datepicker]').datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            maxDate: 0,
            minDate: '-122y', // Jeanne Calment
            showMonthAfterYear: true,
            yearRange: 'c-122:c0'
        });

        $('[data-phone-mask]').mask(phoneMaskBehavior, phoneMaskOptions);
        $('[data-document-mask]').mask(cpfMask);
        $('[data-date-mask]').mask('00/00/0000');
        $('[data-currency-mask]').mask("#.##0,00", {reverse: true});
        $('[data-zipcode-mask]').mask('00000-000');
        $('#cnae').mask('0000-0/00');

        function phoneMaskBehavior(val) {
            return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
        }

        $('#existing_recipient').on('change', function () {
            hideOrShowSectionByFieldVal('pagarme_id', $(this).val());
        });

        webkulSeller.on('change', function () {
            const externalId = webkulSeller.val();

            $('#webkul-id')
                .val(externalId)
                .prop('readonly', !!externalId);

            const recipientName = webkulSeller
                .find(':selected')
                .attr('data-sellername');
            $('#recipient-name')
                .val(recipientName)
                .prop('readonly', !!recipientName)
                .trigger('change');

            const recipientEmail = webkulSeller
                .find(':selected')
                .attr('data-email');
            $('#recipient-email')
                .val(recipientEmail)
                .prop('readonly', !!recipientEmail);

            const recipientDocument = webkulSeller
                .find(':selected')
                .attr('data-document');
            $('#document, #holder-document').val(recipientDocument).trigger('change');
            $('#document-type, #holder-document-type').val(getDocumentTypeByDocument(recipientDocument));
            $('#document').prop('readonly', !!recipientDocument);
            $('#document-type').toggleClass('readonly', !!recipientDocument).trigger('change');

            if (!externalId) {
                hideOrShowSections(''); // Hide all sections
                return;
            }

            hideOrShowSections('seller', 'show');
        });

        $('#document-type').on('change', function () {
            const documentType = $(this).val(),
                config = {
                    'mask': documentType === 'corporation' ? cnpjMask : cpfMask,
                    'maxLength': documentType === 'corporation' ? cnpjMax : cpfMax
                };

            $('#holder-document-type')
                .val(documentType);

            $('#document, #holder-document')
                .attr({
                    'placeholder': config.mask,
                    'maxLength': config.maxLength
                })
                .mask(config.mask);

            if (documentType === 'corporation') {
                $('.admin__fieldset-wrapper-content').addClass('corporation-wrapper');
            } else {
                $('.admin__fieldset-wrapper-content').removeClass('corporation-wrapper');
            }

            hideOrShowSections(['individual', 'corporation']);
            if (documentType !== '') {
                hideOrShowSections(documentType, 'show');
            }
        });

        $('#document')
            .on('keyup change', function () {
                const documentNumber = $(this).val();
                $('#holder-document')
                    .val(documentNumber).trigger('input');
            })
            .on('change', function () {
                let documentNumber = $(this).val();
                if (documentNumber.length === cnpjMax) {
                    documentNumber = documentNumber.replace(/\D/g, '');
                    const maxRequests = 3;
                    let requests = 0;
                    $.ajax({
                        url: 'https://brasilapi.com.br/api/cnpj/v1/' + documentNumber,
                        method: 'get',
                        showLoader: true,
                        beforeSend: function (xhr) {
                            // Empty to remove Magento's default handler
                        },
                        statusCode: {
                            404: function (response) {
                                if (response.responseJSON.type === 'service_error') {
                                    requests++;
                                    if (requests < maxRequests) {
                                        setTimeout($('#document').trigger('change'), 50);
                                    }
                                }
                            }
                        }
                    }).done(function (data) {
                        let cnaeList = '';
                        if (data['cnae_fiscal']) {
                            cnaeList = '<option value=' + formatCnae(data['cnae_fiscal']) + '>'
                                + data['cnae_fiscal_descricao'] + '</option>';
                        }
                        if (data['cnaes_secundarios']) {
                            $.each(data['cnaes_secundarios'], function (index) {
                                if (data['cnaes_secundarios'][index]['codigo']) {
                                    cnaeList += '<option value=' + formatCnae(data['cnaes_secundarios'][index]['codigo']) + '>'
                                        + data['cnaes_secundarios'][index]['descricao']
                                        + '</option>';
                                }
                            });
                        }
                        fillDatalistOptions('cnae-list', cnaeList);

                        if (data['qsa']) {
                            let partnersList = '';
                            $.each(data['qsa'], function (index) {
                                partnersList += '<option value="' + capitalizeAllWords(data['qsa'][index]['nome_socio']) + '">' + data['qsa'][index]['qualificacao_socio'] + '</option>';
                            });
                            fillDatalistOptions('partners-list', partnersList);
                        }

                        const foundingDateArray = data['data_inicio_atividade'].split('-'),
                            foundingDate = foundingDateArray[2] + '/' + foundingDateArray[1] + '/' + foundingDateArray[0],
                            phoneNumber = [
                                data['ddd_telefone_1'],
                                data['ddd_telefone_2']
                            ];

                        $('#recipient-company-name').val(capitalizeAllWords(data['razao_social'])).trigger('change');
                        $('#recipient-trading-name').val(capitalizeAllWords(data['nome_fantasia']));
                        $('#founding-date').val(foundingDate);
                        $('#recipient-corporation-type').val(data['natureza_juridica']);
                        $('#cnae').val(data['cnae_fiscal']).trigger('input');
                        $('#recipient-phones-type-0').val(phoneNumber[0].length === 10 ? 'Telefone' : 'Celular');
                        $('#recipient-phones-number-0').val(phoneNumber[0]).trigger('input');
                        $('#recipient-phones-type-1').val(phoneNumber[1].length === 10 ? 'Telefone' : 'Celular');
                        $('#recipient-phones-number-1').val(phoneNumber[1]).trigger('input');
                        $('#company-zip-code').val(data['cep']).trigger('change').trigger('input');
                        $('#company-street-number').val(data['numero']);
                        $('#company-complementary').val(capitalizeAllWords(data['complemento']));
                        if (data['qsa'].length) {
                            $('#recipient-partner-name').val(capitalizeAllWords(data['qsa'][0]['nome_socio']));
                            $('#recipient-partner-professional-occupation').val(data['qsa'][0]['qualificacao_socio'])
                        }
                    }).fail(function (data) {
                        console.log(data)
                    });
                }
            });

        $('#recipient-name, #recipient-company-name').on('change', function () {
            $('#holder-name').val($(this).val());
        });

        $('[data-cep-search]').on('change', function () {
            const cepNumber = $(this).val().replace(/\D/g, ''),
                addressFieldset = $(this).closest('.admin__fieldset');

            if (cepNumber.length !== 8) {
                return;
            }

            const cepField = $(this),
                maxRequests = 3;
            let requests = 0;

            $.ajax({
                url: 'https://viacep.com.br/ws/' + cepNumber + '/json/',
                method: 'get',
                showLoader: true,
                beforeSend: function (xhr) {
                    // Empty to remove Magento's default handler
                }
            }).done(function (data) {
                addressFieldset.find('input[id$="-street"]').val(data.logradouro);
                addressFieldset.find('input[id$="-neighborhood"]').val(data.bairro);
                addressFieldset.find('select[id$="-state"]').val(data.uf).trigger('change');
                addressFieldset.find('input[id$="-city"]').val(data.localidade);
            }).fail(function (data) {
                console.log(data)
            });
        });

        $('[data-state]').on('change', function () {
            const stateCode = $(this).val();

            if (!stateCode) {
                return;
            }

            const addressFieldset = $(this).closest('.admin__fieldset'),
                citiesListId = addressFieldset.find('datalist[id$="-cities"]').attr('id');
            let citiesList = '';

            $.get('https://servicodados.ibge.gov.br/api/v1/localidades/estados/' + stateCode + '/distritos')
                .done(function (data) {
                    $.each(data, function (index) {
                        citiesList += '<option value="' + data[index].nome + '">';
                    });
                    fillDatalistOptions(citiesListId, citiesList);
                });
        });

        transferEnabled.on('change', function () {
            hideOrShowSectionByFieldVal('transfer_interval', $(this).val());
        });

        $('#transfer-interval').on('change', function () {
            hideOrShowSections(
                'transfer_day',
                $(this).val() === 'Daily' ? 'hide' : 'show'
            );
            fillTransferDayValuesByTransferInterval();
        });

        const editRecipient = $('#edit-recipient').val();
        if (editRecipient.length > 0) {
            $('#webkul-seller-container').hide();
            loadRecipient(JSON.parse(editRecipient), false);
        }

        $('#search-recipient-id').on('click', searchRecipient);
    });

    function localeDatePicker() {
        if ($('html').attr('lang') !== 'pt') {
            return;
        }

        const shortWeekDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        $.datepicker.setDefaults({
            closeText: 'Fechar',
            prevText: 'Anterior',
            nextText: 'Próximo',
            currentText: 'Hoje',
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            dayNames: [
                'Domingo',
                'Segunda-feira',
                'Terça-feira',
                'Quarta-feira',
                'Quinta-feira',
                'Sexta-feira',
                'Sábado'
            ],
            dayNamesShort: shortWeekDays,
            dayNamesMin: shortWeekDays,
            weekHeader: 'Sm'
        });
    }

    function getDocumentTypeByDocument(document) {
        let value = '';
        if (document) {
            value = 'individual';
            document = document.toString().replace(/\D/g, '');
            if (document.length > 11) {
                value = 'corporation';
            }
        }
        return value;
    }

    function fillDatalistOptions(datalistId, options) {
        const list = $('#' + datalistId);
        list.html('');
        if (options !== '') {
            list.html(options);
        }
    }

    function capitalizeAllWords(sentence) {
        if (sentence === '') {
            return '';
        }

        let array = sentence.split(' ');
        for (let i = 0; i < array.length; i++) {
            array[i] = array[i][0].toUpperCase() + array[i].substr(1).toLowerCase();
        }

        return array.join(' ');
    }

    function formatCnae(cnae) {
        return cnae.toString().replace(/(\d+)(\d)(\d{2})/g, '$1-$2/$3');
    }

    function searchRecipient(e) {
        e.preventDefault();

        const recipientId = $('#recipient-id').val(),
            url = $('#url-search-recipient-id').val();

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
                if (response.code !== 200) {
                    alert(response.message);
                    return;
                }

                loadRecipient(response.recipient, true);
            });

    }

    function formSubmit(e) {
        e.preventDefault();

        if (!validateEmail($("#recipient-email").val())) {
            alert('Invalid email');
            return;
        }

        toggleSaveButton();

        jQuery.ajax({
            method: "POST",
            url: $('#url-post').val(),
            contentType: 'application/json',
            data: JSON.stringify(jQuery(this).serialize()),
            success: function (data) {
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

    function toggleSaveButton() {
        const saveButton = $('#save-button'),
            saveButtonSpan = $('#save-button span');
        if (saveButton.prop('disabled')) {
            saveButton.attr('disabled', false);
            saveButtonSpan.html('Save');
            return;
        }
        saveButton.attr('disabled', true);
        saveButtonSpan.html('Saving');
    }

    // TODO: Remove after correcting the loadRecipient() function
    function hideElementByMenuSelectValue(value, elementIdToHide) {
        document.getElementById(elementIdToHide).style.display
            = value == 1 ? 'block' : 'none';
    }

    function hideOrShowSectionByFieldVal(section, value) {
        hideOrShowSections(section, value === '1' ? 'show' : 'hide');
    }

    function hideOrShowSections(sections, action = 'hide') {
        let fields;
        if (Array.isArray(sections)) {
            let dataSections = '';
            $.each(sections, function (key, val) {
                dataSections += '[data-section*="' + val + '"]';
                dataSections += (key < sections.length - 1) ? ', ' : '';
            });
            fields = $(dataSections);
        } else {
            fields = sections ? $('[data-section*="' + sections + '"]') : $('[data-section]');
        }

        if (action === 'hide') {
            fields.each(function () {
                $(this).slideUp('250');
                $(this).find('[data-toggle-required]').prop('required', false);
            });
        }

        if (action === 'show') {
            fields.each(function () {
                $(this).slideDown('250');
                $(this).find('[data-toggle-required]').prop('required', true);
            });
        }
    }

    function fillTransferDayValuesByTransferInterval() {
        const transferDay = $('#transfer-day'),
            transferDayValue = $("#transfer-interval").val();

        if (transferDayValue === 'Weekly') {
            const transferWeekDays = {
                1: 'Segunda-feira',
                2: 'Terça-feira',
                3: 'Quarta-feira',
                4: 'Quinta-feira',
                5: 'Sexta-feira'
            };
            transferDay.children().remove();
            $.each(transferWeekDays, function (index, value) {
                transferDay.append('<option value="' + index + '">' + value + '</option>');
            });
        }

        if (transferDayValue === 'Monthly') {
            transferDay.children().remove();
            for (let i = 1; i < 32; i++) {
                transferDay.append('<option value="' + i + '">' + i + '</option>');
            }
        }

        if (transferDayValue === 'Daily') {
            transferDay.children().remove().end().append('<option value="0">0</option>');
        }
    }

    function validateEmail(email) {
        const validationExpression = /\S+@\S+\.\S+/;
        return validationExpression.test(email);
    }

    function buildRecipientObject(recipient) {
        const nameId = recipient.type === 'individual' ? '#recipient-name' : '#recipient-company-name';

        let recipientObject = {
            '#document-type': recipient.type === 'company' ? 'corporation' : recipient.type,
            '#document': recipient.document,
            '#recipient-email': recipient.email,

            '#holder-name': recipient.default_bank_account.holder_name,
            '#holder-document-type': recipient.default_bank_account.holder_type === 'company' ? 'corporation' : recipient.default_bank_account.holder_type,
            '#holder-document': recipient.document,
            '#bank': recipient.default_bank_account.bank,
            '#branch-number': recipient.default_bank_account.branch_number,
            '#branch-check-digit': recipient.default_bank_account.branch_check_digit,
            '#account-number': recipient.default_bank_account.account_number,
            '#account-check-digit': recipient.default_bank_account.account_check_digit,
            '#account-type': recipient.default_bank_account.type,

            '#transfer-enabled': recipient.transfer_settings.transfer_enabled ? 1 : 0,
            '#transfer-interval': recipient.transfer_settings.transfer_interval,
            '#transfer-day': recipient.transfer_settings.transfer_day
        };

        recipientObject[nameId] = recipient.name;

        return recipientObject;
    }

    function loadRecipient(recipient, wasSearched) {
        const recipientObject = buildRecipientObject(recipient);

        for (const elementId in recipientObject) {
            if (!Object.hasOwnProperty.call(recipientObject, elementId))
                continue;

            const recipientValue = recipientObject[elementId];
            const element = $(elementId);
            element.val(recipientValue)
                .trigger('change')
                .trigger('input');
            if (
                wasSearched
                && recipientValue !== ''
            ) {
                element.attr('readonly', true);
                if (element.is('select')) {
                    element.addClass('readonly');
                }
            }
        }

        fillTransferDayValuesByTransferInterval();

        hideElementByMenuSelectValue(
            transferEnabled.val(),
            "transfer-day-div"
        );

        hideElementByMenuSelectValue(
            transferEnabled.val(),
            "transfer-interval-div"
        );

        $('#recipient-id')
            .val(recipient.id)
            .attr('disabled', false);

        $("#document").attr("readonly", true);
        $("#document-type").attr("readonly", true);

        if (wasSearched) return;

        $('#webkul-id')
            .val(recipient.externalId)
            .attr("readonly", true);
        $("#webkul-id-div").show();

        $('#existing_recipient')
            .val('1')
            .attr("readonly", true);

        $("#use_existing_pagarme_id").hide();

        $('#pagarme_id').show();

        $("#recipient-name")
            .val(recipient.name)
            .attr("readonly", true);

        $('#recipient-email')
            .val(recipient.email)
            .attr("readonly", true);

        $('#document')
            .val(recipient.document)
            .attr("readonly", true);

        $('#holder-document')
            .val(recipient.document)
            .attr("readonly", true);

        $('#holder-document-type')
            .val(recipient.default_bank_account.holder_type)
            .attr("readonly", true)
            .addClass('readonly');
    }

});
