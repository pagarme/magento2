<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Hub;

use Pagarme\Core\Hub\Repositories\InstallTokenRepository;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Core\Hub\ValueObjects\HubInstallToken;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        Magento2CoreSetup::bootstrap();
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (isset($_GET['authorization_code'])) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Pagarme_Hub.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $request = $objectManager->get('\Magento\Framework\App\RequestInterface');

            $params = $request->getParams();

            $hubIntegrationService = new HubIntegrationService();
            $hubIntegrationService->endHubIntegration(
                $params['&install_token'],
                $params['authorization_code'],
                'https://stg-hubapi.mundipagg.com/auth/apps/access-tokens',
                'https://stg-magento2.mundipagg.com/rest/V1/pagarme/webhook'
            );
        }

        $url = $this->getUrl('adminhtml/system_config/edit/section/payment');
        header('Location: ' . explode('?', $url)[0]);
        exit;
    }
}
