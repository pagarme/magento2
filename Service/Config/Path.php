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

/**
 * Class Path
 * @package Pagarme\Pagarme\Service\Config
 */
class Path
{
    /** @var string */
    const PAGARME_PAGARME = 'pagarme_pagarme';

    /** @var string */
    const GLOBAL = 'global';

    /** @var string */
    const PUBLIC_KEY = 'public_key';

    /** @var string */
    const SECRET_KEY = 'secret_key';

    /** @var string */
    const TEST = 'test';

    /** @var string */
    const TEST_MODE = 'test_mode';

    /** @var string */
    const PAGARME_PAGARME_GLOBAL_PUBLIC_KEY = self::PAGARME_PAGARME . DIRECTORY_SEPARATOR . self::GLOBAL . DIRECTORY_SEPARATOR . self::PUBLIC_KEY;

    /** @var string */
    const PAGARME_PAGARME_GLOBAL_SECRET_KEY = self::PAGARME_PAGARME . DIRECTORY_SEPARATOR . self::GLOBAL . DIRECTORY_SEPARATOR . self::SECRET_KEY;

    /** @var string */
    const PAGARME_PAGARME_GLOBAL_TEST_MODE = self::PAGARME_PAGARME . DIRECTORY_SEPARATOR . self::GLOBAL . DIRECTORY_SEPARATOR . self::TEST_MODE;
}
