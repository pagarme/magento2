<?php
/**
 * Class AbstractValidator
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\Base\ResourceGateway\Response;


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
