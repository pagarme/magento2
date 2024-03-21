<?php

namespace Pagarme\Pagarme\Ui\Component\Column;

use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class RecipientActions extends Actions
{
    protected function getActions($name, $type, $item)
    {
        $actions[$name]['edit'] = [
            'href' => $this->getUrlPagarme($type, $item, self::URL_PATH_EDIT),
            'label' => __('View')
        ];

        $actions[$name]['delete'] = [
            'href' => $this->getUrlPagarme($type, $item, self::URL_PATH_DELETE),
            'label' => __('Delete'),
            'confirm' => [
                'title' => __('Confirm action'),
                'message' => __('Are you sure you want to delete this item?')
            ]
        ];

        return $actions;
    }
}
