<?php

namespace MundiPagg\MundiPagg\Model\Api;

use Magento\TestFramework\Event\Magento;
use MundiPagg\MundiPagg\Api\ProductPlanInterface;
use Magento\Framework\Webapi\Rest\Request;
use Mundipagg\Core\Recurrence\Services\PlanService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Concrete\Magento2PlatformProductDecorator;
use Magento\Framework\App\ObjectManager;
use MundiPagg\MundiPagg\Api\ProductPlanApiInterface;
use MundiPagg\MundiPagg\Helper\ProductHelper;
use Mundipagg\Core\Recurrence\Aggregates\SubProduct;

class ProductsPlan implements ProductPlanApiInterface
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
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
            $planService->save($form);
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

    public function save(\Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface $productPlan, $id = null)
    {
        /**
         * @var SubProduct[] $subProductList
         */
        $subProductList = $productPlan->getItems();
        $objectManager = ObjectManager::getInstance();
        $product =
            $objectManager
                ->create('Magento\Catalog\Model\Product')
                ->load(2053);

      //  $product1 = $product;
        $typeInstance = $objectManager->get('Magento\Bundle\Model\Product\Type');
        $selections = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );

        foreach ($selections as $index => $selection) {
            
        }




        $subProductIdList = array_map(function(SubProduct $subProduct) {
          return $subProduct->getProductId();
        }, $subProductList);


        $productHelper = new ProductHelper();
        $rr = $productHelper->getProductList($subProductIdList);


      //  $this->createPlanAtMundipagg($plan);
       // $planRepository->save($plan);

        $planService = new PlanService();
        $planService->create($params['form']);

       // $planService->createPlanAtMundipagg($productPlan);

      //  $x = $productPlan->getProductId();
        // TODO: Implement save() method.
    }

    public function saveFormData()
    {
        // TODO: Implement saveFormData() method.
    }

    public function list()
    {
        // TODO: Implement list() method.
    }

    public function update($id, \Mundipagg\Core\Recurrence\Interfaces\ProductPlanInterface $productSubscription)
    {
        // TODO: Implement update() method.
    }

    public function getProductSubscription($id)
    {
        // TODO: Implement getProductSubscription() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }
}