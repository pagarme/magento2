<?php
/**
 * Class PaymentAction
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model\Source;


use Magento\Framework\Option\ArrayInterface;
use Pagarme\Pagarme\Model\Enum\BankEnum;

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
