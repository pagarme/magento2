<?php
/**
 * Class InstallmentsByBrandManagements
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Model;

use MundiPagg\MundiPagg\Api\CustomerCreateManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;
use MundiPagg\MundiPagg\Helper\ModuleHelper;
use MundiPagg\MundiPagg\Helper\Logger;

class CustomerCreateManagement implements CustomerCreateManagementInterface
{
    const MODULE_NAME = 'MundiPagg_MundiPagg';
    const NAME_METADATA = 'Magento 2';

    private $session;
    private $customerModel;
    private $config;
    private $customerRepository;
    private $moduleHelper;

    /**
     * @param Session $session
     */
    public function __construct(
        Session $session,
        Customer $customerModel,
        Config $config,
        CustomerRepositoryInterface $customerRepository,
        ModuleHelper $moduleHelper,
        Logger $logger
    )
    {
        $this->setSession($session);
        $this->setCustomerModel($customerModel);
        $this->setConfig($config);
        $this->setModuleHelper($moduleHelper);
        $this->setLogger($logger);
        $this->customerRepository = $customerRepository;
    }

    public function createCustomer($customerJson, $websiteId)
    {
        if (!$websiteId) {
            $websiteId = 1;
        }

        $session = $this->getSession();
        $customerModel = $this->getCustomerModel();
        $customerModel->setWebsiteId($websiteId); 
        $customer = $customerModel->loadByEmail($customerJson['email']);
        $customerDataModel = $customer->getDataModel();

        if ($customerDataModel->getCustomAttribute('customer_id_mundipagg')) {

            $customerIdMundipagg = $customerDataModel->getCustomAttribute('customer_id_mundipagg')->getValue();

            $this->getLogger()->logger('id de retorno do customer' . $customerIdMundipagg);
            return [
                [
                    'customer_id_mundipagg' => $customerIdMundipagg
                ]
            ];
        }

        $customerRequest = $this->createRequest($customerJson, $customer->getId());
        $this->getLogger()->logger($customerRequest->jsonSerialize());
        $result = $this->createSdk()->getCustomers()->createCustomer($customerRequest);

        $this->getLogger()->logger($result->jsonSerialize());
        
        $customerDataModel->setCustomAttribute('customer_id_mundipagg', $result->id);
        $customer = $this->customerRepository->save($customerDataModel);

        return [
            [
                'customer_id_mundipagg' => $result->id
            ]
        ];  
    }

    public function createRequest($customerJson, $idMagento)
    {
        $customerRequest = $this->createCustomerRequest();

        $customerRequest->name     = $customerJson['name'];
        $customerRequest->email    = $customerJson['email'];
        $customerRequest->document = $customerJson['document'];
        $customerRequest->type     = $customerJson['type'];
        $customerRequest->address  = $customerJson['address'];
        $customerRequest->metadata = $customerJson['metadata'];
        $customerRequest->phones   = $customerJson['phones'];
        $customerRequest->code     = $idMagento;
        $customerRequest->gender   = $customerJson['gender'];
        $customerRequest->metadata = [
            'module_name' => self::NAME_METADATA,
            'module_version' => $this->getModuleHelper()->getVersion(self::MODULE_NAME),
        ];

        return $customerRequest;
    }

    public function createSdk()
    {
        return new \MundiAPILib\MundiAPIClient($this->getConfig()->getSecretKey(), '');
    }

    public function createCustomerRequest()
    {
        return new \MundiAPILib\Models\CreateCustomerRequest();
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param mixed $session
     *
     * @return self
     */
    public function setSession($session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerModel()
    {
        return $this->customerModel;
    }

    /**
     * @param mixed $customerModel
     *
     * @return self
     */
    public function setCustomerModel($customerModel)
    {
        $this->customerModel = $customerModel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     *
     * @return self
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getModuleHelper()
    {
        return $this->moduleHelper;
    }

    public function setModuleHelper($moduleHelper)
    {
        $this->moduleHelper = $moduleHelper;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param mixed $logger
     *
     * @return self
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }
}