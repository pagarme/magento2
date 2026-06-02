-- =============================================================================
-- PAGARME DEV MOCK SEED  —  docker/seed.sql
-- Executado por: make seed
-- Todos os INSERTs usam IGNORE — idempotentes, seguros para re-execução.
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Magento: customer_entity
-- entity_id=99 é o cliente de dev fixo neste ambiente
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `customer_entity`
    (`entity_id`, `website_id`, `email`, `group_id`, `increment_id`,
     `is_active`, `firstname`, `lastname`, `created_at`, `updated_at`)
VALUES
    (99, 1, 'dev@example.com', 1, '000000099', 1, 'Dev', 'Test', NOW(), NOW());

-- -----------------------------------------------------------------------------
-- Pagar.me: customer
-- code = Magento customer entity_id (varchar 100)
-- pagarme_id = ID da API Pagar.me (varchar 20, prefixo 'cus_')
-- 'cus_devtest0000001' = 18 chars  ✓ cabe em varchar(20)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `pagarme_module_core_customer`
    (`code`, `pagarme_id`)
VALUES
    ('99', 'cus_devtest0000001');

-- -----------------------------------------------------------------------------
-- Pagar.me: saved_card
-- pagarme_id = varchar(21), prefixo 'card_'  → 'card_devtest000001' = 18 chars ✓
-- owner_id   = varchar(21), é o pagarme_id do customer acima (cus_…), NÃO o entity_id do Magento
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `pagarme_module_core_saved_card`
    (`type`, `pagarme_id`, `owner_id`,
     `first_six_digits`, `last_four_digits`, `brand`, `owner_name`)
VALUES
    ('credit_card', 'card_devtest000001', 'cus_devtest0000001', '411111', '1111', 'Visa',       'Dev Test'),
    ('credit_card', 'card_devtest000002', 'cus_devtest0000001', '555555', '4444', 'Mastercard', 'Dev Test');

-- -----------------------------------------------------------------------------
-- Pagar.me: recurrence_products_plan
-- plan_id = varchar(21), prefixo 'plan_' → 'plan_devtest000001' = 18 chars ✓
-- billing_type: varchar(11) → 'prepaid' | 'postpaid' | 'exact_day'
-- status:       varchar(11) → 'active' | 'inactive' | 'deleted'
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `pagarme_module_core_recurrence_products_plan`
    (`interval_type`, `interval_count`, `name`, `description`, `plan_id`,
     `product_id`, `credit_card`, `installments`, `boleto`,
     `billing_type`, `status`, `trial_period_days`, `apply_discount_in_all_product_cycles`)
VALUES
    ('month', 1,
     'Assinatura Mensal Dev', 'Plano mensal para testes locais',
     'plan_devtest000001',
     991, '1', '0', '0', 'prepaid', 'active', NULL, 0),
    ('year', 1,
     'Assinatura Anual Dev', 'Plano anual com trial de 7 dias',
     'plan_devtest000002',
     992, '1', '0', '0', 'prepaid', 'active', '7', 0);

-- -----------------------------------------------------------------------------
-- Pagar.me: recurrence_products_subscription
-- flags credit_card/boleto/allow_installments/sell_as_normal_product = varchar(1)
-- billing_type = varchar(11)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `pagarme_module_core_recurrence_products_subscription`
    (`product_id`, `credit_card`, `allow_installments`, `boleto`,
     `sell_as_normal_product`, `billing_type`)
VALUES
    (991, '1', '1', '0', '0', 'prepaid'),
    (992, '1', '0', '1', '0', 'postpaid');

-- -----------------------------------------------------------------------------
-- Pagar.me: recurrence_subscription_repetitions
-- recurrence_price = xsi:type="int" → CENTAVOS (inteiro)
--   R$ 99,90  = 9990 centavos
--   R$ 990,00 = 99000 centavos
-- interval = varchar(15)
-- subscription_id referencia o id auto-increment da tabela acima (1 e 2)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `pagarme_module_core_recurrence_subscription_repetitions`
    (`subscription_id`, `interval`, `interval_count`, `recurrence_price`,
     `cycles`, `apply_discount_in_all_product_cycles`)
VALUES
    (1, 'month', 1,  9990,  NULL, 0),
    (2, 'month', 1,  9990,  6,    1),
    (2, 'year',  1,  99000, 1,    0);

-- -----------------------------------------------------------------------------
-- Pagar.me: recipients (Marketplace)
-- pagarme_id = varchar(255), prefixo 'rp_' conforme comentário do schema
-- document_type: 'individual' (CPF) | 'corporation' (CNPJ)  max 11 chars ✓
-- external_id = Webkul vendor entity_id (int)
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `pagarme_module_core_recipients`
    (`external_id`, `pagarme_id`, `document_type`, `document`,
     `name`, `email`, `status`)
