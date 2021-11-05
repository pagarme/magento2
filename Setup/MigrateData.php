<?php

namespace Pagarme\Pagarme\Setup;

require_once "app/bootstrap.php";

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$resourceConnection = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resourceConnection->getConnection();


$logger = new \Monolog\Logger('migration');
$logger->pushHandler(new \Monolog\Handler\StreamHandler(BP .'/var/log/Pagarme_Migration.log'));

if (isset($argv)) {
    $m = new MigrateData($connection, $logger, $argv);
    $m->run();
}

class MigrateData
{
    public function __construct($connection, $logger, $argv)
    {
        $this->connection = $connection;
        $this->logger = $logger;
        $this->options = $this->getOptions($argv);
        $this->start = microtime(true);
    }

    public function run()
    {
        echo 'Wait...' . "\n";
        try {
            $options = json_encode($this->options);
            $this->log[] = "\n" . $this->fillLineWithDashes('MigrateData, options: ' . $options . ' ');
            if ($this->validate()) {
                $this->populateQueries();
                $this->getRowsCountUnmigrated();
                $this->migrateData();
            }
        } catch (\Throwable $exception) {

        } finally {
            $timeElapsed = $this->calculateTimeElapsed($this->start);
            $this->log[] = "\n" . $this->fillLineWithDashes('Finished in ' . $timeElapsed . ' ');
            $logs = join("\n", $this->log);
            $this->logger->info("\n" . $logs);
            echo "\n" . $logs . "\n";
            if (isset($exception)) {
                $this->logger->error($exception . "\n\n");
                $line = $this->fillLineWithDashes('-');
                $errorMessage = $exception->getMessage();
                echo "\n" . $line . "\n\e[0;31;43mERROR:\e[0m " . $errorMessage . "\n" . $line . "\n";
            }
            echo "\n";
        }
    }

    private function validate()
    {
        $sql = '
            SELECT COUNT(1) AS qtd
            FROM   information_schema.tables
            WHERE  table_schema = (SELECT DATABASE()
                                   FROM   DUAL)
                   AND table_name = "mundipagg_module_core_customer"
            LIMIT  1
        ';
        $result = $this->connection->fetchRow($sql);
        if ($result['qtd'] > 0) {
            return true;
        }
        $this->log[] = "\e[0;92mNo data to migrate.\e[0m";
        return false;
    }

    private function getRowsCountUnmigrated()
    {
        $this->log[] = "\n## \e[0;92mGetting rows count from tables\e[0m";
        foreach ($this->queries as $table => $query) {
            $sql = 'SELECT COUNT(1) as qtd ' . substr($query['sel'], strpos($query['sel'], 'FROM'));
            $result = $this->connection->fetchRow($sql);
            $this->log[] = ' - ' . $table . ': ' . number_format($result['qtd'], 0) . ' rows';
        }
    }

    private function migrateData()
    {
        if (!isset($this->options['execute'])) {
            return;
        }
        $this->log[] = "\n## \e[1;33mExecuting migration\e[0m";
        foreach ($this->queries as $table => $query) {
            if ($this->options['group'] !== null && $this->isTableInGroup($table) === false) {
                continue;
            }
            $time = microtime(true);
            $sql = $query['ins'] . ' ' . $query['sel'] . ' ' . $this->options['limit'];
            $result = $this->connection->query($sql);
            $rowCount = number_format($result->rowCount(), 0);
            $this->log[] = ' - ' . $table . ': ' . $rowCount . ' inserts in ' . $this->calculateTimeElapsed($time);
        }
    }

    private function isTableInGroup($table)
    {
        $groups['config'] = ['config_data'];
        $groups['card'] = [
            'core_customer',
            'core_saved_card',
            'mundipagg_cards',
        ];
        $groups['order'] = [
            'core_order',
            'core_charge',
            'mundipagg_charges',
            'core_transaction',
        ];
        $groups['recurrence'] = [
            'core_recurrence_charge',
            'core_recurrence_products_plan',
            'core_recurrence_products_subscription',
            'core_recurrence_subscription',
            'core_recurrence_subscription_items',
            'core_recurrence_subscription_repetitions',
            'core_recurrence_sub_products',
        ];

        if (!isset($groups[$this->options['group']])) {
            throw new \Exception('Group "' . $this->options['group'] . '" invalid for execution!');
        }

        return (array_search($table, $groups[$this->options['group']]) !== false);
    }

