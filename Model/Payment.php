<?php

namespace MundiPagg\MundiPagg\Model;


class Payment
{
    protected $customer;


    /**
     * return the object with a new customer with new card
     *
     * @param object $object
     * @param string $name of custumer
     * @param string $email of customer
     * @param int $line line the payment if cards ou two cards need the value line 1 or 2 cards
     * @return object array
     */
    public function addCustomersOnMultiPager($object, $name, $email, $line = NULL)
    {
        if (!empty($name) && !empty($email)) {

            if (is_null($line)) {
                $object->payments[0]['customer'] = array(
                    'name' => $name,
                    'email' => $email
                );
            } else {
                if (($line > 0) || $line < 3) {
                    $object->payments[$line - 1]['customer'] = array(
                        'name' => $name,
                        'email' => $email
                    );
                } else {
                    throw new Exception('Line to add customer not informed. Ex: (1 or 2)');
                }
            }
        }
        return $object;
    }

    /**
     * return the object with a new customer when customer have a saved card
     *
     * @param object $order
     * @param object $card
     * @param int $line line the payment if cards ou two cards need the value line 1 or 2 cards
     * @return object array
     */
    public function addCustomerOnPaymentMethodWithSavedCard($order, $card, $line)
    {

        if($order && $card){

            if (($line > 0) || $line < 3) {
                $order->payments[$line - 1]['customer_id'] = $card->getCardId();
            } else {
                throw new Exception('Line to add customer not informed. Ex: (1 or 2)');
            }

        }

        return $order;

    }
}