VALUES
    (1, 'rp_devtest00000001', 'individual',  '000.000.000-00',        'Dev Seller Test',  'seller@example.com',  'active'),
    (2, 'rp_devtest00000002', 'corporation', '00.000.000/0001-00',    'Dev Company LTDA', 'company@example.com', 'active');

-- -----------------------------------------------------------------------------
-- Magento: catálogo simplificado (produtos para os pedidos mock)
-- entity_id=991/992/993 são reservados para dev neste ambiente
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `catalog_product_entity`
    (`entity_id`, `attribute_set_id`, `type_id`, `sku`,
     `has_options`, `required_options`, `created_at`, `updated_at`)
VALUES
    (991, 4, 'simple', 'DEV-MOCK-SKU-001', 0, 0, NOW(), NOW()),
    (992, 4, 'simple', 'DEV-MOCK-SKU-002', 0, 0, NOW(), NOW()),
    (993, 4, 'simple', 'DEV-MOCK-SKU-003', 0, 0, NOW(), NOW());

INSERT INTO `cataloginventory_stock_item`
    (`product_id`, `stock_id`, `qty`, `is_in_stock`, `manage_stock`, `use_config_manage_stock`)
VALUES
    (991, 1, 100.0000, 1, 1, 1),
    (992, 1, 100.0000, 1, 1, 1),
    (993, 1, 100.0000, 1, 1, 1)
ON DUPLICATE KEY UPDATE `qty` = 100.0000, `is_in_stock` = 1;

-- -----------------------------------------------------------------------------
-- Magento: pedidos mock
-- entity_id=9991/9992 são reservados para dev neste ambiente
-- grand_total/base_grand_total = DECIMAL em BRL (float), como o Magento armazena
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `sales_order`
    (`entity_id`, `state`, `status`, `increment_id`, `protect_code`,
     `store_id`, `store_name`,
     `customer_id`, `customer_email`, `customer_firstname`, `customer_lastname`, `customer_is_guest`,
     `base_currency_code`, `order_currency_code`, `store_currency_code`, `global_currency_code`,
     `base_to_global_rate`, `base_to_order_rate`,
     `base_grand_total`, `grand_total`, `base_subtotal`, `subtotal`,
     `base_tax_amount`, `tax_amount`, `base_shipping_amount`, `shipping_amount`,
     `base_discount_amount`, `discount_amount`,
     `base_total_paid`, `total_paid`,
     `total_qty_ordered`, `total_item_count`, `is_virtual`,
     `billing_address_id`, `shipping_address_id`,
     `created_at`, `updated_at`)
VALUES
    (9991, 'processing', 'processing', 'DEV9999900001', LEFT(MD5('DEV9999900001'), 10),
     1, 'Main Website\nMain Website Store\nDefault Store View',
     99, 'dev@example.com', 'Dev', 'Test', 0,
     'BRL', 'BRL', 'BRL', 'BRL', 1.0, 1.0,
     189.90, 189.90, 189.90, 189.90,
     0, 0, 0, 0, 0, 0,
     189.90, 189.90,
     1, 1, 0,
     99101, 99102,
     NOW(), NOW()),
    (9992, 'pending_payment', 'pending_payment', 'DEV9999900002', LEFT(MD5('DEV9999900002'), 10),
     1, 'Main Website\nMain Website Store\nDefault Store View',
     99, 'dev@example.com', 'Dev', 'Test', 0,
     'BRL', 'BRL', 'BRL', 'BRL', 1.0, 1.0,
     49.90, 49.90, 49.90, 49.90,
     0, 0, 0, 0, 0, 0,
     NULL, NULL,
     1, 1, 0,
     99201, 99202,
     NOW(), NOW());

-- Endereços de cobrança e entrega (entity_id fixos para referenciar acima)
-- sales_order_address não tem region_code — usa region (text) + region_id (int)
INSERT IGNORE INTO `sales_order_address`
    (`entity_id`, `parent_id`, `address_type`,
     `email`, `firstname`, `lastname`,
     `street`, `city`, `region`, `postcode`, `country_id`, `telephone`)
VALUES
    (99101, 9991, 'billing',  'dev@example.com', 'Dev', 'Test', 'Rua Teste Dev, 100', 'São Paulo', 'São Paulo', '01310100', 'BR', '11999990000'),
    (99102, 9991, 'shipping', 'dev@example.com', 'Dev', 'Test', 'Rua Teste Dev, 100', 'São Paulo', 'São Paulo', '01310100', 'BR', '11999990000'),
    (99201, 9992, 'billing',  'dev@example.com', 'Dev', 'Test', 'Rua Teste Dev, 100', 'São Paulo', 'São Paulo', '01310100', 'BR', '11999990000'),
    (99202, 9992, 'shipping', 'dev@example.com', 'Dev', 'Test', 'Rua Teste Dev, 100', 'São Paulo', 'São Paulo', '01310100', 'BR', '11999990000');

INSERT IGNORE INTO `sales_order_payment`
    (`entity_id`, `parent_id`, `method`,
     `base_amount_ordered`, `amount_ordered`,
     `base_amount_paid`, `amount_paid`,
     `additional_information`)