    private function getOptions($argv)
    {
        if (!isset($argv[1])) {
            return null;
        }
        $options = ['execute' => true, 'group' => null, 'limit' => null];
        foreach ($argv as $i => $arg) {
            if (strpos($arg, 'group=') !== false) {
                $options['group'] = explode('=', $argv[$i])[1];
            }
            if (strpos($arg, 'limit=') !== false) {
                $options['limit'] = $this->getLimit(explode('=', $argv[$i])[1]);
            }
        }
        return $options;
    }

    private function getLimit($limit)
    {
        $scale = substr($limit, -1);
        $multiplier = 1;
        if (strtoupper($scale) === 'K') {
            $multiplier = 1000;
        }
        if (strtoupper($scale) === 'M') {
            $multiplier = 1000000;
        }
        $limit = intval($limit) * $multiplier;
        return (is_int($limit)) ? 'LIMIT ' . $limit : null;
    }

    private function calculateTimeElapsed($from)
    {
        return number_format(microtime(true) - $from, 3) . ' seconds';
    }

    private function fillLineWithDashes($str)
    {
        return $str . str_repeat('-', 80 - strlen($str));
    }

    private function populateQueries()
    {
        //-- config
        $this->queries['config_data']['ins'] = '
            INSERT INTO core_config_data
                        (scope,
                        scope_id,
                        path,
                        value)
        ';
        $this->queries['config_data']['sel'] = '
            SELECT scope,
                   scope_id,
                   REPLACE(path, "mundipagg", "pagarme"),
                   value
            FROM   core_config_data
            WHERE  path LIKE "%mundipagg%"
                   AND REPLACE(path, "mundipagg", "pagarme") NOT IN (SELECT path
                                                                     FROM   core_config_data
                                                                     WHERE
                       path LIKE "%pagarme%")
        ';

        //-- customer
        $this->queries['core_customer']['ins'] = '
            INSERT INTO pagarme_module_core_customer
                        (code,
                         pagarme_id)
        ';
        $this->queries['core_customer']['sel'] = '
            SELECT code,
                   mundipagg_id
            FROM   mundipagg_module_core_customer
            WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                        FROM   pagarme_module_core_customer)
        ';

