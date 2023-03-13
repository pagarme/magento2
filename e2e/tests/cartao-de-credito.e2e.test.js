const { test, expect } = require('@playwright/test')
const { searchProduct, 
        selectProduct, 
        addToCart, 
        proceedCheckout } = require('../helper/product_helper')
const { informEmail, 
        informFirstAndLastName, 
        informCompany, 
        informAddress, 
        selectState, 
        selectCountry, 
        informCEP, 
        informCity, 
        informPhoneNumber, 
        informVatNumber, 
        goToCheckoutNextPage } = require('../helper/checkout_helper')
const { selectCreditCardOPtion, 
        informCreditCartNumber, 
        informCreditCartName, 
        selectExpireDate, 
        informCVV, 
        finalizeCheckout } = require('../helper/pagarme_helper')
const { user_information, 
        address_infomration,
        vat_information, 
        credit_card_information_valid } = require('../helper/data_helper')

test.describe('Cartão de Crédito', () => {
    test.beforeEach(async ({ page}) => {
        await page.goto('/#')
    })


    test('Criar pedido com CPF', async ({page}) => {
        const user = user_information();
        const address = address_infomration();
        const credit_card = credit_card_information_valid();
        const vat = vat_information();
        await searchProduct(page, process.env.PRODUCT)
        await selectProduct(page)
        await addToCart(page)
        await proceedCheckout(page)
        await informEmail(page, user.email)
        await informFirstAndLastName(page, user.first_name, user.last_name)
        await informCompany(page, user.company)
        await informAddress(page, address.fisrt_address_line, address.second_address_line, address.third_address_line, address.four_address_line)
        await selectCountry(page, address.country)
        await selectState(page, address.state)
        await informCity(page, address.state)
        await informCEP(page, address.zip_code)
        await informPhoneNumber(page, user.phone_number)
        await informVatNumber(page, vat.valid_cpf)
        await goToCheckoutNextPage(page)
        await selectCreditCardOPtion(page)
        await informCreditCartNumber(page, credit_card.credit_card_number)
        await informCreditCartName(page, user.first_name)
        await selectExpireDate(page, '3', credit_card.credit_card_year)
        await informCVV(page, credit_card.credit_card_cvv)
        await finalizeCheckout(page)
        await expect(page.getByText('Thank you for your purchase!')).toBeVisible();  
    })

    test('Criar pedido com CNPJ', async ({page}) => {
        const user = user_information();
        const address = address_infomration();
        const credit_card = credit_card_information_valid();
        const vat = vat_information();
        await searchProduct(page, 'Pastel')
        await selectProduct(page)
        await addToCart(page)
        await proceedCheckout(page)
        await informEmail(page, user.email)
        await informFirstAndLastName(page, user.first_name, user.last_name)
        await informCompany(page, user.company)
        await informAddress(page, address.fisrt_address_line, address.second_address_line, address.third_address_line, address.four_address_line)
        await selectCountry(page, address.country)
        await selectState(page, address.state)
        await informCity(page, address.state)
        await informCEP(page, address.zip_code)
        await informPhoneNumber(page, user.phone_number)
        await informVatNumber(page, vat.valid_cnpj)
        await goToCheckoutNextPage(page)
        await selectCreditCardOPtion(page)
        await informCreditCartNumber(page, credit_card.credit_card_number)
        await informCreditCartName(page, user.first_name)
        await selectExpireDate(page, credit_card.credit_card_date_mounth, credit_card.credit_card_year)
        await informCVV(page, credit_card.credit_card_cvv)
        await finalizeCheckout(page)
        await expect(page.getByText('Thank you for your purchase!')).toBeVisible();  
    })
})