VALUES
    (9991, 9991, 'pagarme_creditcard',
     189.90, 189.90, 189.90, 189.90,
     '{"pagarme_transaction_id":"or_devtest00000001","pagarme_charge_id":"ch_devtest00000001","brand":"Visa","installments":2}'),
    (9992, 9992, 'pagarme_pix',
     49.90, 49.90, 0, 0,
     '{"pagarme_transaction_id":"or_devtest00000002","pagarme_charge_id":"ch_devtest00000002","qr_code_url":"https://api.pagar.me/core/v5/transactions/or_devtest00000002/qrcode","expires_at":"2099-12-31T23:59:59Z"}');

INSERT IGNORE INTO `sales_order_item`
    (`item_id`, `order_id`, `store_id`,
     `product_type`, `product_options`, `name`, `sku`,
     `qty_ordered`, `qty_canceled`, `qty_invoiced`, `qty_shipped`, `qty_refunded`,
     `price`, `base_price`, `row_total`, `base_row_total`,
     `tax_amount`, `base_tax_amount`, `discount_amount`,
     `is_virtual`, `created_at`, `updated_at`)
VALUES
    (9991, 9991, 1, 'simple', 'a:0:{}', 'Produto Mock Dev (Cartão)', 'DEV-MOCK-SKU-001',
     1, 0, 1, 0, 0,
     189.90, 189.90, 189.90, 189.90, 0, 0, 0, 0, NOW(), NOW()),
    (9992, 9992, 1, 'simple', 'a:0:{}', 'Produto Mock Dev (Pix)',    'DEV-MOCK-SKU-002',
     1, 0, 0, 0, 0,
     49.90, 49.90, 49.90, 49.90, 0, 0, 0, 0, NOW(), NOW());

INSERT IGNORE INTO `sales_order_grid`
    (`entity_id`, `status`, `store_id`, `store_name`,
     `customer_id`, `customer_email`, `customer_name`,
     `base_grand_total`, `grand_total`, `base_total_paid`, `total_paid`,
     `increment_id`, `base_currency_code`, `order_currency_code`,
     `billing_name`, `shipping_name`,
     `billing_address`, `shipping_address`,
     `payment_method`, `created_at`, `updated_at`)
VALUES
    (9991, 'processing', 1, 'Default Store View',
     99, 'dev@example.com', 'Dev Test',
     189.90, 189.90, 189.90, 189.90,
     'DEV9999900001', 'BRL', 'BRL',
     'Dev Test', 'Dev Test',
     'Dev Test, Rua Teste Dev 100, São Paulo, SP, 01310100, BR',
     'Dev Test, Rua Teste Dev 100, São Paulo, SP, 01310100, BR',
     'pagarme_creditcard', NOW(), NOW()),
    (9992, 'pending_payment', 1, 'Default Store View',
     99, 'dev@example.com', 'Dev Test',
     49.90, 49.90, NULL, NULL,
     'DEV9999900002', 'BRL', 'BRL',
     'Dev Test', 'Dev Test',
     'Dev Test, Rua Teste Dev 100, São Paulo, SP, 01310100, BR',
     'Dev Test, Rua Teste Dev 100, São Paulo, SP, 01310100, BR',
     'pagarme_pix', NOW(), NOW());

-- -----------------------------------------------------------------------------
-- Pagar.me: charge
-- ATENÇÃO: esta tabela NÃO está no db_schema.xml deste módulo.
-- É definida em pagarme/ecommerce-module-core (dependência composer).
-- Execute DEPOIS do setup:upgrade para garantir que a tabela existe.
--
-- pagarme_id = varchar, ID da charge na API (prefixo 'ch_')
--   ATENÇÃO: ChargesInterface::CHARGE_ID = 'charge_id', mas a coluna real
--   no banco é 'pagarme_id' — confirmado pelo UI component do grid admin.
-- code       = Pagar.me order_id (prefixo 'or_')
-- order_id   = sales_order.entity_id (INT) — NÃO a string 'or_...'
-- amount/paid_amount/refunded_amount = DECIMAL em BRL (float), NÃO centavos.
-- type: 'credit_card' | 'pix'
-- status: 'paid' | 'pending' | 'failed'
-- -----------------------------------------------------------------------------
INSERT IGNORE INTO `pagarme_module_core_charge`
    (`pagarme_id`, `code`, `order_id`, `status`,
     `amount`, `paid_amount`, `refunded_amount`)
VALUES
    ('ch_devtest00000001', 'or_devtest00000001', 9991, 'paid',    189.90, 189.90,   0.00),
    ('ch_devtest00000002', 'or_devtest00000002', 9992, 'pending',  49.90,   0.00,   0.00),
    ('ch_devtest00000099', 'or_devtest00000099', NULL, 'failed',  249.90,   0.00,   0.00);

SET FOREIGN_KEY_CHECKS = 1;
