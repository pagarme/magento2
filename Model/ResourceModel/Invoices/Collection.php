<?php

namespace MundiPagg\MundiPagg\Model\ResourceModel\Invoices;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\App\Request\Http;

class Collection extends SearchResult
{
    protected $request;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable,
        $resourceModel,
        $identifierName = null,
        $connectionName = null,
        Http $request
    ) {
        $this->request = $request;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName,
            $connectionName
        );
    }

    protected function _renderFiltersBefore()
    {
        $subscriptionId = $this->request->getParam('subscription_id');

        $joinTable = $this->getTable('mundipagg_module_core_transaction');

        $this->getSelect()
            ->joinLeft(
                $joinTable,
                'main_table.mundipagg_id = mundipagg_module_core_transaction.charge_id ',
                [
                    'id' => new \Zend_Db_Expr('GROUP_CONCAT(main_table.id)'),
                    'tran_id' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.id)'),
                    'tran_mundipagg_id' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.mundipagg_id)'),
                    'tran_charge_id' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.charge_id)'),
                    'tran_amount' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.amount)'),
                    'tran_paid_amount' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.paid_amount)'),
                    'tran_acquirer_name' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.acquirer_name)'),
                    'tran_acquirer_message' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.acquirer_message)'),
                    'tran_acquirer_nsu' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.acquirer_nsu)'),
                    'tran_acquirer_tid' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.acquirer_tid)'),
                    'tran_acquirer_auth_code' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.acquirer_auth_code)'),
                    'tran_type' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.type)'),
                    'tran_status' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.status)'),
                    'tran_created_at' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.created_at)'),
                    'tran_boleto_url' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.boleto_url)'),
                    'tran_card_data' => new \Zend_Db_Expr('GROUP_CONCAT(mundipagg_module_core_transaction.card_data SEPARATOR "---")')
                ]
            )
            ->where("main_table.subscription_id = ?", $subscriptionId)
            ->group('main_table.id');

        parent::_renderFiltersBefore();
    }
}
