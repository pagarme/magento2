<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Pagarme\Controller\Adminhtml\Hub\Index as HubControllerIndex;
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
     * @var HubControllerIndex
     */
    protected $hubControllerIndex;

    /**
     * @param Account $account
     * @param StoreManagerInterface $storeManager
     * @param HubControllerIndex $hubControllerIndex
     */
    public function __construct(
        Account               $account,
        StoreManagerInterface $storeManager,
        HubControllerIndex    $hubControllerIndex
    )
    {
        $this->account = $account;
        $this->storeManager = $storeManager;
        $this->hubControllerIndex = $hubControllerIndex;
    }

    /**
     * @param Observer $observer
     * @return $this
     * @throws NoSuchEntityException|LocalizedException
     */
    public function execute(Observer $observer)
    {
        $section = $observer->getRequest()->getParam('section');
        if ($section !== 'payment') {
            return $this;
        }

        $scopeUrl = $this->hubControllerIndex->getScopeUrl() ?? 'default';
        $websiteCode = $this->hubControllerIndex->getScopeUrl() ? $this->storeManager->getWebsite()->getId() : 0;
        $website = $observer->getRequest()->getParam(
            $scopeUrl,
            $websiteCode
        );

        $this->account->validateDashSettings($website);
        return $this;
    }
}
