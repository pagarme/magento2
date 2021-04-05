<?php

namespace Pagarme\Pagarme\Helper;

use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\ValueObjects\TransactionType;
use Pagarme\Core\Kernel\Aggregates\Charge;

class BuildChargeAddtionalInformationHelper
{
    /**
     * @param string $paymentMethodPlatform
     * @param Charge[] $charges
     * @return array
     */
    public static function build($paymentMethodPlatform, array $charges)
    {
        /**
         * @var string contains method name, call dynamic
         */
        $methodName = str_replace(
            ['_', 'Pagarme'],
            '',
            ucwords($paymentMethodPlatform, "_")
        );

        $methodName = "build{$methodName}";

        if (!method_exists(self::class, $methodName)) {
            return [];
        }

        return self::$methodName($charges);
    }

    /**
     * @param Charge[] $charges
     * @return array
     */
    private static function buildBilletCreditcard(array $charges)
    {
        $chargeInformation = [];
        foreach ($charges as $key => $charge) {
            /**
             * @var Transaction $billetTransaction
             */
            $billetTransaction = array_filter(
                $charge->getTransactions(),
                function (Transaction $transaction) {
                    return $transaction->getTransactionType()->equals(
                        TransactionType::boleto()
                    );
                }
            );

            if (empty($billetTransaction)) {
                $acquirerNsuCapturedAndAutorize =
                    $charge->getAcquirerTidCapturedAndAutorize();

                $chargeInformation[$key]["cc_tid"] =
                    $charge->getLastTransaction()->getAcquirerTid();

                $chargeInformation[$key]["cc_nsu_authorization"] =
                    $acquirerNsuCapturedAndAutorize['authorized'];

                $chargeInformation[$key]["cc_nsu_capture"] =
                    $acquirerNsuCapturedAndAutorize['captured'];

                continue;
            }

            $chargePostData = $billetTransaction[0]->getPostData();
            if (!empty($chargePostData->nosso_numero)) {
                $chargeInformation[$key]['billet_buyer_our_number'] =
                    $chargePostData->nosso_numero;
            }
        }

        return $chargeInformation;
    }

    /**
     * @param Charge[] $charges
     * @return array
     */
    private static function buildTwoCreditcard(array $charges)
    {
        $nameForTwoCards = ['_first', '_second'];

        $chargeInformation = [];
        foreach ($charges as $key => $charge) {
            $name = $nameForTwoCards[$key];

            $acquirerNsuCapturedAndAutorize =
                $charge->getAcquirerTidCapturedAndAutorize();

            $chargeInformation[$key]["cc_tid{$name}"] =
                $charge->getLastTransaction()->getAcquirerTid();

            $chargeInformation[$key]["cc_nsu_authorization{$name}"] =
                $acquirerNsuCapturedAndAutorize['authorized'];

            $chargeInformation[$key]["cc_nsu_capture{$name}"] =
                $acquirerNsuCapturedAndAutorize['captured'];
        }

        return $chargeInformation;
    }

    /**
     * @param Charge[] $charges
     * @return array
     */
    private static function buildCreditcard(array $charges)
    {
        $chargeInformation = [];
        foreach ($charges as $key => $charge) {
            $acquirerNsuCapturedAndAutorize =
                $charge->getAcquirerTidCapturedAndAutorize();

            $chargeInformation[$key]["cc_tid"] =
                $charge->getLastTransaction()->getAcquirerTid();

            $chargeInformation[$key]["cc_nsu_authorization"] =
                $acquirerNsuCapturedAndAutorize['authorized'];

            $chargeInformation[$key]["cc_nsu_capture"] =
                $acquirerNsuCapturedAndAutorize['captured'];
        }

        return $chargeInformation;
    }

    /**
     * @param Charge[] $charges
     * @return array
     */
    private static function buildBillet(array $charges)
    {
        $chargeInformation = [];
        foreach ($charges as $key => $charge) {
            $chargePostData = $charge->getLastTransaction()->getPostData();

            if (!empty($chargePostData->nosso_numero)) {
                $chargeInformation[$key]['billet_buyer_our_number'] =
                    $chargePostData->nosso_numero;
            }
        }

        return $chargeInformation;
    }
}
