require([
    'Magento_Ui/js/modal/alert',
    'jquery',
    'jquery/ui',
    'mage/translate',
    'loader',
    'pagarmeJqueryMask'
], function (alert, $) {
    'use strict';

    const
        cpfMask = '000.000.000-00',
        cnpjMax = 18, // Includes punctuation due to the mask
        cnpjMask = '00.000.000/0000-00',
        fieldDataAttr = {
            datepicker: '[data-datepicker]',
            toggleRequired: '[data-toggle-required]'
        },
        fieldId = {
            seller: '#webkul-seller',
            recipientId: '#recipient-id',
            documentType: '#document-type',
            document: '#document',
            transferEnabled: '#transfer-enabled',
            transferInterval: '#transfer-interval',
            companyName: '#recipient-company-name',
            email: '#recipient-email',
            birthdate: '#recipient-birthdate',
            corporationType: '#recipient-corporation-type',
            holderDocumentType: '#holder-document-type',
            holderDocument: '#holder-document',
            holderName: '#holder-name'
        };

    $(document).ready(function () {

        localizeDatePickerToPtBr();
        applyDatePickerToField(fieldDataAttr['datepicker']);
        maskFields();

        $('#pagarme-recipients-form').on('submit', formSubmit);

        $('#existing_recipient').on('change', function () {
            slideUpOrDownSectionByFieldVal('pagarme_id', $(this).val());
        });

        $(fieldId['seller']).on('change', function () {
            const webkulId = $(this).val();
            fillRecipientByWebkulId(webkulId);

            if (!webkulId) {
                slideUpOrDownSections(''); // Hide all sections
                return;
            }

            slideUpOrDownSections('seller', 'show');
        });

        $(fieldId['documentType']).on('change', function () {
            const documentType = $(this).val();
            changeDocumentFieldsByType(documentType);
            slideUpOrDownSections(['individual', 'corporation']);
            if (documentType !== '') {
                slideUpOrDownSections(documentType, 'show');
                return;
            }
            $(this).removeClass('readonly');
        });

        $(fieldId['document'])
            .on('keyup change', function () {
                $(fieldId['holderDocument'])
                    .val($(this).val()).trigger('input');
            })
            .on('change', function () {
                const documentNumber = $(this).val();
                if (documentNumber.length === cnpjMax) {
                    getCnpjData(documentNumber);
                }
                if (documentNumber === '') {
                    $(this).attr('readonly', false);
                }
            });

        $('#recipient-name, #recipient-company-name').on('change', function () {
            $(fieldId['holderName']).val($(this).val());
        });

        $('[data-cep-search]').on('change', function () {
            const cepNumber = $(this).val().replace(/\D/g, '');
            if (cepNumber.length === 8) {
                getCepData($(this), cepNumber);
            }
        });

        $('[data-state]').on('change', function () {
            const stateCode = $(this).val();

            if (!stateCode) {
                return;
            }

            const addressFieldset = $(this).closest('.admin__fieldset'),
                citiesListId = addressFieldset.find('datalist[id$="-cities"]').attr('id');
            let citiesList = '';

            $.get(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${stateCode}/distritos`)
                .done(function (data) {
                    $.each(data, function (index) {
                        citiesList += `<option value="${data[index].nome}">${data[index].nome}</option>`;
                    });
                    fillDatalistOptions(citiesListId, citiesList);
                });
        });

        $(fieldId['transferEnabled']).on('change', function () {
            slideUpOrDownSectionByFieldVal('transfer_interval', $(this).val());
        });

        $(fieldId['transferInterval']).on('change', function () {
            slideUpOrDownSections(
                'transfer_day',
                $(this).val() === 'Daily' ? 'hide' : 'show'
            );
            fillTransferDayValuesByTransferInterval();
        });

        const editRecipient = $('#edit-recipient').val();
        if (editRecipient.length > 0) {
            $('#webkul-seller-container').hide();
            loadRecipient(JSON.parse(editRecipient));
        }

        $(fieldId['recipientId']).on('change', function () {
            const
                recipientVal = $(this).val(),
                controlValue = $('#recipient-id-control').val();
            if (controlValue === '' || recipientVal === controlValue) {
                return;
            }

            resetFields();
        });

        $('#search-recipient-id').on('click', searchRecipient);
    });

    function changeDocumentFieldsByType(documentType) {
        const documentMask = documentType === 'corporation' ? cnpjMask : cpfMask;

        $(fieldId['holderDocumentType'])
            .val(documentTypeCorporationToCompany(documentType));

        $('#document, #holder-document')
            .attr('placeholder', documentMask)
            .mask(documentMask);
    }

    function documentTypeCorporationToCompany(documentType) {
        return documentType === 'corporation' ? 'company' : documentType;
    }

    function documentTypeCompanyToCorporation(documentType) {
        return documentType === 'company' ? 'corporation' : documentType;
    }

    function getNameFieldByDocumentType(documentType) {
        return documentType === 'individual' ? '#recipient-name' : fieldId['companyName'];
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

    function fillRecipientByWebkulId(webkulId) {
        showLoader();

        $('#webkul-id')
            .val(webkulId)
            .prop('readonly', !!webkulId);

        const
            selectedWebkulSeller = $(fieldId['seller']).find(':selected'),
            recipientDocument = selectedWebkulSeller.attr('data-document'),
            documentType = getDocumentTypeByDocument(recipientDocument);
        $('#document-type, #holder-document-type')
            .val(documentType)
            .trigger('change');
        $(fieldId['document'])
            .val(recipientDocument)
            .trigger('input')
            .trigger('change');
        $(fieldId['holderDocument'])
            .val(documentTypeCorporationToCompany(recipientDocument))
            .trigger('input');

        const recipientName = selectedWebkulSeller.attr('data-sellername');
        $(getNameFieldByDocumentType(documentType))
            .val(recipientName)
            .trigger('change');

        const recipientEmail = selectedWebkulSeller.attr('data-email');
        $(fieldId['email'])
            .val(recipientEmail);

        const recipientBirthdate = selectedWebkulSeller.attr('data-birthdate');
        $(fieldId['birthdate'])
            .val(formatDate(recipientBirthdate));

        hideLoader();
    }

    function getCnpjData(documentNumber) {
        documentNumber = documentNumber.replace(/\D/g, '');
        const maxRequests = 3;
        let requests = 0;
        $.ajax({
            url: `https://brasilapi.com.br/api/cnpj/v1/${documentNumber}`,
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
                            setTimeout($(fieldId['document']).trigger('change'), 50);
                        }
                    }
                }
            }
        }).done(function (data) {
            fillCompanyData(data);
        }).fail(function (data) {
            console.error(data);
        });
    }

    function fillCompanyData(cnpjData) {
        if (cnpjData['qsa']) {
            let partnersList = '';
            $.each(cnpjData['qsa'], function (index) {
                partnersList += `<option value="${capitalizeAllWords(cnpjData['qsa'][index]['nome_socio'])}">
                    ${cnpjData['qsa'][index]['qualificacao_socio']}</option>`;
            });
            fillDatalistOptions('recipient-partner-0-partners-list', partnersList);
        }

        const phoneNumber = [
            cnpjData['ddd_telefone_1'],
            cnpjData['ddd_telefone_2']
        ];

        $(fieldId['companyName']).val(capitalizeAllWords(cnpjData['razao_social'])).trigger('change');
        $('#recipient-trading-name').val(capitalizeAllWords(cnpjData['nome_fantasia']));
        $('#recipient-founding-date').val(formatDate(cnpjData['data_inicio_atividade']));
        $(fieldId['corporationType']).val(cnpjData['natureza_juridica']);
        $('#recipient-phones-type-0').val(phoneNumber[0].length === 10 ? 'home_phone' : 'mobile_phone');
        $('#recipient-phones-number-0').val(phoneNumber[0]).trigger('input');
        $('#recipient-phones-type-1').val(phoneNumber[1].length === 10 ? 'home_phone' : 'mobile_phone');
        $('#recipient-phones-number-1').val(phoneNumber[1]).trigger('input');
        $('#company-zip-code').val(cnpjData['cep']).trigger('change').trigger('input');
        $('#company-street-number').val(cnpjData['numero']);
        $('#company-complementary').val(capitalizeAllWords(cnpjData['complemento']));
        if (cnpjData['qsa'].length) {
            $('#recipient-partner-0-name').val(capitalizeAllWords(cnpjData['qsa'][0]['nome_socio']));
            $('#recipient-partner-0-professional-occupation').val(cnpjData['qsa'][0]['qualificacao_socio'])
        }
    }

    function getCepData(element, cepNumber) {
        $.ajax({
            url: `https://viacep.com.br/ws/${cepNumber}/json/`,
            method: 'get',
            showLoader: true,
            beforeSend: function (xhr) {
                // Empty to remove Magento's default handler
            }
        }).done(function (data) {
            fillAddressFields(element.closest('.admin__fieldset'), data)
        }).fail(function (data) {
            console.error(data)
        });
    }

    function fillAddressFields(addressFieldset, cepData) {
        addressFieldset.find('input[id$="-street"]').val(cepData.logradouro);
        addressFieldset.find('input[id$="-neighborhood"]').val(cepData.bairro);
        addressFieldset.find('select[id$="-state"]').val(cepData.uf).trigger('change');
        addressFieldset.find('input[id$="-city"]').val(cepData.localidade);
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

        const array = sentence.split(' ');
        for (let i = 0; i < array.length; i++) {
            array[i] = array[i][0].toUpperCase() + array[i].substr(1).toLowerCase();
        }

        return array.join(' ');
    }

    function searchRecipient(e) {
        e.preventDefault();
        showLoader();

        const recipientId = $(fieldId['recipientId']).val(),
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
                    mageAlert(response.message, 'Error!');
                    return;
                }

                loadRecipient(response.recipient);
                $('#recipient-id-control').val(recipientId);

                hideLoader();
            });

    }

    function formSubmit(e) {
        e.preventDefault();

        if (!validateEmail($("#recipient-email").val())) {
            mageAlert('Invalid email.', 'Error!');
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
                    mageAlert(data.message, 'Success!');
                    return window.history.back();
                }
                mageAlert(data.message, 'Error!');
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

    function hideSection(section) {
        const fields = section ? $(`[data-section*="${section}"]`) : $('[data-section]');
        fields.each(function () {
            $(this)
                .hide()
                .find(fieldDataAttr['toggleRequired']).prop('required', false);
        });
    }

    function slideUpOrDownSectionByFieldVal(section, value) {
        slideUpOrDownSections(section, value === '1' ? 'show' : 'hide');
    }

    function slideUpOrDownSections(sections, action = 'hide') {
        let fields;
        if (Array.isArray(sections)) {
            let dataSections = '';
            $.each(sections, function (key, val) {
                dataSections += `[data-section*="${val}"]`;
                dataSections += (key < sections.length - 1) ? ', ' : '';
            });
            fields = $(dataSections);
        } else {
            fields = sections ? $(`[data-section*="${sections}"]`) : $('[data-section]');
        }

        if (action === 'hide') {
            fields.each(function () {
                $(this)
                    .slideUp('250')
                    .find(fieldDataAttr['toggleRequired']).prop('required', false);
            });
        }

        if (action === 'show') {
            fields.each(function () {
                $(this)
                    .slideDown('250')
                    .find(fieldDataAttr['toggleRequired']).prop('required', true);
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
            $.each(transferWeekDays, function (value, label) {
                transferDay.append(`<option value="${value}">${label}</option>`);
            });
        }

        if (transferDayValue === 'Monthly') {
            transferDay.children().remove();
            for (let i = 1; i < 32; i++) {
                transferDay.append(`<option value="${i}">${i}</option>`);
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

    function isNewRecipientId(recipient) {
        return $.type(recipient.register_information) === 'object';
    }

    function buildRecipientObject(recipient) {
        const recipientObject = {};
        if (isNewRecipientId(recipient)) {
            recipientObject[fieldId['documentType']] =
                documentTypeCompanyToCorporation(recipient.register_information.type);
            recipientObject[fieldId['document']] = recipient.register_information.document;
            recipientObject[fieldId['email']] = recipient.register_information.email;
            recipientObject['#recipient-site'] = recipient.register_information.site_url;

            $.each(recipient.register_information.phone_numbers, function (index, phone) {
                recipientObject['#recipient-phones-type-' + index] = phone.type;
                recipientObject['#recipient-phones-number-' + index] = phone.ddd + phone.number;
            });

            switch (recipient.register_information.type) {
                case 'individual':
                    recipientObject['#recipient-name'] = recipient.register_information.name;
                    recipientObject['#recipient-mother-name'] = recipient.register_information.mother_name;
                    recipientObject[fieldId['birthdate']] = formatDate(recipient.register_information.birthdate);
                    recipientObject['#recipient-monthly-income'] = recipient.register_information.monthly_income;
                    recipientObject['#recipient-professional-occupation'] =
                        recipient.register_information.professional_occupation;

                    recipientObject['#recipient-zip-code'] = recipient.register_information.address.zip_code;
                    recipientObject['#recipient-street'] = recipient.register_information.address.street;
                    recipientObject['#recipient-street-number'] = recipient.register_information.address.street_number;
                    recipientObject['#recipient-complementary'] = recipient.register_information.address.complementary;
                    recipientObject['#recipient-reference-point'] =
                        recipient.register_information.address.reference_point;
                    recipientObject['#recipient-neighborhood'] = recipient.register_information.address.neighborhood;
                    recipientObject['#recipient-state'] = recipient.register_information.address.state;
                    recipientObject['#recipient-city'] = recipient.register_information.address.city;
                    break;
                case 'company':
                case 'corporation':
                    recipientObject[fieldId['companyName']] = recipient.register_information.company_name;
                    recipientObject['#recipient-trading-name'] = recipient.register_information.trading_name;
                    recipientObject['#recipient-annual-revenue'] = recipient.register_information.annual_revenue;
                    recipientObject[fieldId['corporationType']] = recipient.register_information.corporation_type;
                    recipientObject['#recipient-founding-date'] =
                        formatDate(recipient.register_information.founding_date);

                    recipientObject['#company-zip-code'] = recipient.register_information.main_address.zip_code;
                    recipientObject['#company-street'] = recipient.register_information.main_address.street;
                    recipientObject['#company-street-number'] =
                        recipient.register_information.main_address.street_number;
                    recipientObject['#company-complementary'] =
                        recipient.register_information.main_address.complementary;
                    recipientObject['#company-reference-point'] =
                        recipient.register_information.main_address.reference_point;
                    recipientObject['#company-neighborhood'] =
                        recipient.register_information.main_address.neighborhood;
                    recipientObject['#company-state'] = recipient.register_information.main_address.state;
                    recipientObject['#company-city'] = recipient.register_information.main_address.city;
                    break;
            }

            $.each(recipient.register_information.managing_partners, function (partnerIndex, partner) {
                const idPrefix = `#recipient-partner-${partnerIndex}`;
                recipientObject[`${idPrefix}-name`] = partner.name;
                recipientObject[`${idPrefix}-document-type`] = partner.type;
                recipientObject[`${idPrefix}-document`] = partner.document;
                recipientObject[`${idPrefix}-mother-name`] = partner.mother_name;
                recipientObject[`${idPrefix}-email`] = partner.email;
                recipientObject[`${idPrefix}-birthdate`] = formatDate(partner.birthdate);
                recipientObject[`${idPrefix}-monthly-income`] = partner.monthly_income;
                recipientObject[`${idPrefix}-professional-occupation`] = partner.professional_occupation;
                recipientObject[`${idPrefix}-declaration`] = partner.self_declared_legal_representative;

                recipientObject[`${idPrefix}-zip-code`] = partner.address.zip_code;
                recipientObject[`${idPrefix}-street`] = partner.address.street;
                recipientObject[`${idPrefix}-street-number`] = partner.address.street_number;
                recipientObject[`${idPrefix}-complementary`] = partner.address.complementary;
                recipientObject[`${idPrefix}-reference-point`] = partner.address.reference_point;
                recipientObject[`${idPrefix}-neighborhood`] = partner.address.neighborhood;
                recipientObject[`${idPrefix}-state`] = partner.address.state;
                recipientObject[`${idPrefix}-city`] = partner.address.city;

                $.each(partner.phone_numbers, function (phoneIndex, phone) {
                    recipientObject[`${idPrefix}-phones-type-` + phoneIndex] = phone.type;
                    recipientObject[`${idPrefix}-phones-number-` + phoneIndex] = phone.ddd + phone.number;
                });
            });

            recipientObject[fieldId['holderDocument']] = recipient.register_information.document;
        }

        if (!isNewRecipientId(recipient)) {
            recipientObject[fieldId['documentType']] = documentTypeCompanyToCorporation(recipient.type);
            recipientObject[fieldId['document']] = recipient.document;
            recipientObject[getNameFieldByDocumentType(recipient.type)] = recipient.name;
            recipientObject[fieldId['email']] = recipient.email;

            recipientObject[fieldId['holderDocument']] = recipient.document;
        }

        recipientObject[fieldId['holderName']] = recipient.default_bank_account.holder_name;
        recipientObject[fieldId['holderDocumentType']] =
            documentTypeCorporationToCompany(recipient.default_bank_account.holder_type);
        recipientObject['#bank'] = recipient.default_bank_account.bank;
        recipientObject['#branch-number'] = recipient.default_bank_account.branch_number;
        recipientObject['#branch-check-digit'] = recipient.default_bank_account.branch_check_digit;
        recipientObject['#account-number'] = recipient.default_bank_account.account_number;
        recipientObject['#account-check-digit'] = recipient.default_bank_account.account_check_digit;
        recipientObject['#account-type'] = recipient.default_bank_account.type;

        recipientObject[fieldId['transferEnabled']] = recipient.transfer_settings.transfer_enabled ? 1 : 0;
        recipientObject[fieldId['transferInterval']] = recipient.transfer_settings.transfer_interval;
        recipientObject['#transfer-day'] = recipient.transfer_settings.transfer_day;

        return recipientObject;
    }

    function triggerChangeToShowFields(elementId) {
        const changeElements = [
            fieldId['documentType'],
            fieldId['transferEnabled'],
            fieldId['transferInterval']
        ];
        if ($.inArray(elementId, changeElements) >= 0) {
            $(elementId).trigger('change');
        }
    }

    function blockElement(element) {
        const neverBlockIds = [
            'webkul-seller',
            'existing_recipient',
            'recipient-id'
        ];
        if ($.inArray(element.attr('id'), neverBlockIds) >= 0) {
            return;
        }

        if (element.is('select')) {
            element.addClass('readonly');
        } else {
            element.attr('readonly', true);
        }

        if (element.is(fieldDataAttr['datepicker'])) {
            element.datepicker('destroy');
        }
    }

    function unblockElement(element) {
        const
            neverUnblockIds = [
                'holder-name',
                'holder-document-type',
                'holder-document'
            ],
            elementId = element.attr('id');
        if ($.inArray(elementId, neverUnblockIds) >= 0) {
            return;
        }

        if (element.is('select')) {
            element.removeClass('readonly');
        } else {
            element.attr('readonly', false);
        }

        if (element.is(fieldDataAttr['datepicker'])) {
            applyDatePickerToField(`#${elementId}`);
        }
    }

    function resetFields() {
        const neverResetIds = [
                'webkul-seller',
                'webkul-id',
                'existing_recipient',
                'recipient-id'
            ],
            fields = $('.admin__fieldset input[id], .admin__fieldset select[id]');

        $.each(fields, function (key, field) {
            field = $(field);
            if ($.inArray(field.attr('id'), neverResetIds) >= 0) {
                return;
            }

            if (field.is('select')) {
                field.prop('selectedIndex', 0).trigger('change');
            } else {
                field.val('');
            }
            unblockElement(field);
        });

        $(fieldId['holderName']).val($('#webkul-seller :selected').attr('data-sellername'));
    }

    function loadRecipient(recipient) {
        const recipientObject = buildRecipientObject(recipient);

        for (const elementId in recipientObject) {
            if (!Object.hasOwnProperty.call(recipientObject, elementId)) {
                continue;
            }

            const recipientValue = recipientObject[elementId];
            const element = $(elementId);
            element.val(recipientValue)
                .trigger('input');

            triggerChangeToShowFields(elementId);
        }

        if (!isNewRecipientId(recipient)) {
            hideSection('new-field');
        }

        const fields = $('.admin__fieldset input[id], .admin__fieldset select[id]');
        $.each(fields, function (key, field) {
            blockElement($(field));
        });
    }

    function mageAlert(content, title = null) {
        alert({
            title: $.mage.__(title),
            content: $.mage.__(content)
        });
    }

    function showLoader() {
        $('body').loader('show');
    }

    function hideLoader() {
        $('body').loader('hide');
    }

    function phoneMaskBehavior(val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    }

    function maskFields() {
        const phoneMaskOptions = {
            onKeyPress: function (val, e, field, options) {
                field.mask(phoneMaskBehavior.apply({}, arguments), options);
            }
        };

        $('[data-phone-mask]').mask(phoneMaskBehavior, phoneMaskOptions);
        $('[data-date-mask]').mask('00/00/0000');
        $('[data-currency-mask]').mask("#.##0,00", {reverse: true});
        $('[data-zipcode-mask]').mask('00000-000');
    }

    function formatDate(date) {
        if (!date) {
            return date;
        }

        const dateArray = date.split('-');
        if (dateArray.length === 3) {
            return `${dateArray[2]}/${dateArray[1]}/${dateArray[0]}`;
        }

        return date;
    }

    function applyDatePickerToField(element) {
        $(element).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd/mm/yy',
            maxDate: 0,
            minDate: '-122y', // Jeanne Calment
            showMonthAfterYear: true,
            yearRange: 'c-122:c0'
        });
    }

    function localizeDatePickerToPtBr() {
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

});
