<?php
/**
 * Class ConfigInterface
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Model\Installments\Config;


interface ConfigInterface
{
    const PATH_INSTALLMENTS_IS_ACTIVE                       = 'payment/%s/installments_active';
    const PATH_INSTALLMENTS_NUMBER                          = 'payment/%s/installments_number';
    const PATH_INSTALLMENTS_IS_WITH_INTEREST                = 'payment/%s/installments_is_with_interest';
    const PATH_INSTALLMENTS_MIN_MOUNT                       = 'payment/%s/installment_min_amount';
    const PATH_INSTALLMENTS_INTEREST_RATE                   = 'payment/%s/installments_interest_rate_initial';
    const PATH_INSTALLMENTS_INTEREST_RATE_INCREMENTAL       = 'payment/%s/installments_interest_rate_incremental';
    const PATH_INSTALLMENTS_INTEREST_BY_ISSUER              = 'payment/%s/installments_interest_by_issuer';
    const PATH_INSTALLMENTS_MAX_WITHOUT_INTEREST            = 'payment/%s/installments_max_without_interest';

    public function isActive();

    public function getInstallmentsNumber();

    public function isWithInterest();

    public function getInstallmentMinAmount();

    public function getInterestRate();

    public function getInterestRateIncremental();

    public function isInterestByIssuer();

    public function getInstallmentsMaxWithoutInterest();
}
