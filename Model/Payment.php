<?php

namespace Pagarme\Pagarme\Model;


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
    public function addCustomersOnMultiPager($object, $customer, $address = NULL, $line = NULL, $active = NULL)
    {
        if (!empty($customer['name']) && !empty($customer['email'])) {


            if (is_null($line)) {

                $addressMuntiPager = $this->addCustomersAddress($address);


                $object->payments[0]['customer'] = array(
                    'name' => $customer['name'],
                    'email' => $customer['email'],
                    'phone' => $customer['phone'],
                    'document' => !empty($customer['document']) ? $customer['document'] : null,
                    'type' => 'individual',
                    'address' => $addressMuntiPager
                );

            } else {

                if (($line > 0) || $line < 3) {

                    $addressMuntiPager = $this->addCustomersAddress($address);

                    $object->payments[$line - 1]['customer'] = array(
                        'name' => $customer['name'],
                        'email' => $customer['email'],
                        'document' => !empty($customer['document']) ? $customer['document'] : null,
                        'type' => 'individual',
                        'address' => $addressMuntiPager
                    );
                } else {
                    throw new Exception('Line to add customer not informed. Ex: (1 or 2)');
                }
            }
        } else {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Name and Email required'));
        }
        return $object;
    }

    /**
     * return the object with address
     *
     * @param object $address is required
     * @param $country Country with you have
     * @return object array
     */

    public function addCustomersAddress($address, $country = NULL)
    {

        if (empty($address['zip_code'])) {
            $address['zip_code'] = null;
        }

        if (is_null($country)) {
            $address['country'] = 'BR';
        }

        $address = array(
            'street' => $address['street'],
            'number' => $address['number'],
            'zip_code' => $address['zip_code'],
            'neighborhood' => $address['neighborhood'],
            'city' => $address['city'],
            'state' => $address['state'],
            'country' => $address['country'],
            'complement' => $address['complement']
        );

        foreach ($address as $key => $value) {

            if ((empty($value) || $value == '') && $key != 'complement') {
                return null;
            }

        }

        return $address;

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

        if ($order && $card) {

            if (($line > 0) || $line < 3) {
                $order->payments[$line - 1]['customer_id'] = $card->getCardId();
            } else {
                throw new Exception('Line to add customer not informed. Ex: (1 or 2)');
            }

        }

        return $order;

    }

    public function addPhonesToCustomer($object, $home_phone = null, $mobile_phone = null, $country_code = 55)
    {

        if (empty($object)) {
            throw new \InvalidArgumentException('Not informed an object, to addPhonesToCustomer.');
        }

        if (!empty($home_phone)) {

            $home_phone = $this->formatPhone($home_phone);

            $object->customer['phones']['home_phone'] = array(
                'country_code' => $country_code,
                'area_code' => $home_phone['ddd'],
                'number' => $home_phone['number']
            );
        }

        if (!empty($mobile_phone)) {

            $mobile_phone = $this->formatPhone($mobile_phone);

            $object->customer['phones']['mobile_phone'] = array(
                'country_code' => $country_code,
                'area_code' => $mobile_phone['ddd'],
                'number' => $mobile_phone['number']
            );
        }
        return $object;
    }

    public function formatPhone($phone)
    {
        $phone = preg_replace("/[^0-9]/", "", $phone);

        return array('ddd' => $phone[0] . $phone[1], 'number' => substr($phone, 2));
    }
}
