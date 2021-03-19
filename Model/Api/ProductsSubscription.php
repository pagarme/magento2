<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\App\ObjectManager;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Services\ProductSubscriptionService;
use Pagarme\Pagarme\Api\ProductSubscriptionApiInterface;
use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\ProductSubscriptionHelper;
use Pagarme\Pagarme\Api\ObjectMapper\ProductSubscription\ProductSubscriptionMapperInterface;

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

    /**
     * @var ProductSubscriptionHelper
     */
    protected $productSubscriptionHelper;

    public function __construct(Request $request)
    {
        $this->request = $request;
        Magento2CoreSetup::bootstrap();
        $this->i18n = new LocalizationService();
        $this->moneyService = new MoneyService();
        $this->productSubscriptionService = new ProductSubscriptionService();
        $this->productSubscriptionHelper = new ProductSubscriptionHelper();
    }

    /**
     * Returns greeting message to user
     *
     * @param ProductSubscriptionInterface $productSubscription
     * @param int $id
     * @return ProductSubscriptionMapperInterface|array
     */
    public function save($productSubscription, $id = null)
    {
        try {
            if (!empty($id)) {
                $product = $this->productSubscriptionService->findById($id);
                if (empty($product)) {
                    return "Subscription Product not found";
                }

                $productSubscription->setId($id);
            }

            $this->productSubscriptionHelper->getProductPlataform(
                $productSubscription->getProductId()
            );

            $productSubscription = $this->productSubscriptionService
                ->saveProductSubscription($productSubscription);

            $this->productSubscriptionHelper
                ->setCustomOption($productSubscription);

        } catch (\Exception $exception) {
            return [
                'code' => 404,
                'message' => $exception->getMessage()
            ];
        } catch (\Throwable $exception) {
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
     * @return ProductSubscriptionMapperInterface[]|array
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
     * @return ProductSubscriptionMapperInterface|null
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
     * @return ProductSubscriptionMapperInterface|array
     */
    public function update($id, $productSubscription)
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
        try {
            $productSubscription = $this->productSubscriptionService->findById($id);

            if ($productSubscription === null) {
                return "Subscription Product not found";
            }

            $this->productSubscriptionHelper->deleteRecurrenceCustomOption(
                $productSubscription
            );

            $this->productSubscriptionService->delete($id);
        } catch (\Exception $exception) {
            return [$exception->getMessage()];
        } catch (\Throwable $exception) {
            return [$exception->getMessage()];
        }

        return "Subscription Product deleted with success";
    }

    /**
     * Save product subscription
     *
     * @param array $form
     * @param int $id
     * @return ProductSubscriptionMapperInterface|array
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

            $this->productSubscriptionHelper
                ->setCustomOption($productSubscription);

            return json_encode([
                'code' => 200,
                'message' => 'Product subscription saved'
            ]);

        } catch (\Exception $exception) {
            return json_encode([
                'code' => 404,
                'message' => $exception->getMessage()
            ]);
        } catch (\Throwable $exception) {
            return json_encode([
                'code' => 404,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function gerFormattedForm($form)
    {
        if (isset($form['credit_card'])) {
            $form['credit_card'] = (bool)$form['credit_card'];
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

        foreach ($form['repetitions'] as &$repetition) {
            $repetition['recurrence_price'] = str_replace(
                [',', '.'],
                '',
                $repetition['recurrence_price']
            );
        }

        return $form;
    }
}
