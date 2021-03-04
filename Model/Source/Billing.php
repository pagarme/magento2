<?php
/**
 * Class PaymentMethodsAction
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Pagarme\Pagarme\Model\Source\EavBillingType;

class Billing implements ArrayInterface
{
    /*
     * Option getter
     * @return array
     */

    public function toOptionArray() {
        $eav = new EavBillingType();
        return $eav->getAllOptions();
    }

}
