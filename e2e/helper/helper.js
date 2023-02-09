const { faker } = require('@faker-js/faker')


const searchProduct = async page => {
    await page.click('#search')
    await page.type('#search', 'Pastel')
    await page.locator('#search').press('Enter');

}

const selectTheProduct = async page => {
    await page.click('#product-item-info_1')
}

const addToCart = async page =>  {
    await page.click('#product-addtocart-button')
}

const proceedCheckout = async page => {
    await page.waitForTimeout(2000);
    await page.click('.counter:nth-child(2)')
    await page.waitForTimeout(1000);
    await page.click('#top-cart-btn-checkout')
}

module.exports = {
    searchProduct,
    selectTheProduct,
    addToCart,
    proceedCheckout
}