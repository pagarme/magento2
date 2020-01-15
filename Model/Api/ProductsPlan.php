<?php

namespace MundiPagg\MundiPagg\Model\Api;

use Magento\TestFramework\Event\Magento;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Webapi\Exception as MagentoException;
use Magento\Framework\Webapi\Rest\Request;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\ValueObjects\PricingSchemeValueObject as PricingScheme;
use Mundipagg\Core\Recurrence\Services\PlanService;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface;
use Mundipagg\Core\Recurrence\Factories\PlanFactory;
use MundiPagg\MundiPagg\Api\ProductPlanApiInterface;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Concrete\Magento2PlatformProductDecorator;
use MundiPagg\MundiPagg\Helper\ProductHelper;
use MundiPagg\MundiPagg\Helper\ProductPlanHelper;

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
        $this->request = $request;
        $this->planService = new PlanService();
        Magento2CoreSetup::bootstrap();
    }
    /**
     * Returns greeting message to user
     *
     * @param mixed $data
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
     *
     * @param ProductPlanInterface $productPlan
     * @param int $id
     * @return ProductPlanInterface
     */
    public function save(ProductPlanInterface $productPlan, $id = null)
    {
        try {
            ProductPlanHelper::mapperProductPlan($productPlan);
            $productPlan->setStatus('ACTIVE');

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
     * @return ProductPlanInterface[]
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
     * @param ProductPlanInterface $productPlan
     * @return ProductPlanInterface
     */
    public function update($id, ProductPlanInterface $productPlan)
    {
        try {
            $productPlan->setStatus('ACTIVE');
            $productPlan->setId($id);

            $planOriginal = $this->planService->findById($id);

            if (empty($planOriginal)) {
                throw new \Exception('Plan not found', 404);
            }

            ProductPlanHelper::mapperProductPlanUpdate($planOriginal, $productPlan);

            $this->planService->save($planOriginal);
        } catch (\Exception $exception) {
            throw new MagentoException(
                __($exception->getMessage()),
                0,
                $exception->getCode()
            );
        }

        return $planOriginal;
    }

    /**
     * @param int $id
     * @return ProductPlanInterface
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
