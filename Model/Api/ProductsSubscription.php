<?php

namespace MundiPagg\MundiPagg\Model\Api;

use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use MundiPagg\MundiPagg\Api\ProductSubscriptionApiInterface;
use \Magento\Framework\Webapi\Rest\Request;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class ProductsSubscription implements ProductSubscriptionApiInterface
{

    /**
     * @var Request
     */
    protected $request;
    /**
     * @var ProductSubscriptionService
     */
    protected $productSubscriptionService;

    public function __construct(Request $request)
    {
        $this->request = $request;
        Magento2CoreSetup::bootstrap();
        $this->i18n = new LocalizationService();
        $this->moneyService = new MoneyService();
        $this->productSubscriptionService = new ProductSubscriptionService();
    }

    /**
     * Returns greeting message to user
     *
     * @param ProductSubscriptionInterface $productSubscription
     * @param int $id
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface|array
     */
    public function save(ProductSubscriptionInterface $productSubscription, $id = null)
    {
        try {
            if (!empty($id)) {
                $productSubscription->setId($id);
            }

            $productSubscription = $this->productSubscriptionService
                    ->saveProductSubscription($productSubscription);

            $this->setCustomOption($productSubscription);

        } catch (\Exception $exception) {
            return [
                'code' => 404,
                'message' => $exception->getMessage()
            ];
        }

        return $productSubscription;
    }

    /**
     * List products subscription
     *
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface[]|array
     */
    public function list()
    {
        $products = $this->productSubscriptionService->findAll();
        if (empty($products)) {
            return "Subscription Products not found";
        }

        return $products;
    }

    /**
     * Get a product subscription
     *
     * @param int $id
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface|null
     */
    public function getProductSubscription($id)
    {
        $product = $this->productSubscriptionService->findById($id);
        if (empty($product)) {
            return "Subscription Product not found";
        }

        return $product;
    }

    /**
     * Update product subscription
     *
     * @param int $id
     * @param ProductSubscriptionInterface $productSubscription
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface|array
     */
    public function update($id, ProductSubscriptionInterface $productSubscription)
    {
        return $this->save($productSubscription, $id);
    }

    /**
     * Delete product subscription
     *
     * @param int $id
     * @return mixed
     */
    public function delete($id)
    {
        try{
            $this->productSubscriptionService->delete($id);
        } catch (\Exception $exception) {
            return [
                $exception->getMessage()
            ];
        }

        return "Subscription Product deleted with success";
    }

    protected function setCustomOption(ProductSubscription $productSubscription)
    {
        $objectManager = ObjectManager::getInstance();

        $productId = $productSubscription->getProductId();
        $product = $objectManager->get('Magento\Catalog\Model\Product')
            ->load($productId);

        $values = $this->getValuesFromRepetitions($productSubscription);

        $customOption = $objectManager->create(
            'Magento\Catalog\Api\Data\ProductCustomOptionInterface'
        );

        $customOption->setTitle('Cycles')
            ->setType('radio')
            ->setIsRequire(true)
            ->setSortOrder(100)
            ->setPrice(0)
            ->setPriceType('fixed')
            ->setValues($values)
            ->setMaxCharacters(50)
            ->setSku("recurrence")
            ->setProductSku($product->getSku());

        $customOptions = $this->addCustomOptionOnArray($customOption, $product);

        $product->setHasOptions(1);
        $product->setCanSaveCustomOptions(true);
        $product->setOptions($customOptions)->save();
    }

    protected function addCustomOptionOnArray($customOption, $product)
    {
        $options = $product->getOptions();

        if (empty($options)) {
            return [$customOption];
        }

        $customOptions = [];
        $hasRecurrenceOption = false;
        foreach ($options as $option) {
            if ($option->getSku() !== "recurrence") {
                $customOptions[] = $option;
                continue;
            }
            $customOptions[] = $customOption;
            $hasRecurrenceOption = true;
        }

        if (!$hasRecurrenceOption) {
            $customOptions[] = $customOption;
        }
        return $customOptions;
    }

    protected function getValuesFromRepetitions(ProductSubscription $productSubscription)
    {
        $values = [];

        $sellAsNormalProduct = [
            "title" => "Compra Única",
            "price" => 0,
            "price_type"  => "fixed",
            "sort_order"  => "0"
        ];

        if (!empty($productSubscription->getSellAsNormalProduct())) {
            $values[] = $sellAsNormalProduct;
        }

        $repetitions = $productSubscription->getRepetitions();
        foreach ($repetitions as $repetition) {
            $values[] = [
                "title" => $this->getCycleTitle($repetition),
                "price" => 0,
                "price_type"  => "fixed",
                "sort_order"  => $repetition->getId()
            ];
        }

        return $values;
    }

    protected function getCycleTitle(Repetition $repetition)
    {
        $intervalCount = $repetition->getIntervalCount();
        $intervalType = $this->i18n->getDashboard(
            $repetition->getIntervalTypeLabel()
        );

        $totalAmount = $this->moneyService->centsToFloat(
            $repetition->getRecurrencePrice()
        );

        $discountLabel = " - (Total: R$ {$totalAmount})";
        // @todo create dictionary
        $intervalLabel = "De {$intervalCount} em {$intervalCount} {$intervalType}";

        if (empty($repetition->getRecurrencePrice())) {
            return $intervalLabel;
        }
        return $intervalLabel . $discountLabel;
    }

    /**
     * Save product subscription
     *
     * @param array $form
     * @param int $id
     * @return \Mundipagg\Core\Recurrence\Interfaces\ProductSubscriptionInterface|array
     */
    public function saveFormData()
    {
        try {
            $post = $this->request->getBodyParams();
            parse_str($post[0], $params);

            $form = $this->gerFormattedForm($params['form']);

            if (empty($form)) {
                return json_encode([
                    'code' => 404,
                    'message' => 'Error on save product subscription'
                ]);
            }

            $productSubscriptionService = new ProductSubscriptionService();
            $productSubscription =
                $productSubscriptionService->saveFormProductSubscription($form);
            $this->setCustomOption($productSubscription);

            return json_encode([
                'code' => 200,
                'message' => 'Product subscription saved'
            ]);

        } catch (\Exception $exception) {
            return json_encode([
                'code' => 404,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function gerFormattedForm($form)
    {
        if (isset($form['credit_card'])) {
            $form['credit_card'] = (bool) $form['credit_card'];
        }

        if (isset($form['boleto'])) {
            $form['boleto'] = (bool)$form['boleto'];
        }

        if (isset($form['sell_as_normal_product'])) {
            $form['sell_as_normal_product'] = (bool)$form['sell_as_normal_product'];
        }

        if (isset($form['allow_installments'])) {
            $form['allow_installments'] = (bool)$form['allow_installments'];
        }

        foreach($form['repetitions'] as &$repetition) {
            $repetition['recurrence_price'] = str_replace([',', '.'], '', $repetition['recurrence_price']);
        }

        return $form;
    }
}