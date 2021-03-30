<?php

namespace Pagarme\Pagarme\Ui\Component\Column\Invoices;

use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    /** Url path */
    const URL_PATH_EDIT = 'pagarme_pagarme/invoices/index';
    const URL_PATH_DELETE = 'pagarme_pagarme/invoices/delete';
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
                $actions = $this->getActions($name, $item);

                if ($actions) {
                    $item = array_merge($item, $actions);
                }

            }
        }
        return $dataSource;
    }

    protected function getActions($name, $item)
    {
        if ($item['status']->getText() != 'canceled') {
            $actions[$name]['delete'] = [
                'href' => $this->getUrlPagarmeDelete($item, self::URL_PATH_DELETE),
                'label' => __('Cancel'),
                'confirm' => [
                    'title' => __('Confirm action'),
                    'message' => __('Are you sure you want to cancel this item?')
                ]
            ];
            return $actions;
        }

        $actions[$name]['noaction'] = [
            'label' => '---',
            'href' => '#'
        ];

        return $actions;
    }

    protected function getUrlPagarmeEdit($item, $path)
    {
        $url = $this->urlBuilder->getUrl($path);
        return $url . "?invoice_id={$item['pagarme_id']}";
    }

    protected function getUrlPagarmeDelete($item, $path)
    {
        return $this->urlBuilder->getUrl($path, ['id' => $item['invoice_id']]);
    }
}
