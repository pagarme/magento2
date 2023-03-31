const locators = {
        customer_email: '.\_with-tooltip > #customer-email',
        shipping_new_address: '#shipping-new-address-form div',
        role_group: 'group',
        role_combobox: 'combobox',
        role_button: 'button',
        locator_div: 'div',
        has_text_first_name: 'First Name',
        has_text_last_name: 'Last Name',
        has_text_company: 'Company',
        name_text_address: 'Street Address *'
}


const informEmail = async (page, emailInfo) => {
    await page.click(locators.customer_email);
    await page.type(locators.customer_email, emailInfo);
}

const informFirstAndLastName = async (page, firstName, lastName) => {
    await page.locator(locators.shipping_new_address).filter({ hasText: locators.has_text_first_name }).locator(locators.locator_div).click();
    await page.locator(locators.shipping_new_address).filter({ hasText: locators.has_text_first_name }).locator(locators.locator_div).type(firstName);
    await page.locator(locators.shipping_new_address).filter({ hasText: locators.has_text_last_name }).locator(locators.locator_div).click();
    await page.locator(locators.shipping_new_address).filter({ hasText: locators.has_text_last_name }).locator(locators.locator_div).type(lastName);
}

const informCompany = async (page, companyName) => {
    await page.locator(locators.shipping_new_address).filter({ hasText: locators.has_text_company }).locator(locators.locator_div).click();
    await page.locator(locators.shipping_new_address).filter({ hasText: locators.has_text_company }).locator(locators.locator_div).type(companyName);
}

const informAddress = async (page, line1, line2, line3, line4) => {
    await page.getByRole(locators.role_group, { name: locators.name_text_address }).locator(locators.locator_div).filter({ hasText: 'Street Address: Line 1' }).nth(1).click();
    await page.getByRole(locators.role_group, { name: locators.name_text_address }).locator(locators.locator_div).filter({ hasText: 'Street Address: Line 1' }).nth(1).type(line1);
    await page.getByRole(locators.role_group, { name: locators.name_text_address }).locator(locators.locator_div).filter({ hasText: 'Street Address: Line 2' }).nth(1).click();
    await page.getByRole(locators.role_group, { name: locators.name_text_address }).locator(locators.locator_div).filter({ hasText: 'Street Address: Line 2' }).nth(1).type(line2);
    await page.getByRole(locators.role_group, { name: locators.name_text_address }).locator(locators.locator_div).filter({ hasText: 'Street Address: Line 3' }).nth(1).click();
    await page.getByRole(locators.role_group, { name: locators.name_text_address }).locator(locators.locator_div).filter({ hasText: 'Street Address: Line 3' }).nth(1).type(line3);
    await page.getByRole(locators.role_group, { name: locators.name_text_address }).locator(locators.locator_div).filter({ hasText: 'Street Address: Line 4' }).nth(1).click();
    await page.getByRole(locators.role_group, { name: locators.name_text_address }).locator(locators.locator_div).filter({ hasText: 'Street Address: Line 4' }).nth(1).type(line4);
    await page.waitForTimeout(1000); // voce de novo?
}

const selectState = async (page, optionState) => {
    await page.getByRole(locators.role_combobox, { name: 'Estado*' }).click();
    await page.getByRole(locators.role_combobox, { name: 'Estado*' }).selectOption(optionState);
}

const selectCountry = async (page, optionCountry) => {
    await page.getByRole(locators.role_combobox, { name: 'Country*' }).click();
    await page.getByRole(locators.role_combobox, { name: 'Country*' }).selectOption(optionCountry);
}

const informCEP = async (page, cepNumber) => {
    await page.getByLabel('CEP').click();
    await page.getByLabel('CEP').fill(cepNumber);
}

const informCity = async (page, cityName) => {
    await page.getByLabel('Cidade').click();
    await page.getByLabel('Cidade').fill(cityName);
}

const informPhoneNumber = async (page, phoneNumber) => {
    await page.getByLabel('Phone Number').click();
    await page.getByLabel('Phone Number').fill(phoneNumber);
}

const informVatNumber = async (page, cpfNumber) => {
    await page.getByLabel('VAT Number').click();
    await page.getByLabel('VAT Number').fill(cpfNumber);
}

const goToCheckoutNextPage = async page => {
    await page.getByRole(locators.role_button, { name: 'Próximo' }).click();
    await page.waitForTimeout(1000); //ninguem gosta disso, imagina ter que usar ne?
}


module.exports = {
    informEmail,
    informFirstAndLastName,
    informCompany,
    informAddress,
    selectState,
    selectCountry,
    informCEP,
    informCity,
    informPhoneNumber,
    informVatNumber,
    goToCheckoutNextPage
}
