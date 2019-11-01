<?php

namespace MundiPagg\MundiPagg\Model\Api;

use MundiPagg\MundiPagg\Api\ProductSubscriptionInterface;
use \Magento\Framework\Webapi\Rest\Request;

class ProductsSubscription implements ProductSubscriptionInterface
{

    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Returns greeting message to user
     *
     * @param mixed $data
     * @return mixed
     */
    public function saveProductSubscription()
    {
        $post = $this->request->getBodyParams();
        parse_str($post[0], $params);

        if (empty($params)) {
            return json_encode([
                'code' => 404,
                'message' => 'Error on save product subscription'
            ]);
        }

        //@todo Send data to product subscription service

        return json_encode([
            'code' => 200,
            'message' => 'Product subscription saved'
        ]);
    }
}