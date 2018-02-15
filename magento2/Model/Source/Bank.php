<?php
/**
 * Class PaymentAction
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Model\Source;


use Magento\Framework\Option\ArrayInterface;
use MundiPagg\MundiPagg\Model\Enum\BankEnum;

class Bank implements ArrayInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => BankEnum::BANCO_DO_BRASIL,
                'label' => __('Banco do Brasil'),
            ],
            [
                'value' => BankEnum::BRADESCO,
                'label' => __('Bradesco')
            ],
            [
                'value' => BankEnum::HSBC,
                'label' => __('HSBC')
            ],
            [
                'value' => BankEnum::ITAU,
                'label' => __('Itau')
            ],
            [
                'value' => BankEnum::SANTANDER,
                'label' => __('Santander')
            ],
            [
                'value' => BankEnum::CAIXA,
                'label' => __('Caixa')
            ],
            [
                'value' => BankEnum::STONE,
                'label' => __('Stone')
            ]
        ];
    }

    public function getBankNumber($title)
    {

        switch ($title) {
            case 'Itau':
                return BankEnum::ITAU;
                break;

            case 'Bradesco':
                return BankEnum::BRADESCO;
                break;

            case 'Santander':
                return BankEnum::SANTANDER;
                break;

            case 'BancoDoBrasil':
                return BankEnum::BANCO_DO_BRASIL;
                break;

            case 'Caixa':
                return BankEnum::CAIXA;
                break;

            case 'HSBC':
                return BankEnum::HSBC;
                break;

            case 'Stone':
                return BankEnum::STONE;
                break;

            default:
                return false;

        }
    }
}
