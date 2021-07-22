<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\Webapi\Exception as MagentoException;
use Magento\Framework\Webapi\Rest\Request;
use Pagarme\Core\Recurrence\Services\PlanService;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Interfaces\ProductPlanInterface;
use Pagarme\Core\Recurrence\Factories\PlanFactory;
use Pagarme\Pagarme\Api\ProductPlanApiInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\ProductPlanHelper;
use Pagarme\Pagarme\Api\ObjectMapper\ProductPlan\ProductPlanMapperInterface;

class ProductsPlan implements ProductPlanApiInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var PlanService
     */
    private $planService;

    public function __construct(Request $request)
    {
        Magento2CoreSetup::bootstrap();
        $this->request = $request;
        $this->planService = new PlanService();
    }

    /**
     * Returns greeting message to user
     *
     * @return mixed
     */
    public function saveFormData()
    {
        $post = $this->request->getBodyParams();
        parse_str($post[0], $params);

        $form = $this->gerFormattedForm($params['form']);
        $form['status'] = 'ACTIVE';

        if (empty($form)) {
            return json_encode([
                'code' => 404,
                'message' => 'Erro ao tentar criar um produto do tipo plano'
            ]);
        }

        if (!$form['items']) {
            return json_encode([
                'code' => 404,
                'message' => 'Please add subproducts before product saving'
            ]);
        }

        try {
            $planService = new PlanService();
            $planObject = (new PlanFactory())->createFromPostData($form);

            $planService->save($planObject);
        } catch (\Exception $exception) {
            return json_encode([
                'code' => 404,
                'message' => 'Erro ao tentar criar um produto do tipo plano'
            ]);
        }

        return json_encode([
            'code' => 200,
            'message' => 'Product Plan saved'
        ]);
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

        if (isset($form['installments'])) {
            $form['installments'] = (bool)$form['installments'];
        }

        return $form;
    }

    /**
     * Save product subscription
     * @param int $id
     * @return ProductPlanMapperInterface
     * @throws MagentoException
     */
    public function save($productPlan, $id = null)
    {
        try {
            ProductPlanHelper::mapperProductPlan($productPlan);
            $productPlan->setStatus('ACTIVE');
            $productPlan->setBillingType('PREPAID');

            $this->planService->save($productPlan);
        } catch (\Exception $exception) {
            throw new MagentoException(
                __($exception->getMessage()),
                0,
                $exception->getCode()
            );
        }

        return $productPlan;
    }

    /**
     * @return ProductPlanMapperInterface[]
     * @throws MagentoException
     */
    public function list()
    {
        try {
            $products = $this->planService->findAll();

            if (empty($products)) {
                throw new \Exception('List Product plan not found', 404);
            }

            return $products;
        } catch (\Exception $exception) {
            throw new MagentoException(
                __($exception->getMessage()),
                0,
                $exception->getCode()
            );
        }
    }

    /**
     * @param int $id
     * @param ProductPlanMapperInterface $productPlan
     * @return ProductPlanMapperInterface
     * @throws MagentoException
     */
    public function update($id, $productPlan)
    {
        try {
            $planOriginal = $this->planService->findById($id);

            if (empty($planOriginal)) {
                throw new \Exception('Plan not found', 404);
            }

            ProductPlanHelper::mapperProductPlanUpdate($planOriginal, $productPlan);

            $this->planService->save($productPlan);
        } catch (\Exception $exception) {
            throw new MagentoException(
                __($exception->getMessage()),
                0,
                $exception->getCode()
            );
        }

        return $productPlan;
    }

    /**
     * @param int $id
     * @return ProductPlanMapperInterface
     * @throws \Exception
     */
    public function find($id)
    {
        try {
            $plan = $this->planService->findById($id);

            if (empty($plan)) {
                throw new \Exception('Product plan not found', 400);
            }

            return $plan;
        } catch (\Exception $exception) {
            throw new MagentoException(
                __($exception->getMessage()),
                0,
                $exception->getCode()
            );
        }
    }

    /**
     * @param int $id
     * @return Plan
     * @throws MagentoException
     */
    public function delete($id)
    {
        try {
            $productData = $this->planService->findById($id);

            if (!$productData || !$productData->getId()) {
                throw new \Exception('Product plan not found', 404);
            }

            $this->planService->delete($id);

            return $productData;
        } catch (\Exception $exception) {
            throw new MagentoException(
                __($exception->getMessage()),
                0,
                $exception->getCode()
            );
        }
    }
}
