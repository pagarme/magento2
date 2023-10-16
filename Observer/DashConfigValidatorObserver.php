<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Pagarme\Pagarme\Model\Account;

class DashConfigValidatorObserver implements ObserverInterface
{
    /**
     * @var Account
     */
    protected $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function execute(Observer $observer)
    {
        $section = $observer->getRequest()
            ->getParam('section');
        if ($section !== 'payment') {
            return $this;
        }

        $website = $observer->getRequest()
            ->getParam('website', 1);

        $this->account->validateDashSettings($website);
    }
}
