
const locators = {
    cc_number: '#payment_form_pagarme_creditcard input[name="payment\\[cc_number\\]"]',
    cc_owner: '#payment_form_pagarme_creditcard input[name="payment\\[cc_owner\\]"]',
    cc_exp_month: '#payment_form_pagarme_creditcard select[name="payment\\[cc_exp_month\\]"]',
    cc_exp_year: '#payment_form_pagarme_creditcard select[name="payment\\[cc_exp_year\\]"]',
    cc_cid: '#payment_form_pagarme_creditcard input[name="payment\\[cc_cid\\]"]'

}


const selectCreditCardOPtion = async page => {
    await page.getByLabel('Pagar.me Credit Card').check();
    await page.waitForTimeout(1000); //ninguem gosta disso, imagina ter que usar ne?
}

const informCreditCartNumber = async (page, creditCartNumber) => {
    await page.locator(locators.cc_number).click();
    await page.locator(locators.cc_number).fill(creditCartNumber);
}

const informCreditCartName = async (page, creditCartName) => {
    await page.locator(locators.cc_owner).click();
    await page.locator(locators.cc_owner).fill(creditCartName);
}

const selectExpireDate = async (page, dateNumber, YearNumber) => {
    await page.locator(locators.cc_exp_month).selectOption(dateNumber);
    await page.locator(locators.cc_exp_year).selectOption(YearNumber);
}

const informCVV = async (page, cvvNumber) => {
    await page.locator(locators.cc_cid).click();
    await page.locator(locators.cc_cid).fill(cvvNumber);
}

const finalizeCheckout = async page => {
    await page.getByRole('button', { name: 'Fazer pedido' }).click();
}


module.exports = {
    selectCreditCardOPtion,
    informCreditCartNumber,
    informCreditCartName,
    selectExpireDate,
    informCVV,
    finalizeCheckout
}