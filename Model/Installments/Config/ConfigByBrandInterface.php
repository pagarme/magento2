<?php
/**
 * Class ConfigByBrandInterface
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model\Installments\Config;


interface ConfigByBrandInterface
{
    const PATH_INSTALLMENTS_IS_ACTIVE                       = 'payment/%s/installments_active';
    const PATH_INSTALLMENTS_UNIQUE                          = 'payment/%s/installments_type';
    const PATH_INSTALLMENTS_NUMBER                          = 'payment/%s/installments_number%s';
    const PATH_INSTALLMENTS_IS_WITH_INTEREST                = 'payment/%s/installments_is_with_interest%s';
    const PATH_INSTALLMENTS_MIN_MOUNT                       = 'payment/%s/installment_min_amount%s';
    const PATH_INSTALLMENTS_INTEREST_RATE                   = 'payment/%s/installments_interest_rate_initial%s';
    const PATH_INSTALLMENTS_INTEREST_RATE_INCREMENTAL       = 'payment/%s/installments_interest_rate_incremental%s';
    const PATH_INSTALLMENTS_INTEREST_BY_ISSUER              = 'payment/%s/installments_interest_by_issuer%s';
    const PATH_INSTALLMENTS_MAX_WITHOUT_INTEREST            = 'payment/%s/installments_max_without_interest%s';

    public function isActive();

    public function getInstallmentUnique();

    public function getInstallmentsNumber();

    public function isWithInterest();

    public function getInstallmentMinAmount();

    public function getInterestRate();

    public function getInterestRateIncremental();

    public function isInterestByIssuer();

    public function getInstallmentsMaxWithoutInterest();
}
