<?php

namespace MundiPagg\MundiPagg\Model\Api;

use Magento\TestFramework\Event\Magento;
use MundiPagg\MundiPagg\Api\ProductPlanInterface;
use \Magento\Framework\Webapi\Rest\Request;
use Mundipagg\Core\Recurrence\Services\PlanService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Concrete\Magento2PlatformProductDecorator;

class ProductsPlan implements ProductPlanInterface
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
    public function saveProductPlan()
    {
        $post = $this->request->getBodyParams();
        parse_str($post[0], $params);

        if (empty($params)) {
            return json_encode([
                'code' => 404,
                'message' => 'Erro ao tentar criar um produto do tipo plano'
            ]);
        }

        $params['form']['items'] = $this->getSubProductsFromPlatform($params);
        if (!$params['form']['items']) {
            json_encode([
                'code' => 404,
                'message' => 'Please add subproducts before product saving'
            ]);
        }

        try {
            $planService = new PlanService();
            $planService->create($params['form']);
        } catch (\Exception $exception) {
            json_encode([
                'code' => 404,
                'message' => 'Erro ao tentar criar um produto do tipo plano'
            ]);
        }

        return json_encode([
            'code' => 200,
            'message' => 'Product Plan saved'
        ]);
    }

    private function getSubProductsFromPlatform($params)
    {
        if (empty($params['form']['items'])) {
            return null;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $subProducts = [];

        foreach ($params['form']['items'] as $item) {
            $product =
                $objectManager
                    ->create('Magento\Catalog\Model\Product')
                    ->load($item['product_id']);

            $platformProduct = new Magento2PlatformProductDecorator($product);
            $item['description'] = $product->getDescription();

            $subProducts[] = $item;
        }

        return $subProducts;
    }
}