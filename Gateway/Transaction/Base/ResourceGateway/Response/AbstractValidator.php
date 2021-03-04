<?php
/**
 * Class AbstractValidator
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\Base\ResourceGateway\Response;


use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Payment\Gateway\Validator\ResultInterface;

abstract class AbstractValidator
{
    protected $resultInterfaceFactory;

    /**
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory
    )
    {
        $this->setResultInterfaceFactory($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    abstract public function validate(array $validationSubject);

    /**
     * @param bool $isValid
     * @param array $fails
     * @return ResultInterface
     */
    protected function createResult($isValid, array $fails = [])
    {
        return $this->getResultInterfaceFactory()->create(
            [
                'isValid' => (bool)$isValid,
                'failsDescription' => $fails
            ]
        );
    }

    /**
     * @return ResultInterfaceFactory
     */
    protected function getResultInterfaceFactory()
    {
        return $this->resultInterfaceFactory;
    }

    /**
     * @param ResultInterfaceFactory $resultInterfaceFactory
     * @return $this
     */
    protected function setResultInterfaceFactory(ResultInterfaceFactory $resultInterfaceFactory)
    {
        $this->resultInterfaceFactory = $resultInterfaceFactory;
        return $this;
    }
}
