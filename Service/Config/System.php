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

use Pagarme\Pagarme\Api\SystemInterface;

/**
 * Class System
 * @package Pagarme\Pagarme\Service\Config
 */
class System extends AbstractSystem implements SystemInterface
{
    /**
     * @param $scopeType
     * @param $scopeCode
     * @return bool
     */
    public function getTestMode($scopeType = null, $scopeCode = null): bool
    {
        if ($scopeType) {
            $this->_scopeType = $scopeType;
        }
        if ($scopeCode) {
            $this->_scopeCode = $scopeCode;
        }
        return (bool) $this->getValue(Path::PAGARME_PAGARME_GLOBAL_TEST_MODE);
    }

    /**
     * @param string $type
     * @param $scopeType
     * @param $scopeCode
     * @return string
     */
    public function getkey(string $type = Path::PUBLIC_KEY, $scopeType = null, $scopeCode = null): string
    {
        $path = Path::PAGARME_PAGARME . DIRECTORY_SEPARATOR . Path::GLOBAL . DIRECTORY_SEPARATOR . $type;
        if ($scopeType) {
            $this->_scopeType = $scopeType;
        }
        if ($scopeCode) {
            $this->_scopeCode = $scopeCode;
        }
        if ($this->getTestMode()) {
            $path .= '_' . Path::TEST;
        }
        return (string) $this->getValue($path);
    }
}
