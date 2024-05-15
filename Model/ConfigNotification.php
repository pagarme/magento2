<?php
namespace Pagarme\Pagarme\Model;

use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Core\Kernel\Services\LogService;
use Magento\Framework\App\ObjectManager;

class ConfigNotification
{    
    public function addNotify($error)
    {
        $objectManager = ObjectManager::getInstance();
        $messageManager = $objectManager->get("\Magento\AdminNotification\Model\Inbox");
        $lastNotify = $messageManager->loadLatestNotice();
        if ($lastNotify->getDescription() != $error->getMessage()){
            $messageManager->addCritical("Pagar.me - Há configurações incorretas", $error->getMessage());
            $this->addLog($error);
        }
    }

    private function addLog($logMessage)
    {
        // $logService = new LogService(
        //     'Config',
        //     true
        // );

        // $logService->exception($logMessage);
    }

}
    