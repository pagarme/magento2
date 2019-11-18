<?php

namespace MundiPagg\MundiPagg\Model\Api;

use MundiPagg\MundiPagg\Api\ProductPlanInterface;
use \Magento\Framework\Webapi\Rest\Request;
use Mundipagg\Core\Recurrence\Services\PlanService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

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
                'message' => 'Error on save product Plan'
            ]);
        }

        //@todo Send data to product Plan service
        $planService = new PlanService();
        $planService->createPlanAtPlatform($params['form']);

        return json_encode([
            'code' => 200,
            'message' => 'Product Plan saved'
        ]);
    }
}