<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Pagarme\Model\Account;

class DashConfigValidatorObserver implements ObserverInterface
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Account $account
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Account $account,
        StoreManagerInterface $storeManager
    ) {
        $this->account = $account;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $section = $observer->getRequest()
            ->getParam('section');
        if ($section !== 'payment') {
            return $this;
        }

        $website = $observer->getRequest()
            ->getParam('website', $this->storeManager->getStore()->getWebsiteId());

        $this->account->validateDashSettings($website);
        return $this;
    }
}
