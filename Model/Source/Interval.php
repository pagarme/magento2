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
use Pagarme\Pagarme\Model\Source\EavInterval;

class Interval implements ArrayInterface
{

    public function toOptionArray() {
        $eav = new EavInterval();
        return $eav->getAllOptions();
    }

}
