<?php

namespace Pagarme\Pagarme\Test\Unit\Model;
/**
 * Stub class for Configuration to enable testing with final classes
 *
 * This stub bypasses the final restriction by using composition instead of inheritance.
 * It provides a simple interface for testing without complex mocking of final classes.
 */
class ConfigurationStub
{
    private $paymentProfileId;
    private $poiType;
    private $accountId;
    private $merchantId;

    /**
     * @param string|null $paymentProfileId
     * @param array|null $poiType
     * @param string|null $accountId
     * @param string|null $merchantId
     */
    public function __construct($paymentProfileId = null, $poiType = null, $accountId = null, $merchantId = null)
    {
        $this->paymentProfileId = $paymentProfileId;
        $this->poiType = $poiType;
        $this->accountId = $accountId;
        $this->merchantId = $merchantId;
    }

    /**
     * @return string|null
     */
    public function getPaymentProfileId()
    {
        return $this->paymentProfileId;
    }

    /**
     * @param string|null $paymentProfileId
     * @return void
     */
    public function setPaymentProfileId($paymentProfileId)
    {
        $this->paymentProfileId = $paymentProfileId;
    }

    /**
     * @return array|null
     */
    public function getPoiType()
    {
        return $this->poiType;
    }

    /**
     * @param array|null $poiType
     * @return void
     */
    public function setPoiType($poiType)
    {
        $this->poiType = $poiType;
    }

    /**
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param string|null $accountId
     * @return void
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }

    /**
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string|null $merchantId
     * @return void
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * Factory method to create a stub with specific values
     *
     * @param string|null $paymentProfileId
     * @param array|null $poiType
     * @return ConfigurationStub
     */
    public static function create($paymentProfileId = null, $poiType = null)
    {
        return new self($paymentProfileId, $poiType);
    }
}

