<?php
/**
 * Class Builder
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model\Installments;


use Magento\Framework\Api\AbstractSimpleObjectBuilder;
use Magento\Framework\Api\ObjectFactory;
use Pagarme\Pagarme\Api\Data\InstallmentInterface;
use Pagarme\Pagarme\Api\Data\InstallmentInterfaceFactory;
use Pagarme\Pagarme\Model\Installments\Config\ConfigByBrandInterface;
use Magento\Checkout\Model\Session;

class BuilderByBrand extends AbstractSimpleObjectBuilder
{
    protected $config;
    protected $installmentFactory;
    protected $session;
    protected $grandTotal;
    protected $installmentsNumber;

    /**
     * @param InstallmentInterfaceFactory $installmentFactory
     * @param ConfigByBrandInterface $config
     * @param Session $session
     * @param ObjectFactory $objectFactory
     */
    public function __construct(
        InstallmentInterfaceFactory $installmentFactory,
        ConfigByBrandInterface $config,
        Session $session,
        ObjectFactory $objectFactory
    )
    {
        parent::__construct($objectFactory);
        $this->setInstallmentFactory($installmentFactory);
        $this->setConfig($config);
        $this->setSession($session);
    }

    /**
     * @return $this
     */
    public function create()
    {

        $installmentsActive = $this->getConfig()->isActive();

        if($installmentsActive){
            if($this->getConfig()->getInstallmentUnique()){
                $this->session->setCardBrand('');
            }
        }else{
            $this->session->setCardBrand('');
            $this->addInstallment(1);
            return $this;
        }

        $installmentItems = $this->getInstallmentsNumber();

        if($installmentItems > 1){

            for ($i = 1; $i < $installmentItems; $i++) {
                if (!$this->canProcessInstallment($i)) {
                    break;
                }
                $this->addInstallment($i);
            }

        }else{
            $this->addInstallment(1);
        }



        return $this;
    }

    /**
     * @param int $qty
     * @return $this
     */
    protected function addInstallment($qty)
    {

        $installmentAmount = $this->getGrandTotal() / $qty;
        $interest = false;
        $interestLabel = __('without interest');
        $installment = $this->getNewInstallmentInstance();
        $interestRateTotalSend = 0;

        if ($this->getConfig()->isInterestByIssuer() && ($qty > $this->getConfig()->getinstallmentsMaxWithoutInterest())) {
            $interestRate = $this->calcInterestRate($qty);
            $installmentAmount = $this->calcPriceWithInterest($qty, $interestRate);
            $interest = true;
            $interestRateTotal = $interestRate * 100;
            $labelInterestRate = ' ' . $interestRateTotal . '% a.m. ';
            $interestRateTotalSend = ($this->calcPriceWithInterestNoFormated($qty, $interestRate) * $qty) - $this->getGrandTotal();
            $interestLabel = __('with interest') . $labelInterestRate;
        }

        $grandTotal = $installmentAmount * $qty;


        $installment->setQty($qty);
        $installment->setPrice($installmentAmount);
        $installment->setHasInterest($interest);
        $installment->setGrandTotal($grandTotal);
        $installment->setInterest($interestRateTotalSend);
        $installment->setLabel($installment->getQty() . 'x ' . $installment->getPrice(true, false) . ' ' . $interestLabel . ' (Total ' .$installment->getGrandTotal(true, false) . ') ' );

        $this->data[] = $installment;
        return $this;
    }

    /**
     * @param int $qty
     * @return string
     */
    protected function calcPriceWithInterest($qty, $interestRate)
    {
        $price = ( $this->getGrandTotal() * (1 + $interestRate) ) / $qty ;

        return sprintf("%01.2f", $price);
    }

    /**
     * @param int $qty
     * @return string
     */
    protected function calcPriceWithInterestNoFormated($qty, $interestRate)
    {
        $price = ( $this->getGrandTotal() * (1 + $interestRate) ) / $qty ;

        return $price;
    }

    /**
     * @param int $qty
     * @return int
     */
    protected function calcInterestRate($qty)
    {
        $interestRate = $this->getConfig()->getInterestRate();
        $installmentsMaxWithoutInterest = $this->getConfig()->getinstallmentsMaxWithoutInterest();
        $diff = $qty - $installmentsMaxWithoutInterest;
        if ($diff > 1) {
            $interestRateIncremental = $this->getConfig()->getInterestRateIncremental();
            $interestRate = ( ($diff - 1) * $interestRateIncremental) + $interestRate;
        }

        return $interestRate;
    }

    /**
     * @return InstallmentInterface
     */
    protected function getNewInstallmentInstance()
    {
        return $this->getInstallmentFactory()->create();
    }

    /**
     * @param int $i
     * @return bool
     */
    protected function canProcessInstallment($i)
    {
        $installmentAmount = $this->getGrandTotal() / $i;
        return !($i > 1 && $installmentAmount < $this->getConfig()->getInstallmentMinAmount());
    }

    /**
     * @return int
     */
    protected function getInstallmentsNumber()
    {
        if (! $this->installmentsNumber) {
            $this->installmentsNumber = (int) $this->getConfig()->getInstallmentsNumber();
            $this->installmentsNumber++;
        }

        return $this->installmentsNumber;
    }

    /**
     * @return float
     */
    protected function getGrandTotal()
    {
        if (!$this->grandTotal) {
            $this->grandTotal = $this->getSession()->getQuote()->getGrandTotal();
        }
        return $this->grandTotal;
    }

    /**
     * @return ConfigByBrandInterface
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ConfigByBrandInterface $config
     * @return $this
     */
    protected function setConfig(ConfigByBrandInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return InstallmentInterfaceFactory
     */
    protected function getInstallmentFactory()
    {
        return $this->installmentFactory;
    }

    /**
     * @param InstallmentInterfaceFactory $installmentFactory
     * @return $this
     */
    protected function setInstallmentFactory(InstallmentInterfaceFactory $installmentFactory)
    {
        $this->installmentFactory = $installmentFactory;
        return $this;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     * @return $this
     */
    protected function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }
}
