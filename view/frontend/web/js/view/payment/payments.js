/**
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com  Copyright
 *
 * @link        http://www.mundipagg.com
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {

        rendererList.push(
            {
                type: 'mundipagg_creditcard',
                component: 'MundiPagg_MundiPagg/js/view/payment/creditcard'
            },
            {
                type: 'mundipagg_billet',
                component: 'MundiPagg_MundiPagg/js/view/payment/boleto'
            }

        );
        return Component.extend({});
    }
);
