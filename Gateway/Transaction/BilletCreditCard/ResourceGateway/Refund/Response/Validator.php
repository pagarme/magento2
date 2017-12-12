<?php
/**
 * Class Validator
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\BilletCreditCard\ResourceGateway\Refund\Response;


use Magento\Payment\Gateway\Validator\ValidatorInterface;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\ResourceGateway\Response\AbstractValidator;

class Validator extends AbstractValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response'])) {
            throw new \InvalidArgumentException('MundiPagg Credit Card Refund Response object should be provided');
        }

        $isValid = true;
        $fails = [];

        return $this->createResult($isValid, $fails);
    }
}
