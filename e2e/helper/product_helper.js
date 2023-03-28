
const searchProduct = async (page, productName) => {
    await page.click('#search')
    await page.type('#search', productName)
    await page.locator('#search').press('Enter');

}

const selectProduct = async page => {
    await page.click('.product-item-info')
}

const addToCart = async page =>  {
    await page.click('#product-addtocart-button')
}

const proceedCheckout = async page => {
    await page.waitForTimeout(2000); //ninguem gosta disso, imagina ter que usar ne?
    await page.click('.counter:nth-child(2)')
    await page.waitForTimeout(1000); //ninguem gosta disso, imagina ter que usar ne?
    await page.click('#top-cart-btn-checkout')
}

module.exports = {
    searchProduct,
    selectProduct,
    addToCart,
    proceedCheckout
}
