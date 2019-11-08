<?php

namespace MundiPagg\MundiPagg\Ui\Component\Column;

use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'mundipagg_mundipagg/*/create';
    const URL_PATH_DELETE = 'mundipagg_mundipagg/*/delete';
    /** @var UrlBuilder */
    protected $actionUrlBuilder;
    /** @var UrlInterface */
    protected $urlBuilder;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {

                    $item[$name]['edit'] = [
                        'href' => $this->getUrlMundipagg($item, self::URL_PATH_EDIT),
                        'label' => __('Edit')
                    ];

                    $item[$name]['delete'] = [
                        'href' => $this->getUrlMundipagg($item, self::URL_PATH_DELETE),
                        'label' => __('Delete')
                    ];
                }
            }
        }
        return $dataSource;
    }

    protected function getUrlMundipagg($item, $path)
    {
        $type = array_key_exists('plan_id', $item) ? "plans" : "subscriptions";
        $path = str_replace("*", $type, $path);

        $url = $this->urlBuilder->getUrl($path, ['id' => $item['id']]);
        return $url;
    }
}