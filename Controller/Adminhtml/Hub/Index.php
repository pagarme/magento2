<?php

namespace Pagarme\Pagarme\Controller\Adminhtml\Hub;

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

            $payload['code'] = $_GET['authorization_code'];
            $payload['webhook_url'] = 'https://stg-magento2.mundipagg.com/rest/V1/pagarme/webhook';

            $url = 'https://stg-hubapi.mundipagg.com/auth/apps/access-tokens'; //STG
            $appkey = '1e4679e9-5886-4d74-8e10-bbca1732b1fb'; //STG

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'PublicAppKey: ' . $appkey]);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $return = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($return, 0, $header_size);
            $body = substr($return, $header_size);
            curl_close($ch);

            $response['url'] = $url;
            $response['appkey'] = $appkey;
            $response['payload'] = json_encode($payload, JSON_PRETTY_PRINT);
            $response['header'] = $header;
            $response['body'] = $body;
            $logger->info("\n" . join("\n", $response) . "\n" . str_repeat('-', 80));

            $body = json_decode($body);

            if (!isset($body->install_id)) {
                die('Error on Hub install: ' . print_r($body, true));
            }

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $configWriter = $objectManager->get('Magento\Framework\App\Config\Storage\WriterInterface');
            $save = $configWriter->save('pagarme_pagarme/hub/install_id', $body->install_id);
            $save = $configWriter->save('pagarme_pagarme/hub/access_token', $body->access_token);
            $cleanCache = $objectManager->get('Magento\Framework\App\Cache\Manager')->clean(['config']);

            $url = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            header('Location: ' . explode('?', $url)[0]);
            exit;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Hub Config"));

        return $resultPage;
    }
}
