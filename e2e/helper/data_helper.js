const { faker } = require('@faker-js/faker')

const user_information = () => {
    return {
        first_name: faker.name.firstName(),
        last_name: faker.name.lastName(),
        email: faker.internet.email(),
        company: faker.company.name(),
        phone_number: faker.phone.number('+5551#########')
    }
}

const vat_information = () => {
    return {
        valid_cpf: '82006487091',
        valid_cnpj: '07288037000106',
    }
}


const address_infomration = () => {
    return {
        fisrt_address_line: faker.address.street(),
        second_address_line: faker.address.secondaryAddress(),
        third_address_line: faker.address.streetAddress(),
        four_address_line: faker.address.street(),
        country: 'BR',
        state: '502', //O magento representa por numeros, então está selecionado o RJ no momento.
        city: 'Rio de Janeiro',
        zip_code: faker.address.zipCode('########')

    }
}

const credit_card_information_valid = () => {
    return {
        credit_card_number: '4000000000000010',
        credit_card_date_month: '3',
        credit_card_year: '2032',
        credit_card_cvv: '997'
    }
}

module.exports = {
    user_information,
    vat_information,
    address_infomration,
    credit_card_information_valid
}