        //-- card
        $this->queries['core_saved_card']['ins'] = '
            INSERT INTO pagarme_module_core_saved_card
                        (pagarme_id,
                         owner_id,
                         first_six_digits,
                         last_four_digits,
                         brand,
                         owner_name,
                         created_at)
        ';
        $this->queries['core_saved_card']['sel'] = '
            SELECT mundipagg_id,
                   owner_id,
                   first_six_digits,
                   last_four_digits,
                   brand,
                   owner_name,
                   created_at
            FROM   mundipagg_module_core_saved_card
            WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                        FROM   pagarme_module_core_saved_card)
        ';

        //-- card 2
        $this->queries['mundipagg_cards']['ins'] = '
            INSERT INTO pagarme_pagarme_cards
                        (customer_id,
                         card_token,
                         card_id,
                         last_four_numbers,
                         created_at,
                         updated_at,
                         brand)
        ';
        $this->queries['mundipagg_cards']['sel'] = '
            SELECT customer_id,
                   card_token,
                   card_id,
                   last_four_numbers,
                   created_at,
                   updated_at,
                   brand
            FROM   mundipagg_mundipagg_cards
            WHERE  customer_id NOT IN (SELECT customer_id
                                        FROM   pagarme_pagarme_cards)
        ';

        // -- order
        $this->queries['core_order']['ins'] = '
            INSERT INTO pagarme_module_core_order
                        (pagarme_id,
                         code,
                         status)
        ';
        $this->queries['core_order']['sel'] = '
            SELECT mundipagg_id,
                   code,
                   status
            FROM   mundipagg_module_core_order
            WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                        FROM   pagarme_module_core_order)
        ';

        // -- charge
        $this->queries['core_charge']['ins'] = '
            INSERT INTO pagarme_module_core_charge
                        (pagarme_id,
                         order_id,
                         code,
                         amount,
                         paid_amount,
                         canceled_amount,
                         refunded_amount,
                         status,
                         metadata,
                         customer_id)
        ';
        $this->queries['core_charge']['sel'] = '
            SELECT mundipagg_id,
                   order_id,
                   code,
                   amount,
                   paid_amount,
                   canceled_amount,
                   refunded_amount,
                   status,
                   metadata,
                   customer_id
            FROM   mundipagg_module_core_charge
            WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                        FROM   pagarme_module_core_charge)
        ';

        // -- charge 2
        $this->queries['mundipagg_charges']['ins'] = '
            INSERT INTO pagarme_pagarme_charges
                        (charge_id,
                         code,
                         order_id,
                         type,
                         status,
                         amount,
                         paid_amount,
                         refunded_amount)
        ';
        $this->queries['mundipagg_charges']['sel'] = '
            SELECT charge_id,
                   code,
                   order_id,
                   type,
                   status,
                   amount,
                   paid_amount,
                   refunded_amount
            FROM   mundipagg_mundipagg_charges
            WHERE  charge_id NOT IN (SELECT charge_id
                                    FROM   pagarme_pagarme_charges)
        ';

        // -- transaction
        $this->queries['core_transaction']['ins'] = '
            INSERT INTO pagarme_module_core_transaction
                        (pagarme_id,
                         charge_id,
                         amount,
                         paid_amount,
                         acquirer_tid,
                         acquirer_nsu,
                         acquirer_auth_code,
                         acquirer_name,
                         acquirer_message,
                         type,
                         status,
                         created_at,
                         boleto_url,
                         card_data,
                         transaction_data)
        ';
        $this->queries['core_transaction']['sel'] = '
            SELECT mundipagg_id,
                   charge_id,
                   amount,
                   paid_amount,
                   acquirer_tid,
                   acquirer_nsu,
                   acquirer_auth_code,
                   acquirer_name,
                   acquirer_message,
                   type,
                   status,
                   created_at,
                   boleto_url,
                   REPLACE(card_data, "mundipagg", "pagarme"),
                   transaction_data
            FROM   mundipagg_module_core_transaction
            WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                        FROM   pagarme_module_core_transaction)
        ';

        // -- webhook
        $this->queries['core_webhook']['ins'] = '
            INSERT INTO pagarme_module_core_webhook
                        (pagarme_id,
                         handled_at)
        ';
        $this->queries['core_webhook']['sel'] = '
            SELECT mundipagg_id,
                   handled_at
            FROM   mundipagg_module_core_webhook
            WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                        FROM   pagarme_module_core_webhook)
        ';

        // -- recurrence_charge
        $this->queries['core_recurrence_charge']['ins'] = '
            INSERT INTO pagarme_module_core_recurrence_charge
                        (pagarme_id,
                         subscription_id,
                         code,
                         amount,
                         paid_amount,
                         canceled_amount,
                         refunded_amount,
                         status,
                         metadata,
                         invoice_id,
                         payment_method,
                         boleto_link,
                         cycle_start,
                         cycle_end,
                         created_at,
                         updated_at)
        ';
        $this->queries['core_recurrence_charge']['sel'] = '
            SELECT mundipagg_id,
                   subscription_id,
                   code,
                   amount,
                   paid_amount,
                   canceled_amount,
                   refunded_amount,
                   status,
                   metadata,
                   invoice_id,
                   payment_method,
                   boleto_link,
                   cycle_start,
                   cycle_end,
                   created_at,
                   updated_at
                   FROM   mundipagg_module_core_recurrence_charge
                   WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                               FROM   pagarme_module_core_recurrence_charge)
        ';

        // -- recurrence_products_plan
        $this->queries['core_recurrence_products_plan']['ins'] = '
            INSERT INTO pagarme_module_core_recurrence_products_plan
                        (interval_type,
                         interval_count,
                         name,
                         description,
                         plan_id,
                         product_id,
                         credit_card,
                         installments,
                         boleto,
                         billing_type,
                         status,
                         trial_period_days,
                         created_at,
                         updated_at)
        ';
        $this->queries['core_recurrence_products_plan']['sel'] = '
            SELECT interval_type,
                   interval_count,
                   name,
                   description,
                   plan_id,
                   product_id,
                   credit_card,
                   installments,
                   boleto,
                   billing_type,
                   status,
                   trial_period_days,
                   created_at,
                   updated_at
            FROM   mundipagg_module_core_recurrence_products_plan
            WHERE  product_id NOT IN (SELECT product_id
                                      FROM   pagarme_module_core_recurrence_products_plan)
        ';

        // -- recurrence_products_subscription
        $this->queries['core_recurrence_products_subscription']['ins'] = '
            INSERT INTO pagarme_module_core_recurrence_products_subscription
                        (product_id,
                         credit_card,
                         allow_installments,
                         boleto,
                         sell_as_normal_product,
                         billing_type,
                         created_at,
                         updated_at)
        ';
        $this->queries['core_recurrence_products_subscription']['sel'] = '
            SELECT product_id,
                   credit_card,
                   allow_installments,
                   boleto,
                   sell_as_normal_product,
                   billing_type,
                   created_at,
                   updated_at
            FROM   mundipagg_module_core_recurrence_products_subscription
            WHERE  product_id NOT IN (SELECT product_id
                                      FROM
                   pagarme_module_core_recurrence_products_subscription)
        ';

        // -- recurrence_subscription
        $this->queries['core_recurrence_subscription']['ins'] = '
            INSERT INTO pagarme_module_core_recurrence_subscription
                        (pagarme_id,
                         customer_id,
                         code,
                         status,
                         installments,
                         payment_method,
                         recurrence_type,
                         interval_type,
                         interval_count,
                         plan_id,
                         created_at,
                         updated_at)
        ';
        $this->queries['core_recurrence_subscription']['sel'] = '
            SELECT mundipagg_id,
                   customer_id,
                   code,
                   status,
                   installments,
                   payment_method,
                   recurrence_type,
                   interval_type,
                   interval_count,
                   plan_id,
                   created_at,
                   updated_at
            FROM   mundipagg_module_core_recurrence_subscription
            WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                        FROM   pagarme_module_core_recurrence_subscription)
        ';

        // -- recurrence_subscription_items
        $this->queries['core_recurrence_subscription_items']['ins'] = '
            INSERT INTO pagarme_module_core_recurrence_subscription_items
                        (pagarme_id,
                         subscription_id,
                         code,
                         quantity,
                         created_at,
                         updated_at)
        ';
        $this->queries['core_recurrence_subscription_items']['sel'] = '
            SELECT mundipagg_id,
                   subscription_id,
                   code,
                   quantity,
                   created_at,
                   updated_at
            FROM   mundipagg_module_core_recurrence_subscription_items
            WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                        FROM
                   pagarme_module_core_recurrence_subscription_items)';

        // -- recurrence_subscription_repetitions
        $this->queries['core_recurrence_subscription_repetitions']['ins'] = '
            INSERT INTO pagarme_module_core_recurrence_subscription_repetitions
                        (subscription_id,
                         `interval`,
                         interval_count,
                         recurrence_price,
                         cycles,
                         created_at,
                         updated_at)
        ';
        $this->queries['core_recurrence_subscription_repetitions']['sel'] = '
            SELECT subscription_id,
                   `interval`,
                   interval_count,
                   recurrence_price,
                   cycles,
                   created_at,
                   updated_at
            FROM   mundipagg_module_core_recurrence_subscription_repetitions
            WHERE  subscription_id NOT IN (SELECT subscription_id
                                           FROM
                   pagarme_module_core_recurrence_subscription_repetitions)';

        // -- recurrence_sub_products
        $this->queries['core_recurrence_sub_products']['ins'] = '
            INSERT INTO pagarme_module_core_recurrence_sub_products
                        (pagarme_id,
                         product_id,
                         product_recurrence_id,
                         recurrence_type,
                         cycles,
                         quantity,
                         trial_period_days,
                         created_at,
                         updated_at)
        ';
        $this->queries['core_recurrence_sub_products']['sel'] = '
            SELECT mundipagg_id,
                   product_id,
                   product_recurrence_id,
                   recurrence_type,
                   cycles,
                   quantity,
                   trial_period_days,
                   created_at,
                   updated_at
            FROM   mundipagg_module_core_recurrence_sub_products
            WHERE  mundipagg_id NOT IN (SELECT pagarme_id
                                        FROM   pagarme_module_core_recurrence_sub_products)
        ';

    }
}
