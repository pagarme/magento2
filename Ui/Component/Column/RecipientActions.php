<?php

namespace Pagarme\Pagarme\Ui\Component\Column;

class RecipientActions extends Actions
{
    protected function getActions($name, $type, $item)
    {
        $actions = parent::getActions($name, $type, $item);
        $actions[$name]['edit']['label'] = __('View');

        return $actions;
    }
}
