<?php
/**
 * Class Validator
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\BilletCreditCard\ResourceGateway\Create\Response;


use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\Response\AbstractValidator;

class Validator extends AbstractValidator implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response'])) {
            throw new \InvalidArgumentException('Pagar.me Credit Card Authorize Response object should be provided');
        }

        $isValid = true;
        $fails = [];

        return $this->createResult($isValid, $fails);
    }
}
