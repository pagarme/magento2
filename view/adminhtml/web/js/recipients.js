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
        $('#recipient-cnae').mask('0000-0/00');

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
                            fillDatalistOptions('recipient-partner-0-partners-list', partnersList);
                        }

                        const phoneNumber = [
                            data['ddd_telefone_1'],
                            data['ddd_telefone_2']
                        ];

                        $('#recipient-company-name').val(capitalizeAllWords(data['razao_social'])).trigger('change');
                        $('#recipient-trading-name').val(capitalizeAllWords(data['nome_fantasia']));
                        $('#recipient-founding-date').val(formatDate(data['data_inicio_atividade']));
                        $('#recipient-corporation-type').val(data['natureza_juridica']);
                        $('#recipient-cnae').val(data['cnae_fiscal']).trigger('input');
                        $('#recipient-phones-type-0').val(phoneNumber[0].length === 10 ? 'Telefone' : 'Celular');
                        $('#recipient-phones-number-0').val(phoneNumber[0]).trigger('input');
                        $('#recipient-phones-type-1').val(phoneNumber[1].length === 10 ? 'Telefone' : 'Celular');
                        $('#recipient-phones-number-1').val(phoneNumber[1]).trigger('input');
                        $('#company-zip-code').val(data['cep']).trigger('change').trigger('input');
                        $('#company-street-number').val(data['numero']);
                        $('#company-complementary').val(capitalizeAllWords(data['complemento']));
                        if (data['qsa'].length) {
                            $('#recipient-partner-0-name').val(capitalizeAllWords(data['qsa'][0]['nome_socio']));
                            $('#recipient-partner-0-professional-occupation').val(data['qsa'][0]['qualificacao_socio'])
                        }
                    }).fail(function (data) {
                        console.log(data);
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

    function formatDate(date) {
        const dateArray = date.split('-');
        if (dateArray.length === 3) {
            return dateArray[2] + '/' + dateArray[1] + '/' + dateArray[0];
        }
        return date;
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
        let recipientObject = {
            '#document-type': recipient.register_information.type === 'company' ? 'corporation' : recipient.type,
            '#document': recipient.register_information.document,
            '#recipient-email': recipient.register_information.email,
            '#recipient-site': recipient.register_information.site_url,

            '#holder-name': recipient.default_bank_account.holder_name,
            '#holder-document-type': recipient.default_bank_account.holder_type === 'company' ? 'corporation' : recipient.default_bank_account.holder_type,
            '#holder-document': recipient.register_information.document,
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

        $.each(recipient.register_information.phone_numbers, function (index, phone) {
            recipientObject['#recipient-phones-type-' + index] = phone.type;
            recipientObject['#recipient-phones-number-' + index] = phone.ddd + phone.number;
        });

        switch (recipient.type) {
            case 'individual':
                recipientObject['#recipient-name'] = recipient.register_information.name;
                recipientObject['#recipient-mother-name'] = recipient.register_information.mother_name;
                recipientObject['#recipient-birthdate'] = formatDate(recipient.register_information.birthdate);
                recipientObject['#recipient-monthly-income'] = recipient.register_information.monthly_income;
                recipientObject['#recipient-professional-occupation'] = recipient.register_information.professional_occupation;

                recipientObject['#recipient-zip-code'] = recipient.register_information.address.zip_code;
                recipientObject['#recipient-street'] = recipient.register_information.address.street;
                recipientObject['#recipient-street-number'] = recipient.register_information.address.street_number;
                recipientObject['#recipient-complementary'] = recipient.register_information.address.complementary;
                recipientObject['#recipient-reference-point'] = recipient.register_information.address.reference_point;
                recipientObject['#recipient-neighborhood'] = recipient.register_information.address.neighborhood;
                recipientObject['#recipient-state'] = recipient.register_information.address.state;
                recipientObject['#recipient-city'] = recipient.register_information.address.city;
                break;
            case 'company':
            case 'corporation':
                recipientObject['#recipient-company-name'] = recipient.register_information.name;
                recipientObject['#recipient-trading-name'] = recipient.register_information.trading_name;
                recipientObject['#recipient-annual-revenue'] = recipient.register_information.annual_revenue;
                recipientObject['#recipient-corporation-type'] = recipient.register_information.corporation_type;
                recipientObject['#recipient-founding-date'] = formatDate(recipient.register_information.founding_date);

                recipientObject['#company-zip-code'] = recipient.register_information.main_address.zip_code;
                recipientObject['#company-street'] = recipient.register_information.main_address.street;
                recipientObject['#company-street-number'] = recipient.register_information.main_address.street_number;
                recipientObject['#company-complementary'] = recipient.register_information.main_address.complementary;
                recipientObject['#company-reference-point'] = recipient.register_information.main_address.reference_point;
                recipientObject['#company-neighborhood'] = recipient.register_information.main_address.neighborhood;
                recipientObject['#company-state'] = recipient.register_information.main_address.state;
                recipientObject['#company-city'] = recipient.register_information.main_address.city;
                break;
        }

        $.each(recipient.register_information.managing_partners, function (partnerIndex, partner) {
            recipientObject['#recipient-partner-' + partnerIndex + '-name'] = partner.name;
            recipientObject['#recipient-partner-' + partnerIndex + '-document-type'] = partner.type;
            recipientObject['#recipient-partner-' + partnerIndex + '-document'] = partner.document;
            recipientObject['#recipient-partner-' + partnerIndex + '-mother-name'] = partner.mother_name;
            recipientObject['#recipient-partner-' + partnerIndex + '-email'] = partner.email;
            recipientObject['#recipient-partner-' + partnerIndex + '-birthdate'] = formatDate(partner.birthdate);
            recipientObject['#recipient-partner-' + partnerIndex + '-monthly-income'] = partner.monthly_income;
            recipientObject['#recipient-partner-' + partnerIndex + '-declaration'] = partner.self_declared_legal_representative;

            recipientObject['#recipient-partner-' + partnerIndex + '-zip-code'] = partner.address.zip_code;
            recipientObject['#recipient-partner-' + partnerIndex + '-street'] = partner.address.street;
            recipientObject['#recipient-partner-' + partnerIndex + '-street-number'] = partner.address.street_number;
            recipientObject['#recipient-partner-' + partnerIndex + '-complementary'] = partner.address.complementary;
            recipientObject['#recipient-partner-' + partnerIndex + '-reference-point'] = partner.address.reference_point;
            recipientObject['#recipient-partner-' + partnerIndex + '-neighborhood'] = partner.address.neighborhood;
            recipientObject['#recipient-partner-' + partnerIndex + '-state'] = partner.address.state;
            recipientObject['#recipient-partner-' + partnerIndex + '-city'] = partner.address.city;

            $.each(partner.phone_numbers, function (phoneIndex, phone) {
                recipientObject['#recipient-partner-' + partnerIndex + '-phones-type-' + phoneIndex] = phone.type;
                recipientObject['#recipient-partner-' + partnerIndex + '-phones-number-' + phoneIndex] = phone.ddd + phone.number;
            });
        });

        return recipientObject;
    }

    function triggerChange(elementId) {
        const elements = [
            '#document-type',
            '#transfer-enabled'
        ];
        if ($.inArray(elementId, elements) >= 0) {
            $(elementId).trigger('change');
        }
    }

    function loadRecipient(recipient, wasSearched) {
        const recipientObject = buildRecipientObject(recipient);

        for (const elementId in recipientObject) {
            if (!Object.hasOwnProperty.call(recipientObject, elementId))
                continue;

            const recipientValue = recipientObject[elementId];
            const element = $(elementId);
            element.val(recipientValue)
                .trigger('input');
            triggerChange(elementId);
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
