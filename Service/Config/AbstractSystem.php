<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Service\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class AbstractSystem
 * @package Pagarme\Pagarme\Service\Config
 */
abstract class AbstractSystem
{
    /** @var WriterInterface */
    private $_configWriter;

    /** @var ScopeConfigInterface */
    private $_scopeConfig;

    /** @var string */

    protected string $_scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

    /** @var int|string|null */
    protected $_scopeCode = null;

    /** @var int */
    protected int $_scopeId = 0;

    /**
     * SystemAbstract constructor.
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_configWriter = $configWriter;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param string $scopeType
     * @return $this
     */
    public function setScopeType(string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        $this->_scopeType = $scopeType;
        return $this;
    }

    /**
     * @param int|null|string $scopeCode
     * @return $this
     */
    public function setScopeCode($scopeCode = null)
    {
        $this->_scopeCode = $scopeCode;
        return $this;
    }

    /**
     * @param int $scopeId
     * @return $this
     */
    public function setScopeId(int $scopeId = 0)
    {
        $this->_scopeId = $scopeId;
        return $this;
    }

    /**
     * @param string $path
     * @return mixed
     */
    protected function getValue(string $path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            $this->_scopeType,
            $this->_scopeCode
        );
    }

    /**
     * @param $path
     * @param $value
     * @return void
     */
    public function setValue($path, $value)
    {
        $this->_configWriter->save(
            $path,
            $value,
            $this->_scopeType,
            $this->_scopeId
        );
    }
}
